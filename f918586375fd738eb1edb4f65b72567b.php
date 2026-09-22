<?php $__env->startSection('title','Medical History — MEDICARE'); ?>
<?php $__env->startSection('page-title','Digital Medical History'); ?>
<?php $__env->startSection('content'); ?>
<div class="timeline">
<?php $__empty_1 = true; $__currentLoopData = $consultations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $consultation): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
<article class="timeline-item"><div class="timeline-dot"></div><div class="panel"><div class="panel-head"><div><span class="kicker"><?php echo e($consultation->completed_at?->format('d M Y')); ?></span><h2><?php echo e($consultation->patient->full_name); ?></h2><p class="muted"><?php echo e($consultation->doctor->user->full_name); ?></p></div><span class="patient-code"><?php echo e($consultation->patient->patient_code); ?></span></div>
<div class="detail-grid"><div><small>Diagnosis</small><p><?php echo e($consultation->diagnosis_text); ?></p></div><div><small>Treatment plan</small><p><?php echo e($consultation->treatment_plan); ?></p></div><div><small>Patient-visible summary</small><p><?php echo e($consultation->patient_summary ?: 'Not provided'); ?></p></div><div><small>Follow-up</small><p><?php echo e($consultation->follow_up_date?->format('d M Y') ?: 'As needed'); ?></p></div></div>
<?php if($consultation->conditions->isNotEmpty()): ?><div class="profile-meta"><?php $__currentLoopData = $consultation->conditions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $condition): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><span><?php echo e($condition->condition_name); ?></span><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></div><?php endif; ?>
<?php if($consultation->prescriptions->isNotEmpty()): ?><hr class="divider"><h3>Prescription</h3><?php $__currentLoopData = $consultation->prescriptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $prescription): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><p><?php echo e($prescription->instructions); ?></p><div class="table-scroll"><table><thead><tr><th>Medicine</th><th>Dosage</th><th>Frequency</th><th>Duration</th></tr></thead><tbody><?php $__currentLoopData = $prescription->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><tr><td><?php echo e($item->medicine_name); ?></td><td><?php echo e($item->dosage); ?></td><td><?php echo e($item->frequency); ?></td><td><?php echo e($item->duration_days ? $item->duration_days.' days' : 'As directed'); ?></td></tr><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></tbody></table></div><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?> <?php endif; ?>
</div></article>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if (isset($component)) { $__componentOriginal4f22a152e0729cd34293e65bd200d933 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4f22a152e0729cd34293e65bd200d933 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.empty','data' => ['title' => 'No completed consultations','message' => 'Completed doctor consultations will appear as a permanent history timeline.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('empty'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'No completed consultations','message' => 'Completed doctor consultations will appear as a permanent history timeline.']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal4f22a152e0729cd34293e65bd200d933)): ?>
<?php $attributes = $__attributesOriginal4f22a152e0729cd34293e65bd200d933; ?>
<?php unset($__attributesOriginal4f22a152e0729cd34293e65bd200d933); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal4f22a152e0729cd34293e65bd200d933)): ?>
<?php $component = $__componentOriginal4f22a152e0729cd34293e65bd200d933; ?>
<?php unset($__componentOriginal4f22a152e0729cd34293e65bd200d933); ?>
<?php endif; ?><?php endif; ?>
</div><?php echo e($consultations->links()); ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\medicare\resources\views/patient/history.blade.php ENDPATH**/ ?>