 <?php $__env->startSection('page-title','Laboratory Dashboard'); ?> <?php $__env->startSection('content'); ?>
<div class="stat-grid stat-grid-3">
<div class="stat-card">
<span>Pending / in progress</span>
<strong><?php echo e($pending); ?></strong>
</div>
<div class="stat-card">
<span>Completed today</span>
<strong><?php echo e($completedToday); ?></strong>
</div>
<div class="stat-card">
<span>Recent orders</span>
<strong><?php echo e($orders->count()); ?></strong>
</div>
</div>
<section class="panel">
<div class="panel-head">
<h2>Recent laboratory orders</h2>
<a class="btn btn-primary btn-sm" href="<?php echo e(route('lab.orders')); ?>">Manage orders</a>
</div>
<div class="table-scroll">
<table>
<thead>
<tr>
<th>Order</th>
<th>Patient</th>
<th>Doctor</th>
<th>Tests</th>
<th>Status</th>
</tr>
</thead>
<tbody><?php $__empty_1 = true; $__currentLoopData = $orders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $o): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><tr>
<td><?php echo e($o->lab_order_no); ?></td>
<td><?php echo e($o->patient->full_name); ?></td>
<td><?php echo e($o->doctor?->user?->full_name ?: 'Reception booking'); ?></td>
<td><?php echo e($o->items->pluck('test.test_name')->join(', ')); ?></td>
<td>
<?php if (isset($component)) { $__componentOriginal51ed764111e345fc11534f121cfeb451 = $component; } ?>
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
<?php endif; ?>
</td>
</tr><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><tr>
<td colspan="5">No laboratory orders.</td>
</tr><?php endif; ?></tbody>
</table>
</div>
</section><?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\medicare\resources\views\lab\dashboard.blade.php ENDPATH**/ ?>