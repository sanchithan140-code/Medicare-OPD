<?php
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

if (in_array('--existing-admins', $argv, true)) {
    $counts = App\Models\User::whereHas('role', fn ($q) => $q->whereIn('role_name', ['admin', 'main_admin']))
        ->with('role')->get()->groupBy('role.role_name')->map->count();
    echo $counts->toJson().PHP_EOL;
    exit;
}

config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
Illuminate\Support\Facades\DB::purge('sqlite');
Illuminate\Support\Facades\Schema::create('roles', function ($table) {
    $table->increments('role_id');
    $table->string('role_name');
});
$rules = (new App\Http\Requests\AdminUserRequest)->rules();
foreach (['main_admin', 'admin', 'office_staff', 'doctor', 'lab_officer', 'patient'] as $index => $role) {
    $id = $index + 1;
    Illuminate\Support\Facades\DB::table('roles')->insert(['role_id' => $id, 'role_name' => $role]);
    $passes = Illuminate\Support\Facades\Validator::make(['role_id' => $id], ['role_id' => $rules['role_id']])->passes();
    if ($passes !== in_array($role, ['office_staff', 'lab_officer'], true)) {
        throw new RuntimeException("Unexpected creation permission for {$role}");
    }
}
if (Illuminate\Support\Facades\Validator::make(['role_id' => 999], ['role_id' => $rules['role_id']])->passes()) {
    throw new RuntimeException('Unknown role accepted');
}
echo "Role creation restrictions passed for all six roles and an unknown role.".PHP_EOL;
