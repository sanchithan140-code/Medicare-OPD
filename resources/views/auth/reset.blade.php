@extends('layouts.public')
@section('title','Reset Password — MEDICARE')
@section('content')
<section class="simple-auth"><div class="auth-card">
    <div class="otp-icon">✓</div><span class="eyebrow">NEW PASSWORD</span><h2>Complete password reset</h2>
    <p class="muted">The reset OTP expires in 3 minutes. Five incorrect attempts lock OTP access for one hour.</p>
    <p class="otp-timer" data-otp-timer data-expires-at="{{ $expiresAt }}" data-locked-until="{{ $lockedUntil ?? 0 }}">Time remaining: <strong>03:00</strong></p>
    @if($errors->any())<div class="alert alert-error"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    <form method="post" action="{{ route('password.update') }}">@csrf
        <label>Email<input type="email" name="email" value="{{ old('email',$email) }}" maxlength="190" required></label>
        <label>Reset OTP<input name="otp" inputmode="numeric" autocomplete="one-time-code" minlength="6" maxlength="6" pattern="[0-9]{6}" required></label>
        <label>New password<input type="password" name="password" minlength="8" autocomplete="new-password" required><small>Upper/lowercase, a number and a symbol.</small></label>
        <label>Confirm password<input type="password" name="password_confirmation" minlength="8" autocomplete="new-password" required></label>
        <button class="btn btn-primary btn-block">Update password</button>
    </form>
    <form class="inline-form" method="post" action="{{ route('password.email') }}">@csrf
        <input type="hidden" name="email" value="{{ $email }}">
        <button class="btn btn-ghost btn-block" type="submit" data-otp-resend @disabled(! $canResend)>Resend reset OTP</button>
    </form>
</div></section>
@endsection
@push('scripts')
<script src="{{ asset('assets/js/otp-timer.js') }}"></script>
@endpush
