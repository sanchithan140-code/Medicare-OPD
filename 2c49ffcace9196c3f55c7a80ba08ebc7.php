 <?php $__env->startSection('page-title','Insurance Claims'); ?> <?php $__env->startSection('content'); ?><div class="dashboard-grid align-start"><section class="panel"><h2>Submit claim</h2><form method="post" action="<?php echo e(route('staff.insurance.store')); ?>"><?php echo csrf_field(); ?><label>Invoice<select name="invoice_id"><?php $__currentLoopData = $invoices; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($i->invoice_id); ?>"><?php echo e($i->invoice_no); ?> · <?php echo e($i->patient->full_name); ?> · Rs. <?php echo e($i->balance_amount); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></label><label>Provider<select name="provider_id"><?php $__currentLoopData = $providers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($p->provider_id); ?>"><?php echo e($p->provider_name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></label><label>Policy number<input name="policy_no" required></label><label>Requested amount<input type="number" name="requested_amount" min="0.01" step="0.01" required></label><button class="btn btn-primary">Submit claim</button></form></section><section><h2>Claims</h2><?php $__empty_1 = true; $__currentLoopData = $claims; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><article class="panel"><div class="panel-head"><div><span class="kicker"><?php echo e($c->claim_no); ?></span><h3><?php echo e($c->invoice->patient->full_name); ?></h3><p><?php echo e($c->provider->provider_name); ?></p></div><?php if (isset($component)) { $__componentOriginal51ed764111e345fc11534f121cfeb451 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal51ed764111e345fc11534f121cfeb451 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.status','data' => ['value' => $c->status]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('status'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($c->status)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal51ed764111e345fc11534f121cfeb451)): ?>
<?php $attributes = $__attributesOriginal51ed764111e345fc11534f121cfeb451; ?>
<?php unset($__attributesOriginal51ed764111e345fc11534f121cfeb451); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal51ed764111e345fc11534f121cfeb451)): ?>
<?php $component = $__componentOriginal51ed764111e345fc11534f121cfeb451; ?>
<?php unset($__componentOriginal51ed764111e345fc11534f121cfeb451); ?>
<?php endif; ?></div><?php if(in_array($c->status,['APPROVED','REJECTED','CANCELLED'],true)): ?><div class="summary-box"><strong>Finalized claim</strong><p>Requested: Rs. <?php echo e(number_format($c->requested_amount,2)); ?> · Approved: Rs. <?php echo e(number_format($c->approved_amount,2)); ?></p><?php if($c->notes): ?><small><?php echo e($c->notes); ?></small><?php endif; ?></div><?php else: ?><form method="post" action="<?php echo e(route('staff.insurance.update',$c)); ?>"><?php echo csrf_field(); ?><div class="form-grid"><label>Status<select name="status"><?php $__currentLoopData = ['PENDING','SUBMITTED','MORE_INFO','APPROVED','REJECTED','CANCELLED']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($v); ?>" <?php if($c->status===$v): echo 'selected'; endif; ?>><?php echo e(str_replace('_',' ',$v)); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></label><label>Approved amount<input type="number" min="0.01" max="<?php echo e($c->requested_amount); ?>" step="0.01" name="approved_amount" value="<?php echo e($c->approved_amount ?: ''); ?>" placeholder="Required for approval"></label><label class="span-2">Notes<input name="notes" maxlength="500" value="<?php echo e($c->notes); ?>"></label></div><button class="btn btn-secondary btn-sm">Update claim</button></form><?php endif; ?></article><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if (isset($component)) { $__componentOriginal4f22a152e0729cd34293e65bd200d933 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4f22a152e0729cd34293e65bd200d933 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.empty','data' => ['title' => 'No claims']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('empty'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'No claims']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal4f22a152e0729cd34293e65bd200d933)): ?>
<?php $attributes = $__attributesOriginal4f22a152e0729cd34293e65bd200d933; ?>
<?php unset($__attributesOriginal4f22a152e0729cd34293e65bd200d933); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal4f22a152e0729cd34293e65bd200d933)): ?>
<?php $component = $__componentOriginal4f22a152e0729cd34293e65bd200d933; ?>
<?php unset($__componentOriginal4f22a152e0729cd34293e65bd200d933); ?>
<?php endif; ?><?php endif; ?><?php echo e($claims->links()); ?></section></div><?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\medicare\resources\views\staff\insurance.blade.php ENDPATH**/ ?>