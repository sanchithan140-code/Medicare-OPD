<?php

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:', 'session.driver' => 'array']);
Illuminate\Support\Facades\DB::purge('sqlite');
Illuminate\Support\Facades\DB::statement('CREATE TABLE roles (role_id INTEGER PRIMARY KEY, role_name TEXT)');
Illuminate\Support\Facades\DB::statement('CREATE TABLE users (user_id INTEGER PRIMARY KEY, role_id INTEGER, email TEXT, password_hash TEXT, failed_login_count INTEGER DEFAULT 0, locked_until TEXT, account_status TEXT, last_login_at TEXT, created_at TEXT, updated_at TEXT)');
$user = App\Models\User::create(['email' => 'lockout@example.test', 'password_hash' => Illuminate\Support\Facades\Hash::make('Correct-password1!'), 'account_status' => 'ACTIVE']);
$session = $app->make('session')->driver();
$session->start();
$controller = $app->make(App\Http\Controllers\AuthController::class);
$attempt = function ($password = 'wrong-password') use ($app, $session, $controller) {
    $request = Illuminate\Http\Request::create('/login', 'POST', ['email' => 'lockout@example.test', 'password' => $password]);
    $request->setLaravelSession($session);
    $app->instance('request', $request);
    return $controller->login($request);
};
$check = function ($condition, $message) {
    if (!$condition) throw new RuntimeException($message);
};
Illuminate\Support\Carbon::setTestNow(now()->startOfSecond());
for ($cycle = 0; $cycle < 3; $cycle++) {
    $duration = $cycle === 0 ? 30 : 60;
    for ($i = 1; $i <= 3; $i++) {
        $attempt();
        $user->refresh();
        $check($user->failed_login_count === ($cycle === 0 ? $i : $i + 3), 'Each cycle must allow three attempts');
        $check(($user->locked_until !== null) === ($i === 3), 'Only the third failure should lock');
    }
    $deadline = $user->locked_until->timestamp;
    $check($deadline === now()->timestamp + $duration, 'First lock must be 30 seconds, later locks 60 seconds');
    $attempt('Correct-password1!');
    $check($user->refresh()->locked_until->timestamp === $deadline, 'Blocked attempts must not extend the lock');
    $check(!Illuminate\Support\Facades\Auth::check(), 'Correct password must be blocked during lockout');
    $view = $controller->showLogin($app['request']);
    $check($view->getData()['lockSeconds'] === $duration, 'Reload must retain countdown');
    Illuminate\Support\Carbon::setTestNow(now()->addSeconds($duration));
    $check($controller->showLogin($app['request'])->getData()['lockSeconds'] === 0, 'Countdown must expire');
}
$attempt('Correct-password1!');
$check(Illuminate\Support\Facades\Auth::check(), 'Login must succeed after expiry');
$check($user->refresh()->failed_login_count === 0 && $user->locked_until === null, 'Success must clear failures');
Illuminate\Support\Carbon::setTestNow();
$attempt();
$check($user->refresh()->failed_login_count === 1 && $user->locked_until === null, 'Successful login must reset escalation');
echo "PASS: three attempts per cycle, 30/60/60-second locks, blocked valid password, reload countdown, expiry, and reset after success.\n";
