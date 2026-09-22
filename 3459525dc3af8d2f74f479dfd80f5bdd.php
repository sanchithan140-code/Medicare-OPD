<?php $__env->startSection('page-title','Doctor Schedules & Slots'); ?>
<?php $__env->startSection('content'); ?>
<div class="dashboard-grid align-start">
    <section class="panel">
        <h2>Create schedule & slots</h2>
        <form method="post" action="<?php echo e(route('staff.schedules.store')); ?>">
            <?php echo csrf_field(); ?>
            <label>Doctor
                <select name="doctor_id" required>
                    <option value="">Select doctor</option>
                    <?php $__currentLoopData = $doctors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $doctor): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($doctor->doctor_id); ?>" <?php if(old('doctor_id')==$doctor->doctor_id): echo 'selected'; endif; ?>><?php echo e($doctor->user->full_name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </label>
            <div class="form-grid">
                <label>Date<input type="date" name="schedule_date" value="<?php echo e(old('schedule_date')); ?>" min="<?php echo e(now()->toDateString()); ?>" required></label>
                <label>Room<input name="room_no" value="<?php echo e(old('room_no')); ?>" maxlength="30"></label>
                <label>Start<input type="time" name="start_time" value="<?php echo e(old('start_time')); ?>" required></label>
                <label>End<input type="time" name="end_time" value="<?php echo e(old('end_time')); ?>" required></label>
                <label>Slot minutes<input type="number" name="slot_minutes" value="<?php echo e(old('slot_minutes',15)); ?>" min="5" max="60" required></label>
                <label>Capacity<input type="number" name="capacity" value="<?php echo e(old('capacity',12)); ?>" min="1" max="100" required></label>
            </div>
            <button class="btn btn-primary">Generate schedule</button>
        </form>
    </section>
    <section class="recent-schedules">
        <h2>Recent schedules</h2>
        <?php $__empty_1 = true; $__currentLoopData = $schedules; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $schedule): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <article class="panel schedule-card">
                <div>
                    <strong><?php echo e($schedule->doctor->user->full_name); ?></strong>
                    <p><?php echo e($schedule->schedule_date?->format('D, d M Y')); ?> · <?php echo e(\Carbon\Carbon::parse($schedule->start_time)->format('h:i A')); ?>–<?php echo e(\Carbon\Carbon::parse($schedule->end_time)->format('h:i A')); ?></p>
                    <small>Room <?php echo e($schedule->room_no ?: 'TBA'); ?> · <?php echo e($schedule->slots->where('slot_status','AVAILABLE')->count()); ?> available slot(s)</small>
                </div>
                <?php if (isset($component)) { $__componentOriginal51ed764111e345fc11534f121cfeb451 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal51ed764111e345fc11534f121cfeb451 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.status','data' => ['value' => $schedule->status]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('status'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($schedule->status)]); ?>
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
            <?php if (isset($component)) { $__componentOriginal4f22a152e0729cd34293e65bd200d933 = $component; } ?>
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
<?php endif; ?>
        <?php endif; ?>
        <?php echo e($schedules->links()); ?>

    </section>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\medicare\resources\views\staff\schedules.blade.php ENDPATH**/ ?>