 <?php $__env->startSection('page-title','My Appointments'); ?> <?php $__env->startSection('content'); ?><section class="panel">
    <h2>Appointments by date &amp; time</h2>
    <p class="muted">Newest appointments first. Click a date, patient name or Patient details to view their medical record.</p>
    <form method="get" action="<?php echo e(route('doctor.appointments')); ?>" class="filter-bar">
        <label>Appointment date<input type="date" name="date" value="<?php echo e(request('date')); ?>"></label>
        <label>Patient ID<input type="text" name="patient_id" value="<?php echo e(request('patient_id')); ?>" maxlength="50" placeholder="e.g. 0321"></label>
        <button class="btn btn-primary" type="submit">Show appointments</button>
        <a class="btn btn-secondary" href="<?php echo e(route('doctor.appointments')); ?>">Clear filters</a>
    </form>
</section><div class="table-card"><div class="table-scroll"><table><thead><tr><th>Date & time</th><th>Patient</th><th>ID</th><th>Status</th><th>Clinical record</th></tr></thead><tbody><?php $__empty_1 = true; $__currentLoopData = $appointments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $a): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><tr><td><a href="<?php echo e(route('doctor.history',$a->patient_id)); ?>"><?php echo e($a->slot?->slot_start?->format('d M Y h:i A')); ?></a></td><td><a href="<?php echo e(route('doctor.history',$a->patient_id)); ?>"><?php echo e($a->patient->full_name); ?></a></td><td><?php echo e($a->patient->patient_code); ?></td><td><?php if (isset($component)) { $__componentOriginal51ed764111e345fc11534f121cfeb451 = $component; } ?>
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
<?php endif; ?></td><td><div class="action-group"><a class="btn btn-secondary btn-sm" href="<?php echo e(route('doctor.history',$a->patient_id)); ?>">Patient details</a><?php if(in_array($a->status,['CONFIRMED','CHECKED_IN']) && !$a->isWithinConsultationDate()): ?><span class="muted">Consultation unavailable: appointment date passed or missing</span><?php elseif(in_array($a->status,['CONFIRMED','CHECKED_IN'])): ?><a class="btn btn-primary btn-sm" href="<?php echo e(route('doctor.consultation',$a)); ?>">Consult</a><?php elseif($a->consultation): ?><span class="muted">Completed — use History</span><?php elseif($a->status==='BOOKED'): ?><span class="muted">Awaiting confirmation</span><?php endif; ?></div></td></tr><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><tr><td colspan="5">No appointments assigned.</td></tr><?php endif; ?></tbody></table></div></div><?php echo e($appointments->links()); ?><?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\medicare\resources\views\doctor\appointments.blade.php ENDPATH**/ ?>