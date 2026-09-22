 <?php $__env->startSection('page-title','Schedule & Leave'); ?> <?php $__env->startSection('content'); ?><div class="dashboard-grid align-start"><section class="panel"><div class="panel-head"><h2>Consultation schedules</h2></div><?php $__empty_1 = true; $__currentLoopData = $schedules; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><article class="schedule-card" data-schedule-expires="<?php echo e(\Carbon\Carbon::parse($s->schedule_date->toDateString().' '.$s->end_time)->getTimestamp()); ?>"><div><strong><?php echo e($s->schedule_date?->format('D, d M Y')); ?></strong><p><?php echo e(\Carbon\Carbon::parse($s->start_time)->format('h:i A')); ?>–<?php echo e(\Carbon\Carbon::parse($s->end_time)->format('h:i A')); ?> · Room <?php echo e($s->room_no ?: 'TBA'); ?></p></div><div><?php if (isset($component)) { $__componentOriginal51ed764111e345fc11534f121cfeb451 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal51ed764111e345fc11534f121cfeb451 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.status','data' => ['value' => $s->status]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('status'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($s->status)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal51ed764111e345fc11534f121cfeb451)): ?>
<?php $attributes = $__attributesOriginal51ed764111e345fc11534f121cfeb451; ?>
<?php unset($__attributesOriginal51ed764111e345fc11534f121cfeb451); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal51ed764111e345fc11534f121cfeb451)): ?>
<?php $component = $__componentOriginal51ed764111e345fc11534f121cfeb451; ?>
<?php unset($__componentOriginal51ed764111e345fc11534f121cfeb451); ?>
<?php endif; ?><small><?php echo e($s->slots->where('slot_status','AVAILABLE')->count()); ?> available</small></div></article><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if (isset($component)) { $__componentOriginal4f22a152e0729cd34293e65bd200d933 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4f22a152e0729cd34293e65bd200d933 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.empty','data' => ['title' => 'No schedules created']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('empty'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'No schedules created']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal4f22a152e0729cd34293e65bd200d933)): ?>
<?php $attributes = $__attributesOriginal4f22a152e0729cd34293e65bd200d933; ?>
<?php unset($__attributesOriginal4f22a152e0729cd34293e65bd200d933); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal4f22a152e0729cd34293e65bd200d933)): ?>
<?php $component = $__componentOriginal4f22a152e0729cd34293e65bd200d933; ?>
<?php unset($__componentOriginal4f22a152e0729cd34293e65bd200d933); ?>
<?php endif; ?><?php endif; ?><?php echo e($schedules->links()); ?></section><div class="doctor-leave-column">
    <section class="panel doctor-leave-panel">
        <h2>Request leave</h2>
        <p class="muted leave-intro">Approved leave blocks new booking slots and alerts staff to affected appointments.</p>
        <form method="post" action="<?php echo e(route('doctor.leave.store')); ?>">
            <?php echo csrf_field(); ?>
            <div class="leave-date-fields">
                <label>Start date &amp; time
                    <input type="datetime-local" name="start_at" value="<?php echo e(old('start_at')); ?>" required>
                </label>
                <label>End date &amp; time
                    <input type="datetime-local" name="end_at" value="<?php echo e(old('end_at')); ?>" required>
                </label>
            </div>
            <label>Reason
                <textarea name="reason" rows="4" placeholder="Briefly explain the reason for your leave" required><?php echo e(old('reason')); ?></textarea>
            </label>
            <div class="leave-form-actions">
                <button class="btn btn-primary" type="submit">Submit request</button>
            </div>
        </form>
    </section>
    <section class="panel doctor-leave-history">
        <h2>Recent requests</h2>
        <?php $__empty_1 = true; $__currentLoopData = $leaves; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <article class="leave-history-item">
                <div>
                    <strong><?php echo e($l->start_at?->format('d M Y, h:i A')); ?></strong>
                    <small>to <?php echo e($l->end_at?->format('d M Y, h:i A')); ?></small>
                    <p><?php echo e($l->reason); ?></p>
                </div>
                <?php if (isset($component)) { $__componentOriginal51ed764111e345fc11534f121cfeb451 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal51ed764111e345fc11534f121cfeb451 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.status','data' => ['value' => $l->status]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('status'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($l->status)]); ?>
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
            </article>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <p class="leave-empty">No leave requests yet. Your submitted requests will appear here.</p>
        <?php endif; ?>
    </section>
</div>
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

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\medicare\resources\views\doctor\schedule.blade.php ENDPATH**/ ?>