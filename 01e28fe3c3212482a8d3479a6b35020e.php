<?php $__env->startSection('page-title','Lab Bookings & Prices'); ?>
<?php $__env->startSection('content'); ?>
<div class="lab-booking-page">
    <section class="panel">
        <div class="panel-head"><div><h2>Book laboratory tests</h2><p class="muted">Find a patient, then select the tests to book.</p></div></div>
        <form class="lab-patient-search" method="get" action="<?php echo e(route('staff.lab-bookings')); ?>">
            <label>Patient ID<input name="patient_code" value="<?php echo e(request('patient_code')); ?>" placeholder="Enter patient ID" maxlength="50"></label>
            <button class="btn btn-primary" type="submit">Find patient</button>
            <a class="btn btn-secondary" href="<?php echo e(route('staff.lab-bookings')); ?>">All orders</a>
        </form>
        <?php if($patient): ?>
            <div class="lab-patient-summary"><strong><?php echo e($patient->full_name); ?></strong><span><?php echo e($patient->patient_code); ?></span></div>
            <form method="post" action="<?php echo e(route('staff.lab-bookings.store')); ?>">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="patient_id" value="<?php echo e($patient->patient_id); ?>">
                <fieldset class="lab-test-selection"><legend>Select tests</legend><div class="lab-test-grid">
                    <?php $__empty_1 = true; $__currentLoopData = $tests->where('active',true); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $test): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <label class="check-row lab-test-option"><input type="checkbox" name="tests[]" value="<?php echo e($test->lab_test_id); ?>" <?php if(in_array($test->lab_test_id, old('tests', []))): echo 'checked'; endif; ?>><span><?php echo e($test->test_name); ?></span><strong>Rs. <?php echo e(number_format($test->standard_fee,2)); ?></strong></label>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><p class="muted">No active laboratory tests available.</p><?php endif; ?>
                </div></fieldset>
                <div class="lab-booking-actions"><span class="muted">Review the invoice before making payment.</span><button class="btn btn-primary" <?php if($tests->where('active',true)->isEmpty()): echo 'disabled'; endif; ?>>Create order &amp; review payment</button></div>
            </form>
        <?php elseif(request('patient_code')): ?>
            <p class="lab-booking-notice">No active patient found. Check the patient ID or create a patient profile at reception.</p>
        <?php endif; ?>
    </section>

    <details class="panel lab-price-panel">
        <summary>Laboratory test prices <span class="muted">View &amp; edit</span></summary>
        <p class="muted">Updated prices apply to new orders only.</p>
        <div class="lab-price-list">
            <?php $__empty_1 = true; $__currentLoopData = $tests; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $test): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <form method="post" action="<?php echo e(route('staff.lab-prices',$test)); ?>" class="lab-price-row">
                    <?php echo csrf_field(); ?>
                    <div class="lab-price-name"><strong><?php echo e($test->test_name); ?></strong><?php if (! ($test->active)): ?><small class="muted">Inactive</small><?php endif; ?></div>
                    <label for="lab-price-<?php echo e($test->lab_test_id); ?>">Price (Rs.)<input id="lab-price-<?php echo e($test->lab_test_id); ?>" type="number" name="standard_fee" value="<?php echo e(number_format($test->standard_fee,2,'.','')); ?>" min="0" max="999999.99" step=".01" required></label>
                    <button class="btn btn-secondary btn-sm" aria-label="Save price for <?php echo e($test->test_name); ?>">Save price</button>
                </form>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><p class="muted">No laboratory tests available.</p><?php endif; ?>
        </div>
    </details>

    <section class="lab-orders-section" aria-labelledby="lab-orders-heading">
        <div class="panel-head"><h2 id="lab-orders-heading">Laboratory orders</h2><span class="muted"><?php echo e($orders->total()); ?> <?php echo e($orders->total() == 1 ? 'order' : 'orders'); ?></span></div>
        <div class="lab-order-grid">
            <?php $__empty_1 = true; $__currentLoopData = $orders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <article class="panel lab-order-card">
                    <div class="panel-head"><div><h3><?php echo e($order->lab_order_no); ?></h3><p><?php echo e($order->patient->full_name); ?></p><small class="muted"><?php echo e($order->patient->patient_code); ?></small></div><?php if (isset($component)) { $__componentOriginal51ed764111e345fc11534f121cfeb451 = $component; } ?>
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
                    <div class="lab-order-payment"><span class="muted">Payment status</span><strong><?php echo e(str_replace('_',' ', $order->invoice?->status ?: 'Not invoiced')); ?></strong></div>
                    <ul class="lab-order-tests"><?php $__currentLoopData = $order->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><li><span><?php echo e($item->test->test_name); ?></span><strong>Rs. <?php echo e(number_format($item->fee,2)); ?></strong></li><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></ul>
                    <div class="lab-order-total"><span>Test total</span><strong>Rs. <?php echo e(number_format($order->items->sum('fee'),2)); ?></strong></div>
                    <a class="btn btn-primary btn-sm" href="<?php echo e(route('lab-payment.show',$order)); ?>">Payment / receipt</a>
                    <?php if(!$order->consultation_id && $order->status==='PENDING' && $order->invoice?->status==='ISSUED'): ?>
                        <details class="lab-order-edit"><summary>Edit selected tests</summary><form method="post" action="<?php echo e(route('staff.lab-bookings.edit',$order)); ?>"><?php echo csrf_field(); ?>
                            <div class="lab-edit-options"><?php $__currentLoopData = $tests->where('active',true); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $test): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><label class="check-row"><input type="checkbox" name="tests[]" value="<?php echo e($test->lab_test_id); ?>" <?php if($order->items->contains('lab_test_id',$test->lab_test_id)): echo 'checked'; endif; ?>><?php echo e($test->test_name); ?></label><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></div>
                            <button class="btn btn-secondary btn-sm">Update tests &amp; invoice</button>
                        </form></details>
                    <?php endif; ?>
                </article>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><div class="panel empty-state">No laboratory orders found.</div><?php endif; ?>
        </div>
        <?php echo e($orders->links()); ?>

    </section>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\medicare\resources\views/staff/lab-booking.blade.php ENDPATH**/ ?>