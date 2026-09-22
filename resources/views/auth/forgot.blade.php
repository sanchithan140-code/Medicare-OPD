@extends('layouts.public')
@section('title','Forgot Password — MEDICARE')
@section('content')
<section class="simple-auth"><form class="auth-card" method="post" action="{{ route('password.email') }}">@csrf<div class="otp-icon">⌁</div><span class="eyebrow">ACCOUNT RECOVERY</span><h2>Reset your password</h2><p class="muted">Let’s help you return to your healthcare journey. Enter your email address and we’ll send you a secure verification code.</p>@if($errors->any())<div class="alert alert-error">{{ $errors->first() }}</div>@endif<label>Email address<input type="email" name="email" value="{{ old('email') }}" maxlength="190" autocomplete="email" required></label><button class="btn btn-primary btn-block">Send reset OTP</button><a class="text-link" href="{{ route('login') }}">Back to login</a></form></section>
@endsection
