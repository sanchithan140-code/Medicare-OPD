<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterPatientRequest;
use App\Models\EmailOtp;
use App\Models\Role;
use App\Models\User;
use App\Services\AuditService;
use App\Services\HospitalMailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function __construct(
        private readonly AuditService $audit,
        private readonly HospitalMailService $mail,
    ) {}

    public function showLogin(Request $request)
    {
        $email = $request->session()->get('login_lock_email');
        $lockedUntil = $email ? User::where('email', $email)->first()?->locked_until : null;

        return view('auth.login', [
            'lockSeconds' => $lockedUntil ? max(0, $lockedUntil->timestamp - now()->timestamp) : 0,
        ]);
    }
    public function showRegister() { return view('auth.register'); }
    public function showVerify(Request $request)
    {
        $email = mb_strtolower(trim((string) $request->query('email', session('verify_email'))));
        $lockedUntil=$this->otpLockUntil($email,'VERIFY_EMAIL');
        $pending = $request->session()->get('pending_registration');

        if (is_array($pending) && hash_equals((string) ($pending['email'] ?? ''), $email)) {
            return view('auth.verify', [
                'email' => $email,
                'expiresAt' => (int) ($pending['expires_at'] ?? 0),
                'lockedUntil'=>$lockedUntil,
                'canResend' => $lockedUntil<=now()->timestamp && ((int) ($pending['expires_at'] ?? 0)) <= now()->timestamp,
            ]);
        }

        $user = User::where('email', $email)->first();
        $record = $user ? $this->latestOtp($user, 'VERIFY_EMAIL') : null;

        return view('auth.verify', [
            'email' => $email,
            'expiresAt' => $record?->expires_at?->timestamp ?? 0,
            'lockedUntil'=>$lockedUntil,
            'canResend' => $lockedUntil<=now()->timestamp && (!$record || $record->expires_at->isPast()),
        ]);
    }
    public function showForgot() { return view('auth.forgot'); }
    public function showReset(Request $request)
    {
        $email = mb_strtolower(trim((string) $request->query('email', session('reset_email'))));
        $lockedUntil=$this->otpLockUntil($email,'RESET_PASSWORD');
        $user = User::where('email', $email)->first();
        $record = $user ? EmailOtp::where('user_id', $user->user_id)->where('purpose', 'RESET_PASSWORD')->latest('otp_id')->first() : null;

        return view('auth.reset', [
            'email' => $email,
            'expiresAt' => $record?->expires_at?->timestamp ?? 0,
            'lockedUntil'=>$lockedUntil,
            'canResend' => $lockedUntil<=now()->timestamp && (!$record || $record->expires_at->isPast()),
        ]);
    }

    public function register(RegisterPatientRequest $request)
    {
        $data = $request->validated();
        $this->assertOtpUnlocked($data['email'],'VERIFY_EMAIL');
        $roleId = Role::where('role_name', 'patient')->value('role_id');
        abort_unless($roleId, 500, 'Patient role is not configured. Import the MEDICARE database first.');

        $pending = [
            'role_id' => $roleId,
            'full_name' => $data['full_name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'password_hash' => Hash::make($data['password']),
            'requested_patient_code' => $data['existing_patient_id'] ?? null,
        ];

        $code = $this->issuePendingRegistrationOtp($request, $pending);
        $user = new User($pending);
        $sent = $this->mail->otp($user, $code, 'VERIFY_EMAIL');
        if(!$sent){$pending=$request->session()->get('pending_registration');$pending['expires_at']=now()->timestamp;$request->session()->put('pending_registration',$pending);}

        $redirect = redirect()->route('verify.form', ['email' => $user->email]);
        return $sent
            ? $redirect->with('success', 'A six-digit verification OTP was sent. Your account will be created after verification.')
            : $redirect->withErrors(['email' => 'The OTP email could not be sent. Check the SMTP settings and use Resend OTP.']);
    }

    public function verify(Request $request)
    {
        $data = $request->validate([
            'email' => ['required','email:rfc','max:190'],
            'otp' => ['required','digits:6'],
        ]);

        $email = mb_strtolower(trim($data['email']));
        $this->assertOtpUnlocked($email,'VERIFY_EMAIL');
        $pending = $request->session()->get('pending_registration');

        if (is_array($pending) && hash_equals((string) ($pending['email'] ?? ''), $email)) {
            if (! $this->validPendingRegistrationOtp($pending, $data['otp'])) {
                $pending['attempts'] = ((int) ($pending['attempts'] ?? 0)) + 1;
                if ($pending['attempts'] >= 5) {
                    $pending['otp_hash'] = null;
                    $this->lockOtp($email, 'VERIFY_EMAIL');
                }
                $request->session()->put('pending_registration', $pending);

                return back()->withErrors(['otp' => $pending['attempts'] >= 5
                    ? 'Five incorrect attempts. Try again after one hour.'
                    : 'The OTP is invalid or expired.']);
            }

            if (User::where('email', $email)->exists()) {
                $request->session()->forget(['pending_registration', 'verify_email']);
                return redirect()->route('register')->withErrors(['email' => 'This email address was registered while verification was pending. Please sign in or use another email.']);
            }

            $user = DB::transaction(function () use ($pending) {
                $user = User::create([
                'role_id' => $pending['role_id'],
                'full_name' => $pending['full_name'],
                'email' => $pending['email'],
                'phone' => $pending['phone'],
                'password_hash' => $pending['password_hash'],
                'email_verified_at' => now(),
                'account_status' => 'ACTIVE',
                'requested_patient_code' => $pending['requested_patient_code'] ?? null,
                ]);
                if (!$user->requested_patient_code) {
                    app(\App\Services\AccountPatientService::class)->ensure($user);
                }
                return $user;
            });

            $request->session()->forget(['pending_registration', 'verify_email']);
            Auth::login($user);
            $request->session()->regenerate();
            $this->audit->record('auth.registered', $user, [], ['email' => $user->email, 'phone' => $user->phone]);
            $this->audit->record('auth.email_verified', $user);
            if (!$user->requested_patient_code) {
                $patient = app(\App\Services\AccountPatientService::class)->ensure($user);
                $this->mail->welcome($user, $patient);
            }

            return redirect()->route($user->dashboardRoute())->with('success', 'Email verified and account created. Welcome to MEDICARE.');
        }

        // Compatibility for accounts created by the previous registration flow.
        $user = User::where('email', $email)->firstOrFail();
        abort_unless($user->account_status==='PENDING' && $user->email_verified_at===null,422,'This account is not awaiting email verification.');
        $record = $this->latestOtp($user, 'VERIFY_EMAIL');

        if (! $this->validOtp($record, $data['otp'])) {
            if ($record) {
                $record->increment('attempts');
                if ($record->attempts >= 5) {
                    $this->lockOtp($email, 'VERIFY_EMAIL');
                    $record->update(['used_at' => now()]);
                }
            }
            return back()->withErrors(['otp' => $record?->attempts >= 5
                ? 'Five incorrect attempts. Try again after one hour.'
                : 'The OTP is invalid or expired.']);
        }

        $firstVerification = $user->email_verified_at === null;
        $record->update(['used_at' => now()]);
        $user->update(['email_verified_at' => now(), 'account_status' => 'ACTIVE']);

        if ($user->isRole('patient') && !$user->requested_patient_code) {
            $patient = app(\App\Services\AccountPatientService::class)->ensure($user);
            if ($firstVerification) {
                $this->mail->welcome($user, $patient);
            }
        }

        Auth::login($user);
        $request->session()->regenerate();
        $this->audit->record('auth.email_verified', $user);

        return redirect()->route($user->dashboardRoute())->with('success', 'Email verified. Welcome to MEDICARE.');
    }

    public function resend(Request $request)
    {
        $data = $request->validate(['email' => ['required','email:rfc','max:190']]);
        $email = mb_strtolower(trim($data['email']));
        $this->assertOtpUnlocked($email,'VERIFY_EMAIL');
        $pending = $request->session()->get('pending_registration');

        if (is_array($pending) && hash_equals((string) ($pending['email'] ?? ''), $email)) {
            $expired = ((int) ($pending['expires_at'] ?? 0)) <= now()->timestamp;
            $locked = ((int) ($pending['attempts'] ?? 0)) >= 5;
            if (! $expired && ! $locked) {
                return back()->withErrors(['email' => 'You can resend after the three-minute timer reaches zero.']);
            }

            $code = $this->issuePendingRegistrationOtp($request, $pending);
            $sent = $this->mail->otp(new User($pending), $code, 'VERIFY_EMAIL');
            if(!$sent){$pending=$request->session()->get('pending_registration');$pending['expires_at']=now()->timestamp;$request->session()->put('pending_registration',$pending);}

            return $sent
                ? back()->with('success', 'A new verification OTP was sent.')
                : back()->withErrors(['email' => 'The OTP could not be sent. Check the SMTP sender settings and try again.']);
        }

        $user = User::where('email', $email)->firstOrFail();

        abort_unless($user->account_status==='PENDING' && $user->email_verified_at===null,422,'This account is not awaiting email verification.');
        $latest = EmailOtp::where('user_id',$user->user_id)
            ->where('purpose','VERIFY_EMAIL')->latest('otp_id')->first();
        if ($latest && $latest->attempts < 5 && $latest->expires_at->isFuture()) {
            return back()->withErrors(['email' => 'You can resend after the three-minute timer reaches zero.']);
        }

        $code = $this->issueOtp($user, 'VERIFY_EMAIL', $request->ip());
        $sent = $this->mail->otp($user, $code, 'VERIFY_EMAIL');
        if(!$sent)EmailOtp::where('user_id',$user->user_id)->where('purpose','VERIFY_EMAIL')->whereNull('used_at')->update(['expires_at'=>now(),'used_at'=>now()]);

        return $sent
            ? back()->with('success', 'A new verification OTP was sent.')
            : back()->withErrors(['email' => 'The OTP could not be sent. Check the SMTP sender settings and try again.']);
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => ['required','email:rfc','max:190'],
            'password' => ['required','string','max:255'],
        ]);

        $user = User::with('role')->where('email', mb_strtolower(trim($data['email'])))->first();

        if ($user?->locked_until?->isFuture()) {
            $request->session()->put('login_lock_email', $user->email);
            return back()->withErrors(['email' => 'Three incorrect attempts. Please wait before trying again.'])->onlyInput('email');
        }

        if ($user?->locked_until !== null) {
            // Keep the first lockout recorded until a successful login or password reset.
            $user->update(['failed_login_count' => 3, 'locked_until' => null]);
        }

        if (! $user || ! Hash::check($data['password'], $user->password_hash)) {
            if ($user) {
                $failed = min(255, (int) $user->failed_login_count + 1);
                $lockSeconds = $failed === 3 ? 30 : ($failed >= 6 ? 60 : 0);
                $user->update([
                    'failed_login_count' => $failed,
                    'locked_until' => $lockSeconds ? now()->addSeconds($lockSeconds) : null,
                ]);
                if ($lockSeconds) {
                    $request->session()->put('login_lock_email', $user->email);
                    return back()->withErrors(['email' => 'Three incorrect attempts. Please wait '.$lockSeconds.' seconds before trying again.'])->onlyInput('email');
                }
            }
            return back()->withErrors(['email' => 'The email address or password is incorrect.'])->onlyInput('email');
        }

        if ($user->account_status === 'PENDING' && $user->role?->role_name === 'patient') {
            session(['verify_email' => $user->email]);
            return redirect()->route('verify.form', ['email' => $user->email])
                ->withErrors(['email' => 'Verify your email before signing in. Use Resend OTP if required.']);
        }

        if ($user->account_status !== 'ACTIVE') {
            return back()->withErrors(['email' => 'This account is not active. Contact MEDICARE administration.'])->onlyInput('email');
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->forget('login_lock_email');
        $request->session()->regenerate();
        $user->update(['failed_login_count' => 0, 'locked_until' => null, 'last_login_at' => now()]);
        $this->audit->record('auth.login', $user);

        return redirect()->intended(route($user->dashboardRoute()));
    }

    public function forgot(Request $request)
    {
        $data = $request->validate(['email' => ['required','email:rfc','max:190']]);
        $this->assertOtpUnlocked($data['email'],'RESET_PASSWORD');
        $user = User::where('email', mb_strtolower(trim($data['email'])))->first();

        if ($user) {
            $latest = EmailOtp::where('user_id',$user->user_id)->where('purpose','RESET_PASSWORD')->latest('otp_id')->first();
            if (! $latest || $latest->used_at !== null || $latest->attempts >= 5 || $latest->expires_at->isPast()) {
                $code = $this->issueOtp($user, 'RESET_PASSWORD', $request->ip());
                if(!$this->mail->otp($user, $code, 'RESET_PASSWORD')){
                    EmailOtp::where('user_id',$user->user_id)->where('purpose','RESET_PASSWORD')->whereNull('used_at')->update(['expires_at'=>now(),'used_at'=>now()]);
                    return back()->withInput($request->only('email'))
                        ->withErrors(['email'=>'We could not send your reset code because the email service is unavailable. Please try again later or contact hospital support.']);
                }
            }
            session(['reset_email' => $user->email]);
            return redirect()->route('password.reset.form', ['email' => $user->email])
                ->with('success', 'A password-reset OTP was sent to your registered email. Check your inbox and spam folder. If you already requested a code, use that code until it expires.');
        }

        return back()->withInput($request->only('email'))->withErrors(['email'=>'No registered account was found for this email address.']);
    }

    public function reset(Request $request)
    {
        $data = $request->validate([
            'email' => ['required','email:rfc','max:190'],
            'otp' => ['required','digits:6'],
            'password' => ['required','confirmed',Password::min(8)->letters()->mixedCase()->numbers()->symbols()],
        ]);

        $user = User::where('email', mb_strtolower(trim($data['email'])))->firstOrFail();
        $this->assertOtpUnlocked($user->email,'RESET_PASSWORD');
        $record = $this->latestOtp($user, 'RESET_PASSWORD');

        if (! $this->validOtp($record, $data['otp'])) {
            if ($record) {
                $record->increment('attempts');
                if ($record->attempts >= 5) {
                    $this->lockOtp($user->email, 'RESET_PASSWORD');
                    $record->update(['used_at' => now()]);
                }
            }
            return back()->withErrors(['otp' => $record?->attempts >= 5
                ? 'Five incorrect attempts. Try again after one hour.'
                : 'The reset OTP is invalid or expired.']);
        }

        $record->update(['used_at' => now()]);
        $user->update([
            'password_hash' => Hash::make($data['password']),
            'password_changed_at' => now(),
            'failed_login_count' => 0,
            'locked_until' => null,
        ]);
        $this->audit->record('auth.password_reset', $user);

        return redirect()->route('login')->with('success', 'Password changed successfully. Sign in using the new password.');
    }

    public function logout(Request $request)
    {
        $user = $request->user();
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        if ($user) {
            $this->audit->record('auth.logout', $user);
        }

        return redirect()->route('home');
    }

    private function otpLockUntil(string $email,string $purpose): int
    {
        return (int) \Illuminate\Support\Facades\Cache::get('otp-lock:'.hash('sha256',mb_strtolower(trim($email)).'|'.$purpose),0);
    }
    private function lockOtp(string $email,string $purpose): void
    {
        \Illuminate\Support\Facades\Cache::put('otp-lock:'.hash('sha256',mb_strtolower(trim($email)).'|'.$purpose),now()->addHour()->timestamp,now()->addHour());
    }
    private function assertOtpUnlocked(string $email,string $purpose): void
    {
        if($this->otpLockUntil($email,$purpose)>now()->timestamp)throw \Illuminate\Validation\ValidationException::withMessages(['otp'=>'Five incorrect attempts. Try again after one hour.']);
    }
    private function issueOtp(User $user, string $purpose, ?string $ip): string
    {
        EmailOtp::where('user_id',$user->user_id)->where('purpose',$purpose)->whereNull('used_at')->update(['used_at' => now()]);
        $code = (string) random_int(100000, 999999);
        EmailOtp::create([
            'user_id' => $user->user_id,
            'purpose' => $purpose,
            'code_hash' => hash('sha256', $code),
            'expires_at' => now()->addMinutes(3),
            'requested_ip' => $ip,
        ]);

        return $code;
    }

    private function issuePendingRegistrationOtp(Request $request, array $pending): string
    {
        $code = (string) random_int(100000, 999999);
        $pending['otp_hash'] = hash('sha256', $code);
        $pending['expires_at'] = now()->addMinutes(3)->timestamp;
        $pending['sent_at'] = now()->timestamp;
        $pending['attempts'] = 0;
        $pending['requested_ip'] = $request->ip();

        $request->session()->put('pending_registration', $pending);
        $request->session()->put('verify_email', $pending['email']);

        return $code;
    }

    private function validPendingRegistrationOtp(array $pending, string $plainCode): bool
    {
        return ! empty($pending['otp_hash'])
            && ((int) ($pending['attempts'] ?? 0)) < 5
            && ((int) ($pending['expires_at'] ?? 0)) > now()->timestamp
            && hash_equals((string) $pending['otp_hash'], hash('sha256', $plainCode));
    }

    private function latestOtp(User $user, string $purpose): ?EmailOtp
    {
        return EmailOtp::where('user_id',$user->user_id)
            ->where('purpose',$purpose)
            ->whereNull('used_at')
            ->latest('otp_id')
            ->first();
    }

    private function validOtp(?EmailOtp $record, string $plainCode): bool
    {
        return $record
            && $record->attempts < 5
            && ! $record->expires_at->isPast()
            && hash_equals($record->code_hash, hash('sha256', $plainCode));
    }
}
