<?php $__env->startSection('title','Laboratory Result — MEDICARE'); ?>
<?php $__env->startSection('page-title','Laboratory Result Entry'); ?>
<?php $__env->startSection('content'); ?>
<div class="dashboard-grid align-start">
<section class="panel"><span class="kicker"><?php echo e($item->order->lab_order_no); ?></span><h2><?php echo e($item->test->test_name); ?></h2><div class="detail-grid"><div><small>Patient</small><p><?php echo e($item->order->patient->full_name); ?> (<?php echo e($item->order->patient->patient_code); ?>)</p></div><div><small>Ordering doctor</small><p><?php echo e($item->order->doctor?->user?->full_name ?: 'Reception booking'); ?></p></div><div><small>Reference information</small><p><?php echo e($item->test->reference_notes ?: 'Refer to approved laboratory reference ranges.'); ?></p></div><div><small>Current status</small><p><?php if (isset($component)) { $__componentOriginal51ed764111e345fc11534f121cfeb451 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal51ed764111e345fc11534f121cfeb451 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.status','data' => ['value' => $item->status]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('status'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($item->status)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal51ed764111e345fc11534f121cfeb451)): ?>
<?php $attributes = $__attributesOriginal51ed764111e345fc11534f121cfeb451; ?>
<?php unset($__attributesOriginal51ed764111e345fc11534f121cfeb451); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal51ed764111e345fc11534f121cfeb451)): ?>
<?php $component = $__componentOriginal51ed764111e345fc11534f121cfeb451; ?>
<?php unset($__componentOriginal51ed764111e345fc11534f121cfeb451); ?>
<?php endif; ?></p></div></div><div class="warning-check"><strong>Privacy rule:</strong> report documents are stored outside the public web directory. Email contains only a notification; the doctor and patient open the report securely after authorization.</div>
<?php if($attachments->isNotEmpty()): ?><hr class="divider"><h3>Existing protected documents</h3><?php $__currentLoopData = $attachments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $attachment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><div class="file-row"><span>📄 <?php echo e($attachment->original_name); ?></span><a class="btn btn-sm btn-secondary" target="_blank" href="<?php echo e(route('documents.view',$attachment->attachment_id)); ?>">Open</a></div><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?> <?php endif; ?>
</section>
<section class="panel"><form method="post" enctype="multipart/form-data" action="<?php echo e(route('lab.result.store',$item)); ?>"><?php echo csrf_field(); ?>
<label>Result values / interpretation<textarea name="result_text" rows="12" maxlength="30000" placeholder="Enter measured values, units and reference ranges when available..."><?php echo e(old('result_text',$item->result?->result_text)); ?></textarea><small>Either typed result content or a report file is required.</small></label>
<label>Protected report document<input type="file" name="report_file" accept="application/pdf,image/jpeg,image/png,.pdf,.jpg,.jpeg,.png"><small>PDF, JPG or PNG only. Maximum <?php echo e(config('ai.lab_result_max_mb',8)); ?> MB.</small></label>
<label class="check-row"><input type="checkbox" name="abnormal_flag" value="1" <?php if(old('abnormal_flag',$item->result?->abnormal_flag)): echo 'checked'; endif; ?>> Mark abnormal for doctor attention</label>
<label class="check-row warning-check"><input type="checkbox" name="verify_now" value="1" <?php if(old('verify_now')): echo 'checked'; endif; ?>> Verify and release this result to the patient now</label>
<button class="btn btn-primary btn-block">Save, summarize & notify</button><p class="muted">A clear summary supports faster understanding. Clinical review always remains the responsibility of qualified staff.</p>
</form></section></div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\medicare\resources\views\lab\result.blade.php ENDPATH**/ ?>