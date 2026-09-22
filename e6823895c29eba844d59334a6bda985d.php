 <?php $__env->startSection('page-title','Doctor Dashboard'); ?> <?php $__env->startSection('content'); ?><section class="welcome-banner"><div><span class="kicker"><?php echo e($doctor->specialty->specialty_name); ?></span><h2>Good day, <?php echo e($doctor->user->full_name); ?>.</h2><p>Review today’s appointments, waiting patients and clinical work securely.</p></div></section><div class="stat-grid stat-grid-3"><div class="stat-card"><span>Today’s appointments</span><strong><?php echo e($appointments->count()); ?></strong></div><div class="stat-card"><span>Patients waiting</span><strong><?php echo e($waiting); ?></strong></div><div class="stat-card"><span>Completed today</span><strong><?php echo e($completedToday); ?></strong></div></div><section class="panel"><div class="panel-head"><div><span class="kicker">TODAY</span><h2>Patient list</h2></div><a class="btn btn-secondary btn-sm" href="<?php echo e(route('doctor.appointments')); ?>">All appointments</a></div><div class="table-scroll"><table><thead><tr><th>Time</th><th>Patient</th><th>Patient ID</th><th>Status</th><th>Action</th></tr></thead><tbody><?php $__empty_1 = true; $__currentLoopData = $appointments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $a): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><tr><td><?php echo e($a->slot?->slot_start?->format('h:i A')); ?></td><td><?php echo e($a->patient->full_name); ?></td><td><?php echo e($a->patient->patient_code); ?></td><td><?php if (isset($component)) { $__componentOriginal51ed764111e345fc11534f121cfeb451 = $component; } ?>
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
<?php endif; ?></td><td><?php if(in_array($a->status,['CONFIRMED','CHECKED_IN']) && !$a->isWithinConsultationDate()): ?><span class="muted">Consultation unavailable: appointment date passed or missing</span><?php elseif(in_array($a->status,['CONFIRMED','CHECKED_IN'])): ?><a class="btn btn-primary btn-sm" href="<?php echo e(route('doctor.consultation',$a)); ?>">Open consultation</a><?php else: ?><span class="muted">Awaiting confirmation</span><?php endif; ?></td></tr><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><tr><td colspan="5">No appointments scheduled today.</td></tr><?php endif; ?></tbody></table></div></section><?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\medicare\resources\views/doctor/dashboard.blade.php ENDPATH**/ ?>