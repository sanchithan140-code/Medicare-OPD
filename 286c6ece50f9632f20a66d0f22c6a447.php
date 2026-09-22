 <?php $__env->startSection('page-title','Office Staff Dashboard'); ?> <?php $__env->startSection('content'); ?><section class="welcome-banner"><div><span class="kicker">FRONT-OFFICE OPERATIONS</span><h2>Coordinate appointments, patients, billing and support.</h2><p>Confirm bookings, check in patients, assist walk-ins, record payments and respond to requests.</p></div></section><div class="stat-grid stat-grid-4"><div class="stat-card"><span>Pending bookings</span><strong><?php echo e($pending); ?></strong></div><div class="stat-card"><span>Appointments today</span><strong><?php echo e($today); ?></strong></div><div class="stat-card"><span>Unresolved support</span><strong><?php echo e($unresolved); ?></strong></div><div class="stat-card"><span>Payments today</span><strong>Rs. <?php echo e(number_format($paymentsToday,2)); ?></strong></div></div><section class="panel"><div class="panel-head"><h2>Latest appointments</h2><a class="btn btn-primary btn-sm" href="<?php echo e(route('staff.appointments')); ?>">Manage all</a></div><div class="table-scroll"><table><thead><tr><th>Reference</th><th>Patient</th><th>Doctor</th><th>Date & time</th><th>Status</th></tr></thead><tbody><?php $__empty_1 = true; $__currentLoopData = $appointments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $a): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><tr><td><?php echo e($a->appointment_no); ?></td><td><?php echo e($a->patient->full_name); ?></td><td><?php echo e($a->doctor->user->full_name); ?></td><td><?php echo e($a->slot?->slot_start?->format('d M Y h:i A')); ?></td><td><?php if (isset($component)) { $__componentOriginal51ed764111e345fc11534f121cfeb451 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal51ed764111e345fc11534f121cfeb451 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.status','data' => ['value' => $a->status]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('status'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($a->status)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal51ed764111e345fc11534f121cfeb451)): ?>
<?php $attributes = $__attributesOriginal51ed764111e345fc11534f121cfeb451; ?>
<?php unset($__attributesOriginal51ed764111e345fc11534f121cfeb451); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal51ed764111e345fc11534f121cfeb451)): ?>
<?php $component = $__componentOriginal51ed764111e345fc11534f121cfeb451; ?>
<?php unset($__componentOriginal51ed764111e345fc11534f121cfeb451); ?>
<?php endif; ?></td></tr><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><tr><td colspan="5">No appointments.</td></tr><?php endif; ?></tbody></table></div></section><?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\medicare\resources\views\staff\dashboard.blade.php ENDPATH**/ ?>