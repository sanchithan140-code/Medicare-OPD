<?php $__env->startSection('page-title','Appointment Operations'); ?>
<?php $__env->startSection('content'); ?>
<section class="panel">
    <div class="panel-head"><h2>Doctor sessions</h2><a class="btn btn-primary" href="<?php echo e(route('staff.appointments.book')); ?>">Book for patient</a></div>
    <form class="filter-bar" method="get" action="<?php echo e(route('staff.appointments')); ?>">
        <label>Doctor
            <select name="doctor_id" id="session-doctor-select">
                <option value="">All doctors</option>
                <?php $__currentLoopData = $doctors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $doctor): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($doctor->doctor_id); ?>" data-registration="<?php echo e($doctor->registration_no); ?>" <?php if(request('doctor_id')==$doctor->doctor_id || (!request('doctor_id') && request('doctor_suffix') && $doctor->registration_no==='SLMC-900'.request('doctor_suffix'))): echo 'selected'; endif; ?>><?php echo e($doctor->user->full_name); ?> &middot; <?php echo e($doctor->specialty->specialty_name); ?> &middot; <?php echo e($doctor->registration_no); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </label>
        <label>Doctor registration ID<div class="doctor-id-input"><span>SLMC-900</span><input name="doctor_suffix" inputmode="numeric" pattern="[0-9]{3}" maxlength="3" placeholder="101" value="<?php echo e(request('doctor_suffix')); ?>" aria-label="Last three digits of doctor registration ID"></div></label>
        <label>Session date<input type="date" name="date" value="<?php echo e(request('date')); ?>"></label>
        <button class="btn btn-primary">Find sessions</button>
        <a class="btn btn-secondary" href="<?php echo e(route('staff.appointments')); ?>">Clear filters</a>
    </form>
    <p class="muted">Click a doctor session to see its patients. Confirm all applies only to pending bookings in that session.</p>
</section>
<?php $__empty_1 = true; $__currentLoopData = $sessions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $session): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
<details class="panel staff-session">
    <summary class="panel-head">
        <div><h3><?php echo e($session->doctor->user->full_name); ?></h3><p><?php echo e($session->doctor->registration_no); ?> &middot; <?php echo e($session->doctor->specialty->specialty_name); ?></p>
        <strong><?php echo e($session->schedule_date->format('D, d M Y')); ?> &middot; <?php echo e(\Carbon\Carbon::parse($session->start_time)->format('h:i A')); ?> &ndash; <?php echo e(\Carbon\Carbon::parse($session->end_time)->format('h:i A')); ?></strong>
        <p>Room <?php echo e($session->room_no ?: 'not assigned'); ?></p></div>
        <span><?php echo e($session->appointments->count()); ?> patient(s) &middot; View patients &#9662;</span>
    </summary>
    <?php ($pendingCount=$session->appointments->whereIn('status',['BOOKED','RESCHEDULED'])->count()); ?>
    <?php if($pendingCount): ?>
    <form method="post" action="<?php echo e(route('staff.appointments.confirm-session',$session)); ?>" class="session-confirm-action"><?php echo csrf_field(); ?>
        <button class="btn btn-primary">Confirm all <?php echo e($pendingCount); ?> pending appointments</button>
    </form>
    <?php endif; ?>
    <div class="table-scroll"><table><thead><tr><th>Time / Reference</th><th>Patient details</th><th>Status</th><th>Update</th></tr></thead><tbody>
    <?php $__currentLoopData = $session->appointments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $appointment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <tr><td><?php echo e($appointment->slot?->slot_start?->format('h:i A')); ?><small><?php echo e($appointment->appointment_no); ?></small></td>
        <td><strong><?php echo e($appointment->patient->full_name); ?></strong><small>Patient ID: <?php echo e($appointment->patient->patient_code); ?></small><small><?php echo e($appointment->patient->phone); ?> &middot; <?php echo e($appointment->patient->account?->is_walk_in ? 'Walk-in patient' : $appointment->patient->account?->email); ?></small></td>
        <td><?php if (isset($component)) { $__componentOriginal51ed764111e345fc11534f121cfeb451 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal51ed764111e345fc11534f121cfeb451 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.status','data' => ['value' => $appointment->status]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('status'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($appointment->status)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal51ed764111e345fc11534f121cfeb451)): ?>
<?php $attributes = $__attributesOriginal51ed764111e345fc11534f121cfeb451; ?>
<?php unset($__attributesOriginal51ed764111e345fc11534f121cfeb451); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal51ed764111e345fc11534f121cfeb451)): ?>
<?php $component = $__componentOriginal51ed764111e345fc11534f121cfeb451; ?>
<?php unset($__componentOriginal51ed764111e345fc11534f121cfeb451); ?>
<?php endif; ?></td><td><?php ($statusOptions = in_array($appointment->status,['BOOKED','RESCHEDULED'],true) ? ['CONFIRMED','NO_SHOW','CANCELLED'] : ($appointment->status==='CONFIRMED' ? ['CHECKED_IN','NO_SHOW','CANCELLED'] : [])); ?>
<?php if($statusOptions): ?><form method="post" action="<?php echo e(route('staff.appointments.status',$appointment)); ?>"><?php echo csrf_field(); ?><div class="inline-input"><select name="status" required><?php $__currentLoopData = $statusOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($value); ?>"><?php echo e(str_replace('_',' ',$value)); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select><input name="reason" maxlength="500" placeholder="Required for cancellation/no-show"><button class="btn btn-primary btn-sm">Save</button></div></form><?php elseif($appointment->status==='CHECKED_IN'): ?><span class="muted">Waiting for doctor</span><?php else: ?><span class="muted">Final state</span><?php endif; ?>
        </td></tr>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </tbody></table></div>
</details>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
<section class="panel"><?php if (isset($component)) { $__componentOriginal4f22a152e0729cd34293e65bd200d933 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4f22a152e0729cd34293e65bd200d933 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.empty','data' => ['title' => 'No doctor sessions with appointments found']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('empty'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'No doctor sessions with appointments found']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal4f22a152e0729cd34293e65bd200d933)): ?>
<?php $attributes = $__attributesOriginal4f22a152e0729cd34293e65bd200d933; ?>
<?php unset($__attributesOriginal4f22a152e0729cd34293e65bd200d933); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal4f22a152e0729cd34293e65bd200d933)): ?>
<?php $component = $__componentOriginal4f22a152e0729cd34293e65bd200d933; ?>
<?php unset($__componentOriginal4f22a152e0729cd34293e65bd200d933); ?>
<?php endif; ?></section>
<?php endif; ?>
<?php echo e($sessions->links()); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
(() => {
    const select = document.getElementById('session-doctor-select');
    const suffix = select.form.elements.doctor_suffix;
    select.addEventListener('change', () => {
        const match = (select.selectedOptions[0].dataset.registration || '').match(/^SLMC-900(\d{3})$/);
        suffix.value = match ? match[1] : '';
    });
    suffix.addEventListener('input', () => {
        const match = Array.from(select.options).find(option => option.dataset.registration === 'SLMC-900' + suffix.value);
        select.value = match ? match.value : '';
    });
})();
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\medicare\resources\views/staff/appointments.blade.php ENDPATH**/ ?>