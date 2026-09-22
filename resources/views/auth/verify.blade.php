@extends('layouts.public')
@section('title','Verify Email — MEDICARE')
@section('content')
<section class="simple-auth"><div class="auth-card">
    <div class="otp-icon">✉</div><span class="eyebrow">EMAIL VERIFICATION</span><h2>Enter your six-digit OTP</h2>
    <p class="muted">The code was sent to <strong>{{ $email }}</strong>. It expires in 3 minutes. Five incorrect attempts lock OTP access for one hour.</p>
    <p class="otp-timer" data-otp-timer data-expires-at="{{ $expiresAt }}" data-locked-until="{{ $lockedUntil ?? 0 }}">Time remaining: <strong>03:00</strong></p>
    @if($errors->any())<div class="alert alert-error">{{ $errors->first() }}</div>@endif
    <form method="post" action="{{ route('verify.store') }}">@csrf<input type="hidden" name="email" value="{{ $email }}"><label>OTP code<input name="otp" inputmode="numeric" autocomplete="one-time-code" minlength="6" maxlength="6" pattern="[0-9]{6}" placeholder="000000" required></label><button class="btn btn-primary btn-block">Verify & activate account</button></form>
    <form class="inline-form" method="post" action="{{ route('verify.resend') }}">@csrf<input type="hidden" name="email" value="{{ $email }}"><button class="btn btn-ghost btn-block" type="submit" data-otp-resend @disabled(! $canResend)>Resend OTP</button></form>
</div></section>
@endsection
@push('scripts')
<script src="{{ asset('assets/js/otp-timer.js') }}"></script>
@endpush
