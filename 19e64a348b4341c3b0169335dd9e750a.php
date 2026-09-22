<?php $__env->startSection('page-title','Reschedule Appointment'); ?>
<?php $__env->startSection('content'); ?>
<div class="dashboard-grid align-start">
    <section class="panel">
        <span class="kicker">CURRENT APPOINTMENT</span>
        <h2><?php echo e($appointment->doctor->user->full_name); ?></h2>
        <p><?php echo e($appointment->doctor->specialty->specialty_name); ?></p>
        <div class="detail-grid">
            <div><small>Patient</small><p><?php echo e($appointment->patient->full_name); ?></p></div>
            <div><small>Current time</small><p><?php echo e($appointment->slot?->slot_start?->format('d M Y h:i A')); ?></p></div>
        </div>
    </section>
    <section class="panel">
        <h2>Select a new slot</h2>
        <form method="post" action="<?php echo e(route('patient.appointments.reschedule.store',$appointment)); ?>">
            <?php echo csrf_field(); ?>
            <div class="slot-grid">
                <?php $__empty_1 = true; $__currentLoopData = $slots; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $slot): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <label class="slot-option" data-schedule-expires="<?php echo e(\Carbon\Carbon::parse($slot->schedule->schedule_date->toDateString().' '.$slot->schedule->start_time)->getTimestamp()); ?>">
                        <input type="radio" name="slot_id" value="<?php echo e($slot->slot_id); ?>" required>
                        <span><strong><?php echo e($slot->slot_start->format('h:i A')); ?></strong><small><?php echo e($slot->slot_start->format('d M Y')); ?></small></span>
                    </label>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <p>No future slots are currently available for this doctor.</p>
                <?php endif; ?>
            </div>
            <button class="btn btn-primary btn-block" <?php if($slots->isEmpty()): echo 'disabled'; endif; ?>>Confirm reschedule</button>
        </form>
    </section>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?><?php if (isset($component)) { $__componentOriginal7efc54c829dd0d3789fae18771bdd23a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal7efc54c829dd0d3789fae18771bdd23a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.schedule-expiry-script','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('schedule-expiry-script'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal7efc54c829dd0d3789fae18771bdd23a)): ?>
<?php $attributes = $__attributesOriginal7efc54c829dd0d3789fae18771bdd23a; ?>
<?php unset($__attributesOriginal7efc54c829dd0d3789fae18771bdd23a); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal7efc54c829dd0d3789fae18771bdd23a)): ?>
<?php $component = $__componentOriginal7efc54c829dd0d3789fae18771bdd23a; ?>
<?php unset($__componentOriginal7efc54c829dd0d3789fae18771bdd23a); ?>
<?php endif; ?><?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\medicare\resources\views\patient\reschedule.blade.php ENDPATH**/ ?>