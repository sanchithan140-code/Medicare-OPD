<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <meta name="theme-color" content="#073b72">
    <title><?php echo $__env->yieldContent('title','MEDICARE'); ?></title>
    <script>
        // Recheck authentication when the browser restores an in-memory page.
        window.addEventListener('pagehide', function (event) {
            if (event.persisted) document.documentElement.style.visibility = 'hidden';
        });
        window.addEventListener('pageshow', function (event) {
            if (event.persisted) window.location.reload();
        });
    </script>
    <link rel="icon" type="image/png" sizes="512x512" href="<?php echo e(asset('assets/images/brand/medicare-app-icon.png')); ?>?v=4">
    <link rel="apple-touch-icon" href="<?php echo e(asset('assets/images/brand/medicare-app-icon.png')); ?>?v=4">
    <link rel="stylesheet" href="<?php echo e(asset('assets/css/app.css')); ?>">
    <?php echo $__env->yieldPushContent('styles'); ?>
</head>
<body class="app-shell">
<?php ($role = auth()->user()->role->role_name); ?>
<aside class="sidebar" aria-label="Portal navigation">
    <a class="brand brand-light" href="<?php echo e(route(auth()->user()->dashboardRoute())); ?>">
        <img class="brand-logo" src="<?php echo e(asset('assets/images/brand/mediland-hospital-logo.png')); ?>" alt="Mediland Hospital logo">
        <span><strong>MEDICARE</strong><small><?php echo e(auth()->user()->role->display_name ?? 'Portal'); ?></small></span>
    </a>
    <nav class="side-nav">
        <?php if($role==='patient'): ?>
            <a class="<?php echo e(request()->routeIs('patient.dashboard')?'active':''); ?>" href="<?php echo e(route('patient.dashboard')); ?>">Dashboard</a>
            <a class="<?php echo e(request()->routeIs('patient.profiles*')?'active':''); ?>" href="<?php echo e(route('patient.profiles')); ?>">Family profiles</a>
            <a class="<?php echo e(request()->routeIs('patient.ai*')?'active':''); ?>" href="<?php echo e(route('patient.ai')); ?>">Care Guide</a>
            <a class="<?php echo e(request()->routeIs('patient.doctors')?'active':''); ?>" href="<?php echo e(route('patient.doctors')); ?>">Find doctors</a>
            <a class="<?php echo e(request()->routeIs('patient.appointments*')?'active':''); ?>" href="<?php echo e(route('patient.appointments')); ?>">Appointments</a>
            <a class="<?php echo e(request()->routeIs('patient.history')?'active':''); ?>" href="<?php echo e(route('patient.history')); ?>">Medical history</a>
            <a class="<?php echo e(request()->routeIs('patient.labs')?'active':''); ?>" href="<?php echo e(route('patient.labs')); ?>">Lab reports</a>
            <a class="<?php echo e(request()->routeIs('patient.payments')?'active':''); ?>" href="<?php echo e(route('patient.payments')); ?>">Payments</a>
            <a class="<?php echo e(request()->routeIs('patient.insurance')?'active':''); ?>" href="<?php echo e(route('patient.insurance')); ?>">Insurance</a>
            <a class="<?php echo e(request()->routeIs('patient.notifications')?'active':''); ?>" href="<?php echo e(route('patient.notifications')); ?>">Notifications</a>
            <a class="<?php echo e(request()->routeIs('patient.support')?'active':''); ?>" href="<?php echo e(route('patient.support')); ?>">Support</a>
        <?php elseif($role==='doctor'): ?>
            <a class="<?php echo e(request()->routeIs('doctor.dashboard')?'active':''); ?>" href="<?php echo e(route('doctor.dashboard')); ?>">Dashboard</a>
            <a class="<?php echo e(request()->routeIs('doctor.appointments*')||request()->routeIs('doctor.consultation*')?'active':''); ?>" href="<?php echo e(route('doctor.appointments')); ?>">Appointments</a>
            <a class="<?php echo e(request()->routeIs('doctor.patients','doctor.history')?'active':''); ?>" href="<?php echo e(route('doctor.patients')); ?>">Patient records</a>
            <a class="<?php echo e(request()->routeIs('doctor.schedule')?'active':''); ?>" href="<?php echo e(route('doctor.schedule')); ?>">Schedule & leave</a>
        <?php elseif($role==='lab_officer'): ?>
            <a class="<?php echo e(request()->routeIs('lab.dashboard')?'active':''); ?>" href="<?php echo e(route('lab.dashboard')); ?>">Dashboard</a>
            <a class="<?php echo e(request()->routeIs('lab.orders*')||request()->routeIs('lab.result*')?'active':''); ?>" href="<?php echo e(route('lab.orders')); ?>">Test orders</a>
        <?php elseif($role==='office_staff'): ?>
            <a class="<?php echo e(request()->routeIs('staff.dashboard')?'active':''); ?>" href="<?php echo e(route('staff.dashboard')); ?>">Dashboard</a>
            <a class="<?php echo e(request()->routeIs('staff.appointments*')?'active':''); ?>" href="<?php echo e(route('staff.appointments')); ?>">Appointments</a>
            <a class="<?php echo e(request()->routeIs('staff.walk-in*')?'active':''); ?>" href="<?php echo e(route('staff.walk-in')); ?>">Walk-in booking</a>
            <a class="<?php echo e(request()->routeIs('staff.walkin-receipts')?'active':''); ?>" href="<?php echo e(route('staff.walkin-receipts')); ?>">Walk-in receipts</a>
            <a class="<?php echo e(request()->routeIs('staff.schedules*')?'active':''); ?>" href="<?php echo e(route('staff.schedules')); ?>">Schedules</a>
            <a class="<?php echo e(request()->routeIs('staff.patient-links*')?'active':''); ?>" href="<?php echo e(route('staff.patient-links')); ?>">Patient account links</a>
            <a href="<?php echo e(route('staff.lab-bookings')); ?>">Lab bookings &amp; prices</a>
            <a class="<?php echo e(request()->routeIs('staff.patients')?'active':''); ?>" href="<?php echo e(route('staff.patients')); ?>">Patients</a>
            <a class="<?php echo e(request()->routeIs('staff.billing')?'active':''); ?>" href="<?php echo e(route('staff.billing')); ?>">Billing</a>
            <a class="<?php echo e(request()->routeIs('staff.insurance')?'active':''); ?>" href="<?php echo e(route('staff.insurance')); ?>">Insurance</a>
            <a class="<?php echo e(request()->routeIs('staff.support')?'active':''); ?>" href="<?php echo e(route('staff.support')); ?>">Support</a>
        <?php else: ?>
            <a class="<?php echo e(request()->routeIs('admin.dashboard')?'active':''); ?>" href="<?php echo e(route('admin.dashboard')); ?>">Dashboard</a>
            <a class="<?php echo e(request()->routeIs('admin.users')?'active':''); ?>" href="<?php echo e(route('admin.users')); ?>">Users</a>
            <a class="<?php echo e(request()->routeIs('admin.doctors')?'active':''); ?>" href="<?php echo e(route('admin.doctors')); ?>">Doctors & schedules</a>
            <a class="<?php echo e(request()->routeIs('admin.catalogs')?'active':''); ?>" href="<?php echo e(route('admin.catalogs')); ?>">Catalogs</a>
            <a class="<?php echo e(request()->routeIs('admin.analytics')?'active':''); ?>" href="<?php echo e(route('admin.analytics')); ?>">Analytics</a>
            <a class="<?php echo e(request()->routeIs('admin.audit')?'active':''); ?>" href="<?php echo e(route('admin.audit')); ?>">Audit log</a>
            <a class="<?php echo e(request()->routeIs('staff.*')?'active':''); ?>" href="<?php echo e(route('staff.appointments')); ?>">Hospital operations</a>
            <a class="<?php echo e(request()->routeIs('staff.walk-in*')?'active':''); ?>" href="<?php echo e(route('staff.walk-in')); ?>">Walk-in booking</a>
            <a class="<?php echo e(request()->routeIs('staff.walkin-receipts')?'active':''); ?>" href="<?php echo e(route('staff.walkin-receipts')); ?>">Walk-in receipts</a>
            <a href="<?php echo e(route('staff.patient-links')); ?>">Patient account links</a>
            <a href="<?php echo e(route('staff.lab-bookings')); ?>">Lab bookings &amp; prices</a>
        <?php endif; ?>
    </nav>
    <div class="sidebar-foot">
        <a class="user-chip account-chip" href="<?php echo e(route('account.edit')); ?>">
            <strong>Account settings</strong>
        </a>
        <form action="<?php echo e(route('logout')); ?>" method="post"><?php echo csrf_field(); ?><button class="btn btn-ghost btn-block" type="submit">Sign out</button></form>
    </div>
</aside>
<div class="sidebar-backdrop" data-sidebar-close></div>
<main class="main-shell">
    <header class="topbar">
        <button class="icon-btn menu-toggle" type="button" aria-label="Open menu">☰</button>
        <div><span class="eyebrow">MEDILAND HOSPITAL</span><h1><?php echo $__env->yieldContent('page-title','Dashboard'); ?></h1></div>
        <div class="top-actions"><span class="user-welcome">Welcome, <strong><?php echo e(auth()->user()->full_name); ?></strong></span><a class="notification-link" href="<?php echo e(route('account.notifications')); ?>" aria-label="Notifications" title="Notifications"><svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"/><path d="M10 21h4"/></svg><?php ($unread=auth()->user()->notifications()->whereNull('read_at')->count()); ?> <?php if($unread): ?><b><?php echo e($unread>99?'99+':$unread); ?></b><?php endif; ?></a><span class="secure-pill">● Secure session</span></div>
    </header>
    <div class="content-wrap">
        <?php if(session('success')): ?><div class="alert alert-success"><span>✓</span><div><?php echo e(session('success')); ?></div></div><?php endif; ?>
        <?php if(session('warning')): ?><div class="alert alert-warning"><span>!</span><div><?php echo e(session('warning')); ?></div></div><?php endif; ?>
        <?php if($errors->any()): ?>
            <div class="alert alert-error"><span>!</span><div><strong>Please correct the following:</strong><ul><?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><li><?php echo e($error); ?></li><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></ul></div></div>
        <?php endif; ?>
        <?php echo $__env->yieldContent('content'); ?>
        <?php if(request()->routeIs('patient.dashboard')): ?>
            <?php echo $__env->make('components.home-chatbot', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php endif; ?>
    </div>
</main>
<script src="<?php echo e(asset('assets/js/app.js')); ?>"></script>
<?php if(request()->routeIs('patient.dashboard')): ?>
<script src="<?php echo e(asset('assets/js/home-chatbot.js')); ?>"></script>
<?php endif; ?>
<?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\medicare\resources\views/layouts/app.blade.php ENDPATH**/ ?>