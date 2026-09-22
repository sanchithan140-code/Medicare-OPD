 <?php $__env->startSection('page-title','My Appointments'); ?> <?php $__env->startSection('content'); ?><div class="page-actions"><div><h2>Appointment history</h2><p class="muted">Cancel or reschedule within seven days of booking, before your consultation.</p></div><a class="btn btn-primary" href="<?php echo e(route('patient.appointments.book')); ?>">Book appointment</a></div><div class="table-card"><div class="table-scroll"><table><thead><tr><th>Reference</th><th>Patient</th><th>Doctor</th><th>Date & time</th><th>Fee</th><th>Status</th><th>Action</th></tr></thead><tbody><?php $__empty_1 = true; $__currentLoopData = $appointments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $a): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><tr><td><?php echo e($a->appointment_no); ?></td><td><?php echo e($a->patient->full_name); ?></td><td><strong><?php echo e($a->doctor->user->full_name); ?></strong><small><?php echo e($a->doctor->specialty->specialty_name); ?></small></td><td><?php echo e($a->slot?->slot_start?->format('d M Y h:i A')); ?></td><td>Rs. <?php echo e(number_format($a->consultation_fee,2)); ?></td><td><?php if (isset($component)) { $__componentOriginal51ed764111e345fc11534f121cfeb451 = $component; } ?>
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
<?php endif; ?></td><td><?php if($a->allowsPatientChanges()): ?><div class="action-group"><a class="btn btn-secondary btn-sm" href="<?php echo e(route('patient.appointments.reschedule',$a)); ?>">Reschedule</a><form method="post" action="<?php echo e(route('patient.appointments.cancel',$a)); ?>" onsubmit="return confirm('Cancel this appointment?')"><?php echo csrf_field(); ?><input type="hidden" name="reason" value="Patient requested cancellation"><button class="btn btn-danger btn-sm">Cancel</button></form></div><?php elseif(in_array($a->status,['BOOKED','CONFIRMED','RESCHEDULED'])): ?><small class="muted">Changes no longer available</small><?php else: ?>&mdash;<?php endif; ?></td></tr><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><tr><td colspan="7">No appointment records.</td></tr><?php endif; ?></tbody></table></div></div><?php echo e($appointments->links()); ?><?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\medicare\resources\views\patient\appointments.blade.php ENDPATH**/ ?>