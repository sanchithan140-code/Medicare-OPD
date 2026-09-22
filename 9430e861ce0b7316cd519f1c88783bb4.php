 <?php $__env->startSection('page-title','Patient Dashboard'); ?> <?php $__env->startSection('content'); ?><section class="welcome-banner">
<div>
<span class="kicker">Welcome, <?php echo e(auth()->user()->full_name); ?></span>
<h2>Your healthcare, organized in one place.</h2>
<p>Every step counts. Choose a family profile, find available doctors and move forward with trusted care guidance.</p>
</div>
<div class="quick-actions">
<a class="btn btn-light" href="<?php echo e(route('patient.ai')); ?>">✦ Open Care Guide</a>
<a class="btn btn-secondary" href="<?php echo e(route('patient.appointments.book')); ?>">Book appointment</a>
</div>
</section>
<div class="stat-grid stat-grid-4">
<div class="stat-card">
<span>Family profiles</span>
<strong><?php echo e($patients->count()); ?></strong>
<a href="<?php echo e(route('patient.profiles')); ?>">Manage profiles</a>
</div>
<div class="stat-card">
<span>Upcoming appointments</span>
<strong><?php echo e($upcoming->count()); ?></strong>
<a href="<?php echo e(route('patient.appointments')); ?>">View all</a>
</div>
<div class="stat-card">
<span>Unread notices</span>
<strong><?php echo e($notifications->whereNull('read_at')->count()); ?></strong>
<a href="<?php echo e(route('patient.notifications')); ?>">Open inbox</a>
</div>
<div class="stat-card">
<span>Health trend items</span>
<strong><?php echo e($trends->count()); ?></strong>
<a href="#trends">Review trends</a>
</div>
</div>
<div class="dashboard-grid">
<section class="panel">
<div class="panel-head">
<div>
<span class="kicker">NEXT VISITS</span>
<h2>Upcoming appointments</h2>
</div>
<a class="btn btn-sm btn-primary" href="<?php echo e(route('patient.appointments.book')); ?>">New booking</a>
</div><?php $__empty_1 = true; $__currentLoopData = $upcoming; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $a): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><article class="appointment-row">
<div class="date-tile">
<strong><?php echo e(optional($a->slot?->slot_start)->format('d')); ?></strong>
<span><?php echo e(optional($a->slot?->slot_start)->format('M')); ?></span>
</div>
<div>
<h3><?php echo e($a->doctor->user->full_name); ?></h3>
<p><?php echo e($a->doctor->specialty->specialty_name); ?> · <?php echo e(optional($a->slot?->slot_start)->format('h:i A')); ?></p>
<small><?php echo e($a->patient->full_name); ?> · <?php echo e($a->appointment_no); ?></small>
</div>
<?php if (isset($component)) { $__componentOriginal51ed764111e345fc11534f121cfeb451 = $component; } ?>
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
<?php endif; ?>
</article><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if (isset($component)) { $__componentOriginal4f22a152e0729cd34293e65bd200d933 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4f22a152e0729cd34293e65bd200d933 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.empty','data' => ['title' => 'No upcoming appointments','message' => 'A healthier tomorrow can start today—choose a doctor or use the care guide to begin.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('empty'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'No upcoming appointments','message' => 'A healthier tomorrow can start today—choose a doctor or use the care guide to begin.']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal4f22a152e0729cd34293e65bd200d933)): ?>
<?php $attributes = $__attributesOriginal4f22a152e0729cd34293e65bd200d933; ?>
<?php unset($__attributesOriginal4f22a152e0729cd34293e65bd200d933); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal4f22a152e0729cd34293e65bd200d933)): ?>
<?php $component = $__componentOriginal4f22a152e0729cd34293e65bd200d933; ?>
<?php unset($__componentOriginal4f22a152e0729cd34293e65bd200d933); ?>
<?php endif; ?><?php endif; ?></section>
<section class="panel" id="trends">
<div class="panel-head">
<div>
<span class="kicker">WEEKLY AWARENESS</span>
<h2>Health trends</h2>
</div>
</div><?php $__empty_1 = true; $__currentLoopData = $trends; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><div class="trend-row">
<div>
<strong><?php echo e($t->condition->condition_name); ?></strong>
<small>Week of <?php echo e($t->week_start?->format('d M Y')); ?></small>
</div>
<b><?php echo e($t->percentage); ?>%</b>
</div><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><p class="muted">Trend analytics will appear after completed consultations are aggregated.</p><?php endif; ?>
</section>
</div><?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\medicare\resources\views\patient\dashboard.blade.php ENDPATH**/ ?>