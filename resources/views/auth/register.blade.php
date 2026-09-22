@extends('layouts.public')
@section('title','Patient Registration — MEDICARE')
@section('content')
<section class="auth-page">
    <aside class="auth-visual auth-visual-register">
        <a class="brand brand-light" href="{{ route('home') }}"><img class="brand-logo" src="{{ asset('assets/images/brand/mediland-hospital-logo.png') }}" alt="Mediland Hospital"><span><strong>MEDICARE</strong><small>Patient registration</small></span></a>
        <div><span class="kicker">ONE ACCOUNT · MULTIPLE PROFILES</span><h1>Manage healthcare for yourself and your family.</h1><p>Welcome to MEDICARE! Please fill in the details below to create your account.</p></div>
    </aside>
    <main class="auth-panel">
        <form class="auth-card" method="post" action="{{ route('register.store') }}" novalidate>
            @csrf
            <span class="eyebrow">PATIENT ACCOUNT</span><h2>Create your secure account</h2><p class="muted">Welcome!</p>
            @if($errors->any())<div class="alert alert-error"><div><strong>Please correct these details:</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div></div>@endif
            <div class="form-grid">
                <label class="span-2">Existing patient ID (if you visited before)
                    <input name="existing_patient_id" value="{{ old('existing_patient_id') }}" inputmode="numeric" maxlength="4" pattern="[0-9]{4}">
                    <small>Use the ID on your hospital receipt. Reception will verify your identity before linking your records. Leave blank only if you are a new patient.</small>
                </label>
                <label class="span-2">Full name
                    <input name="full_name" value="{{ old('full_name') }}" minlength="3" maxlength="150" pattern="[A-Za-zÀ-ž .'-]+" autocomplete="name" required>
                </label>
                <label>Mobile number
                    <input name="phone" value="{{ old('phone') }}" inputmode="numeric" pattern="07[0-9]{8}" minlength="10" maxlength="10" autocomplete="tel" required>
                   
                </label>
                <label>Email address
                    <input type="email" name="email" value="{{ old('email') }}" maxlength="190" autocomplete="email" required>
                   
                </label>
                <label>Password
                    <input type="password" name="password" minlength="8" maxlength="255" autocomplete="new-password" required>
                    
                </label>
                <label>Confirm password
                    <input type="password" name="password_confirmation" minlength="8" maxlength="255" autocomplete="new-password" required>
                </label>
            </div>
            <label class="check-row warning-check"><input type="checkbox" name="terms" value="1" required>I accept the Terms and Conditions.</label>
            <br>
            <button class="btn btn-primary btn-block" type="submit">Create Account</button>
            <p class="auth-switch">Already registered? <a href="{{ route('login') }}">Sign in</a></p>
        </form>
    </main>
</section>
@endsection
