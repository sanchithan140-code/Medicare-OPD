<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
class ActiveAccount
{
    public function handle(Request $request,Closure $next)
    {
        // Existing sessions must lose access when administration disables an account.
        abort_if($request->user() && $request->user()->account_status!=='ACTIVE' && !$request->is('logout'),403,'This account is no longer active.');
        return $next($request);
    }
}
