<?php $__env->startSection('page-title','Patient Records'); ?>
<?php $__env->startSection('content'); ?>
<section class="panel">
    <h2>Find patient records</h2>
    <p class="muted">Enter the patient ID to view details, previous doctors' notes and medical reports.</p>
    <form method="get" action="<?php echo e(route('doctor.patients')); ?>" class="filter-bar">
        <label>Patient ID<input name="patient_id" value="<?php echo e($search); ?>" maxlength="50" placeholder="Enter patient ID" required></label>
        <button type="submit" class="btn btn-primary">Find patient</button>
    </form>
</section>
<?php if(!$patient): ?>
    <section class="panel"><?php if (isset($component)) { $__componentOriginal4f22a152e0729cd34293e65bd200d933 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4f22a152e0729cd34293e65bd200d933 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.empty','data' => ['title' => $searched ? 'No patient found with this ID' : 'Search for a patient to view their records']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('empty'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($searched ? 'No patient found with this ID' : 'Search for a patient to view their records')]); ?>
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
<?php else: ?>
<section class="panel">
    <span class="patient-code">PATIENT ID <?php echo e($patient->patient_code); ?></span>
    <h2><?php echo e($patient->full_name); ?></h2>
    <div class="profile-meta">
        <span><?php echo e($patient->sex); ?></span>
        <span>Date of birth: <?php echo e($patient->dob?->format('d M Y') ?: 'Not recorded'); ?></span>
        <span>Blood group: <?php echo e($patient->blood_group ?: 'Not recorded'); ?></span>
        <span>Phone: <?php echo e($patient->phone ?: 'Not recorded'); ?></span>
    </div>
    <p>Address: <?php echo e($patient->address ?: 'Not recorded'); ?></p>
</section>
<h2>Previous consultations</h2>
<?php $__empty_1 = true; $__currentLoopData = $consultations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $consultation): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
    <article class="panel patient-record-entry">
        <div class="panel-head"><h3><?php echo e($consultation->doctor?->user?->full_name ?: 'Doctor not recorded'); ?></h3><span class="muted"><?php echo e(($consultation->completed_at ?? $consultation->started_at)?->format('d M Y, h:i A')); ?></span></div>
        <p><strong>Presenting complaint:</strong> <?php echo e($consultation->chief_complaint ?: 'Not recorded'); ?></p>
        <p><strong>Diagnosis:</strong> <?php echo e($consultation->diagnosis_text ?: 'Not recorded'); ?></p>
        <p><strong>Clinical notes:</strong> <span class="record-note"><?php echo e($consultation->clinical_notes ?: 'Not recorded'); ?></span></p>
        <p><strong>Treatment plan:</strong> <span class="record-note"><?php echo e($consultation->treatment_plan ?: 'Not recorded'); ?></span></p>
        <?php if($consultation->patient_summary): ?><p><strong>Patient summary:</strong> <?php echo e($consultation->patient_summary); ?></p><?php endif; ?>
        <?php if($consultation->follow_up_date): ?><p><strong>Follow-up:</strong> <?php echo e($consultation->follow_up_date->format('d M Y')); ?></p><?php endif; ?>
        <div class="profile-meta"><?php $__currentLoopData = $consultation->conditions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $condition): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><span><?php echo e($condition->condition_name); ?></span><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></div>
        <?php $__currentLoopData = $consultation->prescriptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $prescription): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <h4>Prescription</h4>
            <?php $__currentLoopData = $prescription->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $medicine): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <p><?php echo e($medicine->medicine_name); ?> &middot; <?php echo e($medicine->dosage); ?> &middot; <?php echo e($medicine->frequency); ?> &middot; <?php echo e($medicine->duration_days); ?> day(s)</p>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <?php if($prescription->instructions): ?><p class="muted"><?php echo e($prescription->instructions); ?></p><?php endif; ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </article>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
    <section class="panel"><?php if (isset($component)) { $__componentOriginal4f22a152e0729cd34293e65bd200d933 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4f22a152e0729cd34293e65bd200d933 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.empty','data' => ['title' => 'No previous consultations']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('empty'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'No previous consultations']); ?>
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
<h2>Medical reports</h2>
<?php $__empty_1 = true; $__currentLoopData = $labOrders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
    <article class="panel patient-record-entry">
        <div class="panel-head"><h3><?php echo e($order->lab_order_no); ?></h3><?php if (isset($component)) { $__componentOriginal51ed764111e345fc11534f121cfeb451 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal51ed764111e345fc11534f121cfeb451 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.status','data' => ['value' => $order->status]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('status'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($order->status)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal51ed764111e345fc11534f121cfeb451)): ?>
<?php $attributes = $__attributesOriginal51ed764111e345fc11534f121cfeb451; ?>
<?php unset($__attributesOriginal51ed764111e345fc11534f121cfeb451); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal51ed764111e345fc11534f121cfeb451)): ?>
<?php $component = $__componentOriginal51ed764111e345fc11534f121cfeb451; ?>
<?php unset($__componentOriginal51ed764111e345fc11534f121cfeb451); ?>
<?php endif; ?></div>
        <p class="muted"><?php echo e($order->ordered_at?->format('d M Y, h:i A')); ?></p>
        <?php $__currentLoopData = $order->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="lab-result <?php echo e($item->result?->abnormal_flag ? 'abnormal' : ''); ?>">
                <h4><?php echo e($item->test?->test_name ?: 'Laboratory test'); ?></h4>
                <p class="record-note"><?php echo e($item->result?->result_text ?: 'Result pending'); ?></p>
                <?php if($item->result?->result_json): ?><pre class="record-note"><?php echo e(json_encode($item->result->result_json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)); ?></pre><?php endif; ?>
                <?php if($item->result?->ai_summary): ?><p><strong>AI summary:</strong> <?php echo e($item->result->ai_summary); ?></p><?php endif; ?>
                <?php if($item->result?->attachments?->isNotEmpty()): ?>
                    <div class="file-actions"><?php $__currentLoopData = $item->result->attachments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $attachment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <a class="btn btn-sm btn-secondary" target="_blank" rel="noopener" href="<?php echo e(route('documents.view',$attachment)); ?>">Open <?php echo e($attachment->original_name ?: 'report'); ?></a>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></div>
                <?php endif; ?>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </article>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
    <section class="panel"><?php if (isset($component)) { $__componentOriginal4f22a152e0729cd34293e65bd200d933 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4f22a152e0729cd34293e65bd200d933 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.empty','data' => ['title' => 'No medical reports recorded']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('empty'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'No medical reports recorded']); ?>
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
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\medicare\resources\views\doctor\history.blade.php ENDPATH**/ ?>