 <?php $__env->startSection('page-title','Laboratory Test Orders'); ?> <?php $__env->startSection('content'); ?><section class="panel" style="margin-bottom:24px"><form class="filter-bar"><select name="status"><option value="">All statuses</option><?php $__currentLoopData = ['PENDING','SAMPLE_COLLECTED','IN_PROGRESS','COMPLETED','CANCELLED']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option <?php if(request('status')===$v): echo 'selected'; endif; ?>><?php echo e($v); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select><button class="btn btn-primary">Filter</button></form></section><div class="lab-grid"><?php $__empty_1 = true; $__currentLoopData = $orders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $o): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><article class="panel lab-card"><div class="panel-head"><div><span class="kicker"><?php echo e($o->lab_order_no); ?></span><h2><?php echo e($o->patient->full_name); ?></h2><p class="muted"><?php echo e($o->doctor?->user?->full_name ?: 'Reception booking'); ?></p></div><?php if (isset($component)) { $__componentOriginal51ed764111e345fc11534f121cfeb451 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal51ed764111e345fc11534f121cfeb451 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.status','data' => ['value' => $o->status]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('status'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($o->status)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal51ed764111e345fc11534f121cfeb451)): ?>
<?php $attributes = $__attributesOriginal51ed764111e345fc11534f121cfeb451; ?>
<?php unset($__attributesOriginal51ed764111e345fc11534f121cfeb451); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal51ed764111e345fc11534f121cfeb451)): ?>
<?php $component = $__componentOriginal51ed764111e345fc11534f121cfeb451; ?>
<?php unset($__componentOriginal51ed764111e345fc11534f121cfeb451); ?>
<?php endif; ?></div><p style="display:flex;align-items:center;gap:12px;flex-wrap:wrap">Payment: <strong><?php echo e($o->invoice?->status ?: 'Not invoiced'); ?></strong> <a class="btn btn-secondary btn-sm" href="<?php echo e(route('lab-payment.show',$o)); ?>">Payment / receipt</a></p><?php if(!in_array($o->status,['COMPLETED','CANCELLED'])): ?><form method="post" action="<?php echo e(route('lab.orders.status',$o)); ?>"><?php echo csrf_field(); ?><div class="inline-input"><select name="status"><?php $__currentLoopData = ['PENDING','SAMPLE_COLLECTED','IN_PROGRESS','CANCELLED']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option <?php if($o->status===$v): echo 'selected'; endif; ?>><?php echo e($v); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select><button class="btn btn-secondary btn-sm">Update</button></div></form><?php else: ?><p class="muted"><?php echo e($o->status==='COMPLETED' ? 'All test results completed.' : 'Order cancelled.'); ?></p><?php endif; ?><hr class="divider"><?php $__currentLoopData = $o->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><div class="trend-row" style="padding:18px 0;gap:16px;flex-wrap:wrap"><div><strong><?php echo e($item->test->test_name); ?></strong><small>Rs. <?php echo e(number_format($item->fee,2)); ?> &middot; <?php echo e($item->status); ?></small></div><?php if($o->invoice?->status==='PAID' && $o->status!=='CANCELLED'): ?><a class="btn btn-primary btn-sm" href="<?php echo e(route('lab.result',$item)); ?>"><?php echo e($item->result?'Edit result':'Enter result'); ?></a><?php else: ?><span class="muted">Payment required</span><?php endif; ?></div><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></article><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if (isset($component)) { $__componentOriginal4f22a152e0729cd34293e65bd200d933 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4f22a152e0729cd34293e65bd200d933 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.empty','data' => ['title' => 'No test orders']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('empty'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'No test orders']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal4f22a152e0729cd34293e65bd200d933)): ?>
<?php $attributes = $__attributesOriginal4f22a152e0729cd34293e65bd200d933; ?>
<?php unset($__attributesOriginal4f22a152e0729cd34293e65bd200d933); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal4f22a152e0729cd34293e65bd200d933)): ?>
<?php $component = $__componentOriginal4f22a152e0729cd34293e65bd200d933; ?>
<?php unset($__componentOriginal4f22a152e0729cd34293e65bd200d933); ?>
<?php endif; ?><?php endif; ?></div><?php echo e($orders->links()); ?><?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\medicare\resources\views\lab\orders.blade.php ENDPATH**/ ?>