 <?php $__env->startSection('page-title','Doctors, Schedules & Leave'); ?> <?php $__env->startSection('content'); ?><div class="dashboard-grid align-start"><div style="display:grid;gap:24px;align-content:start"><section class="panel"><h2>Create doctor</h2><form method="post" action="<?php echo e(route('admin.doctors.store')); ?>"><?php echo csrf_field(); ?><label>Doctor name<input name="full_name" value="<?php echo e(old('full_name')); ?>" maxlength="150" required></label><label>Email / username<input type="email" name="email" value="<?php echo e(old('email')); ?>" maxlength="190" autocomplete="off" required></label><label>Password<input type="password" name="password" minlength="8" autocomplete="new-password" required><small>Use at least 8 characters with uppercase, lowercase, numbers and symbols.</small></label><label>Specialty<select name="specialty_id"><?php $__currentLoopData = $specialties; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($s->specialty_id); ?>" <?php if(old('specialty_id') == $s->specialty_id): echo 'selected'; endif; ?>><?php echo e($s->specialty_name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></label><label>Registration number<input name="registration_no" value="<?php echo e(old('registration_no')); ?>"></label><label>Qualifications<input name="qualifications_summary" value="<?php echo e(old('qualifications_summary')); ?>"></label><label>Consultation fee<input type="number" step=".01" name="consultation_fee" value="<?php echo e(old('consultation_fee')); ?>" required></label><label>Biography<textarea name="bio"><?php echo e(old('bio')); ?></textarea></label><button class="btn btn-primary">Create doctor</button></form></section><section class="panel"><h2>Create schedule & slots</h2><form method="post" action="<?php echo e(route('admin.schedules.store')); ?>"><?php echo csrf_field(); ?><label>Doctor<select name="doctor_id"><?php $__currentLoopData = $doctors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($d->doctor_id); ?>"><?php echo e($d->user->full_name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></label><div class="form-grid"><label>Date<input type="date" name="schedule_date" min="<?php echo e(today()->toDateString()); ?>" required></label><label>Room<input name="room_no"></label><label>Start<input type="time" name="start_time" required></label><label>End<input type="time" name="end_time" required></label><label>Slot minutes<input type="number" name="slot_minutes" value="15" required></label><label>Capacity<input type="number" name="capacity" value="12" required></label></div><button class="btn btn-primary">Generate schedule</button></form></section></div><section><h2>Doctors</h2><?php $__currentLoopData = $doctors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<details class="panel admin-doctor-schedules">
    <summary class="doctor-list-item">
        <div class="doctor-avatar"><?php echo e(strtoupper(substr($d->user->full_name,0,1))); ?></div>
        <div>
            <h3><?php echo e($d->user->full_name); ?></h3>
            <p><?php echo e($d->specialty->specialty_name); ?> &middot; Rs. <?php echo e(number_format($d->consultation_fee,2)); ?></p>
            <small><?php echo e($d->schedules->count()); ?> schedule(s) <span class="schedule-toggle-label">View schedules <span aria-hidden="true">&#9662;</span></span></small>
        </div>
        <?php if (isset($component)) { $__componentOriginal51ed764111e345fc11534f121cfeb451 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal51ed764111e345fc11534f121cfeb451 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.status','data' => ['value' => $d->active?'ACTIVE':'INACTIVE']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('status'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($d->active?'ACTIVE':'INACTIVE')]); ?>
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
    </summary>
    <div class="admin-doctor-schedule-list">
        <?php $__empty_1 = true; $__currentLoopData = $d->schedules->sortBy([['schedule_date', 'asc'], ['start_time', 'asc']]); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $schedule): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div class="schedule-card">
                <div>
                    <strong><?php echo e($schedule->schedule_date->format('D, d M Y')); ?></strong>
                    <p><?php echo e(\Carbon\Carbon::parse($schedule->start_time)->format('h:i A')); ?> &ndash; <?php echo e(\Carbon\Carbon::parse($schedule->end_time)->format('h:i A')); ?></p>
                    <small><?php echo e($schedule->room_no ? 'Room: '.$schedule->room_no : 'Room not assigned'); ?></small>
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
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <p>No schedules created for this doctor yet.</p>
        <?php endif; ?>
    </div>
</details>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php echo e($doctors->links()); ?><h2>Leave requests</h2><?php $__currentLoopData = $leaves; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><article class="panel"><div class="panel-head"><div><h3><?php echo e($l->doctor->user->full_name); ?></h3><p><?php echo e($l->start_at?->format('d M Y h:i A')); ?> – <?php echo e($l->end_at?->format('d M Y h:i A')); ?></p><small><?php echo e($l->reason); ?></small></div><?php if (isset($component)) { $__componentOriginal51ed764111e345fc11534f121cfeb451 = $component; } ?>
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
<?php endif; ?></div><?php if($l->status==='REQUESTED'): ?><div class="action-group"><?php $__currentLoopData = ['APPROVED','REJECTED']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><form method="post" action="<?php echo e(route('admin.leave.decide',$l)); ?>"><?php echo csrf_field(); ?><input type="hidden" name="status" value="<?php echo e($v); ?>"><button class="btn <?php echo e($v==='APPROVED'?'btn-primary':'btn-danger'); ?> btn-sm"><?php echo e(ucfirst(strtolower($v))); ?></button></form><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></div><?php endif; ?></article><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></section></div><?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\medicare\resources\views/admin/doctors.blade.php ENDPATH**/ ?>