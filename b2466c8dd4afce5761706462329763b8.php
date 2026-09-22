<?php $__env->startSection('page-title','Laboratory Payment'); ?>
<?php $__env->startSection('content'); ?>
<section class="panel lab-payment-card">
    <div class="panel-head lab-payment-heading">
        <div><span class="kicker">Laboratory order</span><h2><?php echo e($order->lab_order_no); ?></h2></div>
        <?php if (isset($component)) { $__componentOriginal51ed764111e345fc11534f121cfeb451 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal51ed764111e345fc11534f121cfeb451 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.status','data' => ['value' => $order->invoice?->status ?? $order->status]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('status'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($order->invoice?->status ?? $order->status)]); ?>
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
    </div>
    <div class="lab-payment-patient">
        <div><span>Patient</span><strong><?php echo e($order->patient->full_name); ?></strong></div>
        <div><span>Patient ID</span><strong><?php echo e($order->patient->patient_code); ?></strong></div>
    </div>
    <div class="table-scroll lab-payment-tests">
        <table><thead><tr><th scope="col">Test</th><th scope="col">Fee</th></tr></thead>
            <tbody><?php $__currentLoopData = $order->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><tr><td><?php echo e($item->test->test_name); ?></td><td>Rs. <?php echo e(number_format($item->fee,2)); ?></td></tr><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></tbody>
        </table>
    </div>
    <div class="lab-payment-summary">
        <div><span>Test total</span><strong>Rs. <?php echo e(number_format($order->items->sum('fee'),2)); ?></strong></div>
        <div class="lab-payment-balance"><span>Balance due</span><strong>Rs. <?php echo e(number_format($order->invoice?->balance_amount ?? $order->items->sum('fee'),2)); ?></strong></div>
    </div>
    <?php if($order->invoice?->status==='PAID'): ?>
        <div class="invoice-paid-message lab-payment-complete"><strong>Payment complete</strong><span>Ready for laboratory processing.</span></div>
    <?php elseif($order->status!=='CANCELLED'): ?>
        <form class="lab-payment-form" method="post" action="<?php echo e(route('lab-payment.store',$order)); ?>">
            <?php echo csrf_field(); ?>
            <h3>Record payment</h3>
            <div class="form-grid">
                <label>Payment method<select name="method"><option value="CASH" <?php if(old('method')==='CASH'): echo 'selected'; endif; ?>>Cash</option><option value="CARD" <?php if(old('method')==='CARD'): echo 'selected'; endif; ?>>Card &middot; hospital terminal</option></select></label>
                <label>Terminal reference (optional)<input name="reference" value="<?php echo e(old('reference')); ?>" maxlength="100"></label>
            </div>
            <label class="check-row"><input type="checkbox" name="received" value="1" required>I received the displayed amount in cash or through the hospital card terminal.</label>
            <button class="btn btn-primary">Confirm payment</button>
        </form>
    <?php endif; ?>
    <div class="lab-payment-footer">
        <?php if($order->invoice): ?><a class="btn btn-secondary btn-sm" href="<?php echo e(route('lab-payment.receipt',$order->invoice)); ?>">View / print receipt</a><?php endif; ?>
        <?php if(auth()->user()->isRole('lab_officer')): ?><a href="<?php echo e(route('lab.orders')); ?>">Back to laboratory orders</a><?php else: ?><a href="<?php echo e(route('staff.lab-bookings')); ?>">Back to laboratory bookings</a><?php endif; ?>
    </div>
</section>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\medicare\resources\views\lab\payment.blade.php ENDPATH**/ ?>