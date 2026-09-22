<?php

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:', 'session.driver' => 'array', 'cache.default' => 'array']);
Illuminate\Support\Facades\DB::purge('sqlite');
Illuminate\Support\Facades\DB::statement('CREATE TABLE users (user_id INTEGER PRIMARY KEY, email TEXT, full_name TEXT, password_hash TEXT, password_changed_at TEXT, failed_login_count INTEGER DEFAULT 0, locked_until TEXT, created_at TEXT, updated_at TEXT)');
Illuminate\Support\Facades\DB::statement('CREATE TABLE email_otps (otp_id INTEGER PRIMARY KEY, user_id INTEGER, purpose TEXT, code_hash TEXT, expires_at TEXT, used_at TEXT, attempts INTEGER DEFAULT 0, requested_ip TEXT, created_at TEXT)');
$user = App\Models\User::create(['email' => 'reset@example.test', 'full_name' => 'Reset Test', 'password_hash' => Illuminate\Support\Facades\Hash::make('Old-password1!')]);
$mail = new class extends App\Services\HospitalMailService {
    public string $code = '';
    public int $sent = 0;
    public bool $available = true;
    public function otp(App\Models\User $user, string $code, string $purpose): bool {
        $this->code = $code;
        $this->sent++;
        return $this->available;
    }
};
$controller = new App\Http\Controllers\AuthController(new App\Services\AuditService, $mail);
$session = $app->make('session')->driver();
$session->start();
$request = function (array $data) use ($app, $session) {
    $request = Illuminate\Http\Request::create('/forgot-password', 'POST', $data);
    $request->setLaravelSession($session);
    $app->instance('request', $request);
    return $request;
};
$check = function ($condition, $message) { if (!$condition) throw new RuntimeException($message); };
$controller->forgot($request(['email' => 'RESET@example.test']));
$check($mail->sent === 1 && strlen($mail->code) === 6, 'Registered email should receive a six-digit OTP.');
$controller->forgot($request(['email' => $user->email]));
$check($mail->sent === 1, 'An unexpired code should prevent duplicate sends.');
$code = $mail->code;
$controller->reset($request(['email' => $user->email, 'otp' => $code, 'password' => 'New-password2!', 'password_confirmation' => 'New-password2!']));
$check(Illuminate\Support\Facades\Hash::check('New-password2!', $user->fresh()->password_hash), 'Valid OTP must update the password.');
$controller->reset($request(['email' => $user->email, 'otp' => $code, 'password' => 'Replay-password3!', 'password_confirmation' => 'Replay-password3!']));
$check(Illuminate\Support\Facades\Hash::check('New-password2!', $user->fresh()->password_hash), 'Used OTP must not be reusable.');
$controller->forgot($request(['email' => $user->email]));
$check($mail->sent === 2, 'A consumed OTP must allow a new reset immediately.');
App\Models\EmailOtp::query()->update(['used_at' => now()]);
$mail->available = false;
$controller->forgot($request(['email' => $user->email]));
$check(App\Models\EmailOtp::whereNull('used_at')->count() === 0, 'Failed delivery must invalidate the code.');
$check($session->get('_old_input')['email'] === $user->email, 'Failed delivery must preserve the email.');
$check(str_contains($session->get('errors')->first('email'), 'email service is unavailable'), 'Error must identify a delivery failure.');
echo "PASS: reset OTP generation, duplicate prevention, password update, replay rejection, new reset after use, and failed delivery.\n";
