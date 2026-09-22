<?php
// Real Laravel HTTP middleware and page rendering, with isolated sessions and rollback.
require __DIR__.'/../vendor/autoload.php';
$app=require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\{DB,Mail,Http,Auth};
use App\Models\User;
config(['session.driver'=>'array','cache.default'=>'array']);
Mail::fake();Http::preventStrayRequests();
$kernel=$app->make(Illuminate\Contracts\Http\Kernel::class);
$failed=[];$count=0;$pages=0;
DB::beginTransaction();
try {
    $users=[];
    foreach(['patient','doctor','office_staff','lab_officer','admin','main_admin'] as $role) {
        $users[$role]=User::whereHas('role',fn($q)=>$q->where('role_name',$role))->where('account_status','ACTIVE')->whereNull('requested_patient_code')->firstOrFail();
    }
    foreach(app('router')->getRoutes() as $route) {
        if(!in_array('GET',$route->methods()) || str_contains($route->uri(),'{') || !$route->getName() || str_starts_with($route->uri(),'storage'))continue;
        $middleware=$route->gatherMiddleware();$roles=null;
        foreach($middleware as $m)if(str_starts_with($m,'role:'))$roles=explode(',',substr($m,5));
        $private=in_array('auth',$middleware);$guest=in_array('guest',$middleware);
        foreach(['guest',...array_keys($users)] as $role) {
            Auth::forgetGuards();app('session')->driver()->flush();
            if($role!=='guest')Auth::guard()->setUser($users[$role]);
            $expected=$private && $role==='guest' ? 302 : ($guest && $role!=='guest' ? 302 : ($roles && $role!=='guest' && !in_array($role,$roles) ? 403 : 200));
            $request=Illuminate\Http\Request::create('/'.ltrim($route->uri(),'/'),'GET');
            $response=$kernel->handle($request);$status=$response->getStatusCode();$count++;
            if($status!==$expected)$failed[]=$route->getName()." [$role]: expected $expected, got $status";
            elseif($status===200)$pages++;
        }
    }
    foreach($users as $role=>$user){
        $user->update(['account_status'=>'DISABLED']);Auth::forgetGuards();Auth::guard()->setUser($user);
        $response=$kernel->handle(Illuminate\Http\Request::create('/account','GET'));$count++;
        if($response->getStatusCode()!==403)$failed[]="Disabled $role retains account access";
        $user->update(['account_status'=>'ACTIVE']);
    }
    Auth::forgetGuards();Auth::guard()->setUser($users['office_staff']);
    $response=$kernel->handle(Illuminate\Http\Request::create('/staff/lab-bookings','POST'));$count++;
    if($response->getStatusCode()!==419)$failed[]='POST without CSRF token was not rejected';
} finally {DB::rollBack();}
foreach($failed as $failure)echo "FAIL $failure\n";
echo "$count role/page checks; $pages successful page renders; ".count($failed)." failures. Database changes rolled back.\n";
exit($failed ? 1 : 0);

