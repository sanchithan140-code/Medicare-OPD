<?php $__env->startSection('page-title','Patient Account Links'); ?>
<?php $__env->startSection('content'); ?>
<section class="panel"><h2>Verify an existing patient record</h2><p>Check the patient in person against their identification and existing hospital record. A patient ID alone is not proof of identity.</p><a href="<?php echo e(route('staff.patients')); ?>">Open patient directory</a></section>
<?php $__empty_1 = true; $__currentLoopData = $requests; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $account): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
<section class="panel"><h3><?php echo e($account->full_name); ?></h3><p>Verified email: <?php echo e($account->email); ?> ? Phone: <?php echo e($account->phone); ?></p><p>Requested patient ID: <?php echo e($account->requested_patient_code); ?></p>
<form method="post" action="<?php echo e(route('staff.patient-links.store',$account)); ?>"><?php echo csrf_field(); ?>
<?php ($record=$records->get($account->requested_patient_code)); ?>
<?php if($record): ?><div class="summary-box"><strong>Existing hospital record: <?php echo e($record->full_name); ?></strong><p>Date of birth: <?php echo e($record->dob?->format('d M Y') ?: 'Not recorded'); ?> · Phone: <?php echo e($record->phone ?: 'Not recorded'); ?></p><p>NIC/passport: <?php echo e($record->nic_passport ?: 'Not recorded'); ?></p></div><?php else: ?><p>No record matches the submitted ID. Verify the correct ID with the patient before proceeding.</p><?php endif; ?>
<label>Verified patient ID<input name="patient_code" value="<?php echo e($account->requested_patient_code); ?>" maxlength="4" required></label>
<label>Verification note<textarea name="verification_note" minlength="10" maxlength="500" required></textarea></label>
<label class="check-row"><input type="checkbox" name="identity_verified" value="1" required>I checked the person's identity against the existing hospital record.</label>
<button class="btn btn-primary">Link existing patient record</button>
</form></section>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><section class="panel"><?php if (isset($component)) { $__componentOriginal4f22a152e0729cd34293e65bd200d933 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4f22a152e0729cd34293e65bd200d933 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.empty','data' => ['title' => 'No account links awaiting verification']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('empty'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'No account links awaiting verification']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal4f22a152e0729cd34293e65bd200d933)): ?>
<?php $attributes = $__attributesOriginal4f22a152e0729cd34293e65bd200d933; ?>
<?php unset($__attributesOriginal4f22a152e0729cd34293e65bd200d933); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal4f22a152e0729cd34293e65bd200d933)): ?>
<?php $component = $__componentOriginal4f22a152e0729cd34293e65bd200d933; ?>
<?php unset($__componentOriginal4f22a152e0729cd34293e65bd200d933); ?>
<?php endif; ?></section><?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\medicare\resources\views\staff\patient-links.blade.php ENDPATH**/ ?>