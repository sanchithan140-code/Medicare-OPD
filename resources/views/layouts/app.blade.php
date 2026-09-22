<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#073b72">
    <title>@yield('title','MEDICARE')</title>
    <script>
        // Recheck authentication when the browser restores an in-memory page.
        window.addEventListener('pagehide', function (event) {
            if (event.persisted) document.documentElement.style.visibility = 'hidden';
        });
        window.addEventListener('pageshow', function (event) {
            if (event.persisted) window.location.reload();
        });
    </script>
    <link rel="icon" type="image/png" sizes="512x512" href="{{ asset('assets/images/brand/medicare-app-icon.png') }}?v=4">
    <link rel="apple-touch-icon" href="{{ asset('assets/images/brand/medicare-app-icon.png') }}?v=4">
    <link rel="stylesheet" href="{{ asset('assets/css/app.css') }}">
    @stack('styles')
</head>
<body class="app-shell">
@php($role = auth()->user()->role->role_name)
<aside class="sidebar" aria-label="Portal navigation">
    <a class="brand brand-light" href="{{ route(auth()->user()->dashboardRoute()) }}">
        <img class="brand-logo" src="{{ asset('assets/images/brand/mediland-hospital-logo.png') }}" alt="Mediland Hospital logo">
        <span><strong>MEDICARE</strong><small>{{ auth()->user()->role->display_name ?? 'Portal' }}</small></span>
    </a>
    <nav class="side-nav">
        @if($role==='patient')
            <a class="{{ request()->routeIs('patient.dashboard')?'active':'' }}" href="{{ route('patient.dashboard') }}">Dashboard</a>
            <a class="{{ request()->routeIs('patient.profiles*')?'active':'' }}" href="{{ route('patient.profiles') }}">Family profiles</a>
            <a class="{{ request()->routeIs('patient.ai*')?'active':'' }}" href="{{ route('patient.ai') }}">Care Guide</a>
            <a class="{{ request()->routeIs('patient.doctors')?'active':'' }}" href="{{ route('patient.doctors') }}">Find doctors</a>
            <a class="{{ request()->routeIs('patient.appointments*')?'active':'' }}" href="{{ route('patient.appointments') }}">Appointments</a>
            <a class="{{ request()->routeIs('patient.history')?'active':'' }}" href="{{ route('patient.history') }}">Medical history</a>
            <a class="{{ request()->routeIs('patient.labs')?'active':'' }}" href="{{ route('patient.labs') }}">Lab reports</a>
            <a class="{{ request()->routeIs('patient.payments')?'active':'' }}" href="{{ route('patient.payments') }}">Payments</a>
            <a class="{{ request()->routeIs('patient.insurance')?'active':'' }}" href="{{ route('patient.insurance') }}">Insurance</a>
            <a class="{{ request()->routeIs('patient.notifications')?'active':'' }}" href="{{ route('patient.notifications') }}">Notifications</a>
            <a class="{{ request()->routeIs('patient.support')?'active':'' }}" href="{{ route('patient.support') }}">Support</a>
        @elseif($role==='doctor')
            <a class="{{ request()->routeIs('doctor.dashboard')?'active':'' }}" href="{{ route('doctor.dashboard') }}">Dashboard</a>
            <a class="{{ request()->routeIs('doctor.appointments*')||request()->routeIs('doctor.consultation*')?'active':'' }}" href="{{ route('doctor.appointments') }}">Appointments</a>
            <a class="{{ request()->routeIs('doctor.patients','doctor.history')?'active':'' }}" href="{{ route('doctor.patients') }}">Patient records</a>
            <a class="{{ request()->routeIs('doctor.schedule')?'active':'' }}" href="{{ route('doctor.schedule') }}">Schedule & leave</a>
        @elseif($role==='lab_officer')
            <a class="{{ request()->routeIs('lab.dashboard')?'active':'' }}" href="{{ route('lab.dashboard') }}">Dashboard</a>
            <a class="{{ request()->routeIs('lab.orders*')||request()->routeIs('lab.result*')?'active':'' }}" href="{{ route('lab.orders') }}">Test orders</a>
        @elseif($role==='office_staff')
            <a class="{{ request()->routeIs('staff.dashboard')?'active':'' }}" href="{{ route('staff.dashboard') }}">Dashboard</a>
            <a class="{{ request()->routeIs('staff.appointments*')?'active':'' }}" href="{{ route('staff.appointments') }}">Appointments</a>
            <a class="{{ request()->routeIs('staff.walk-in*')?'active':'' }}" href="{{ route('staff.walk-in') }}">Walk-in booking</a>
            <a class="{{ request()->routeIs('staff.walkin-receipts')?'active':'' }}" href="{{ route('staff.walkin-receipts') }}">Walk-in receipts</a>
            <a class="{{ request()->routeIs('staff.schedules*')?'active':'' }}" href="{{ route('staff.schedules') }}">Schedules</a>
            <a class="{{ request()->routeIs('staff.patient-links*')?'active':'' }}" href="{{ route('staff.patient-links') }}">Patient account links</a>
            <a href="{{ route('staff.lab-bookings') }}">Lab bookings &amp; prices</a>
            <a class="{{ request()->routeIs('staff.patients')?'active':'' }}" href="{{ route('staff.patients') }}">Patients</a>
            <a class="{{ request()->routeIs('staff.billing')?'active':'' }}" href="{{ route('staff.billing') }}">Billing</a>
            <a class="{{ request()->routeIs('staff.insurance')?'active':'' }}" href="{{ route('staff.insurance') }}">Insurance</a>
            <a class="{{ request()->routeIs('staff.support')?'active':'' }}" href="{{ route('staff.support') }}">Support</a>
        @else
            <a class="{{ request()->routeIs('admin.dashboard')?'active':'' }}" href="{{ route('admin.dashboard') }}">Dashboard</a>
            <a class="{{ request()->routeIs('admin.users')?'active':'' }}" href="{{ route('admin.users') }}">Users</a>
            <a class="{{ request()->routeIs('admin.doctors')?'active':'' }}" href="{{ route('admin.doctors') }}">Doctors & schedules</a>
            <a class="{{ request()->routeIs('admin.catalogs')?'active':'' }}" href="{{ route('admin.catalogs') }}">Catalogs</a>
            <a class="{{ request()->routeIs('admin.analytics')?'active':'' }}" href="{{ route('admin.analytics') }}">Analytics</a>
            <a class="{{ request()->routeIs('admin.audit')?'active':'' }}" href="{{ route('admin.audit') }}">Audit log</a>
            <a class="{{ request()->routeIs('staff.*')?'active':'' }}" href="{{ route('staff.appointments') }}">Hospital operations</a>
            <a class="{{ request()->routeIs('staff.walk-in*')?'active':'' }}" href="{{ route('staff.walk-in') }}">Walk-in booking</a>
            <a class="{{ request()->routeIs('staff.walkin-receipts')?'active':'' }}" href="{{ route('staff.walkin-receipts') }}">Walk-in receipts</a>
            <a href="{{ route('staff.patient-links') }}">Patient account links</a>
            <a href="{{ route('staff.lab-bookings') }}">Lab bookings &amp; prices</a>
        @endif
    </nav>
    <div class="sidebar-foot">
        <a class="user-chip account-chip" href="{{ route('account.edit') }}">
            <strong>Account settings</strong>
        </a>
        <form action="{{ route('logout') }}" method="post">@csrf<button class="btn btn-ghost btn-block" type="submit">Sign out</button></form>
    </div>
</aside>
<div class="sidebar-backdrop" data-sidebar-close></div>
<main class="main-shell">
    <header class="topbar">
        <button class="icon-btn menu-toggle" type="button" aria-label="Open menu">☰</button>
        <div><span class="eyebrow">MEDILAND HOSPITAL</span><h1>@yield('page-title','Dashboard')</h1></div>
        <div class="top-actions"><span class="user-welcome">Welcome, <strong>{{ auth()->user()->full_name }}</strong></span><a class="notification-link" href="{{ route('account.notifications') }}" aria-label="Notifications" title="Notifications"><svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"/><path d="M10 21h4"/></svg>@php($unread=auth()->user()->notifications()->whereNull('read_at')->count()) @if($unread)<b>{{ $unread>99?'99+':$unread }}</b>@endif</a><span class="secure-pill">● Secure session</span></div>
    </header>
    <div class="content-wrap">
        @if(session('success'))<div class="alert alert-success"><span>✓</span><div>{{ session('success') }}</div></div>@endif
        @if(session('warning'))<div class="alert alert-warning"><span>!</span><div>{{ session('warning') }}</div></div>@endif
        @if($errors->any())
            <div class="alert alert-error"><span>!</span><div><strong>Please correct the following:</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div></div>
        @endif
        @yield('content')
        @if(request()->routeIs('patient.dashboard'))
            @include('components.home-chatbot')
        @endif
    </div>
</main>
<script src="{{ asset('assets/js/app.js') }}"></script>
@if(request()->routeIs('patient.dashboard'))
<script src="{{ asset('assets/js/home-chatbot.js') }}"></script>
@endif
@stack('scripts')
</body>
</html>
