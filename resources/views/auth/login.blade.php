@extends('layouts.public')
@section('title','Login — MEDICARE')
@section('content')
<section class="auth-page">
    <aside class="auth-visual auth-visual-login">
        <a class="brand brand-light" href="{{ route('home') }}">
            <img class="brand-logo" src="{{ asset('assets/images/brand/mediland-hospital-logo.png') }}" alt="Mediland Hospital logo">
            <span><strong>MEDICARE</strong><small>Secure role-based access</small></span>
        </a>
        <div>
            <span class="kicker">Welcome back</span>
            <h1>Continue your healthcare journey securely.</h1>
            <p>We are committed to providing you with a seamless and secure experience.</p>
        </div>
    </aside>

    <main class="auth-panel">
        <form class="auth-card" method="post" action="{{ route('login.store') }}">
            @csrf
            <span class="eyebrow">PORTAL LOGIN</span>
            <h2>Sign in to MEDICARE</h2>
            <p class="muted">Welcome back!</p>
            @if($errors->any())
                <div class="alert alert-error">{{ $errors->first() }}</div>
            @endif
            @if($lockSeconds > 0)
            <div id="login-lockout" class="alert" role="status">
                Try again in <strong id="login-countdown">{{ sprintf('%02d:%02d', intdiv($lockSeconds, 60), $lockSeconds % 60) }}</strong>.
            </div>
            @endif
            <label>Email address
                <input type="email" name="email" value="{{ old('email') }}" required autocomplete="email">
            </label>
            <label>Password
                <span class="password-wrap">
                    <input id="login-password" type="password" name="password" required autocomplete="current-password">
                    <button class="password-toggle" type="button" data-password-toggle="login-password" aria-label="Show password" aria-pressed="false">Show</button>
                </span>
            </label>
            <div class="form-row-between login-options">
                <label class="check-row">
                    <input type="checkbox" name="remember" value="1">
                    <span>Remember me</span>
                </label>
                <a href="{{ route('password.request') }}">Forgot password?</a>
            </div>
            <button id="login-submit" class="btn btn-primary btn-block" type="submit" @disabled($lockSeconds > 0)>Sign in</button>
            <p class="auth-switch">New patient? <a href="{{ route('register') }}">Create account</a></p>
        </form>
    </main>
</section>
<script>
(() => {
    const seconds = @json($lockSeconds);
    if (seconds <= 0) return;
    const deadline = Date.now() + seconds * 1000;
    const button = document.getElementById('login-submit');
    const notice = document.getElementById('login-lockout');
    const countdown = document.getElementById('login-countdown');
    const timer = setInterval(() => {
        const remaining = Math.max(0, Math.ceil((deadline - Date.now()) / 1000));
        countdown.textContent = `${String(Math.floor(remaining / 60)).padStart(2, '0')}:${String(remaining % 60).padStart(2, '0')}`;
        if (remaining === 0) {
            clearInterval(timer);
            button.disabled = false;
            notice.remove();
        }
    }, 250);
})();
</script>
@endsection
