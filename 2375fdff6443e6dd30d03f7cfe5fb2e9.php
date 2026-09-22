<?php $__env->startSection('page-title','Billing & Payments'); ?>
<?php $__env->startSection('content'); ?>
<section class="panel">
    <div class="panel-head"><div><span class="kicker">RECEPTION PAYMENT</span><h2>Find booking by patient ID</h2></div></div>
    <form class="filter-bar" method="get">
        <input name="q" value="<?php echo e($search); ?>" placeholder="Enter patient ID or patient name" required>
        <button class="btn btn-primary">Find booking</button>
        <?php if($search): ?><a class="btn btn-secondary" href="<?php echo e(route('staff.billing')); ?>">Clear</a><?php endif; ?>
    </form>
</section>

<div class="dashboard-grid align-start">
    <section class="panel">
        <h2>Create other invoice</h2>
        <form method="post" action="<?php echo e(route('staff.billing.store')); ?>">
            <?php echo csrf_field(); ?>
            <label>Patient<select name="patient_id" required><?php $__currentLoopData = $patients; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $patient): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($patient->patient_id); ?>"><?php echo e($patient->full_name); ?> (<?php echo e($patient->patient_code); ?>)</option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></label>
            <label>Appointment ID (optional)<input type="number" name="appointment_id"></label>
            <label>Service type<select name="item_type"><?php $__currentLoopData = ['CONSULTATION','LABORATORY','PHARMACY','OTHER']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option><?php echo e($type); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></label>
            <label>Description<input name="description" required></label>
            <label>Amount (LKR)<input type="number" step="0.01" min="0.01" name="amount" required></label>
            <button class="btn btn-primary">Create invoice</button>
        </form>
    </section>

    <section>
        <h2><?php echo e($search ? 'Matching bookings and invoices' : 'Recent invoices'); ?></h2>
        <?php $__empty_1 = true; $__currentLoopData = $invoices; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $invoice): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <article class="panel invoice-card">
                <div class="panel-head">
                    <div><span class="kicker"><?php echo e($invoice->invoice_no); ?></span><h3><?php echo e($invoice->patient->full_name); ?></h3><p class="invoice-patient-id">Patient ID: <?php echo e($invoice->patient->patient_code); ?></p></div>
                    <?php if (isset($component)) { $__componentOriginal51ed764111e345fc11534f121cfeb451 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal51ed764111e345fc11534f121cfeb451 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.status','data' => ['value' => $invoice->status]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('status'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($invoice->status)]); ?>
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
                <?php if($invoice->labOrder): ?>
                    <div class="summary-box"><strong>Laboratory tests</strong><p><?php echo e($invoice->labOrder->lab_order_no); ?></p>
                        <?php $__currentLoopData = $invoice->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $line): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><p><?php echo e($line->description); ?> &middot; Rs. <?php echo e(number_format($line->line_total,2)); ?></p><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                <?php elseif($invoice->appointment): ?>
                    <div class="summary-box">
                        <strong><?php echo e($invoice->appointment->doctor->user->full_name); ?></strong>
                        <p><?php echo e($invoice->appointment->appointment_no); ?> · <?php echo e($invoice->appointment->slot?->slot_start?->format('d M Y h:i A')); ?></p>
                    </div>
                <?php endif; ?>
                <div class="detail-grid invoice-totals">
                    <div><small>Total</small><p>Rs. <?php echo e(number_format($invoice->total_amount,2)); ?></p></div>
                    <div><small>Paid</small><p>Rs. <?php echo e(number_format($invoice->paid_amount,2)); ?></p></div>
                    <div><small>Balance</small><p><strong>Rs. <?php echo e(number_format($invoice->balance_amount,2)); ?></strong></p></div>
                </div>
                <a class="btn btn-secondary btn-sm" href="<?php echo e(route('staff.receipt',$invoice)); ?>">View / print receipt</a>
                <?php if($invoice->status==='VOID'): ?>
                    <p class="muted">Invoice cancelled.</p>
                    <?php $__currentLoopData = $invoice->payments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $payment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php if($payment->status === 'COMPLETED' && str_starts_with((string)$payment->notes, 'Hospital refund awaiting collection; ')): ?>
                            <form method="post" action="<?php echo e(route('staff.refunds.collect', [$invoice, $payment])); ?>">
                                <?php echo csrf_field(); ?>
                                <p><strong>Refund awaiting collection: Rs. <?php echo e(number_format($payment->amount, 2)); ?></strong><br><small><?php echo e($payment->payment_no); ?></small></p>
                                <label class="check-row warning-check"><input type="checkbox" name="confirm_refund" value="1" required> I handed Rs. <?php echo e(number_format($payment->amount, 2)); ?> back to the patient.</label>
                                <button class="btn btn-primary">Confirm refund handed over</button>
                            </form>
                        <?php elseif($payment->status === 'REFUNDED'): ?>
                            <p class="muted">Refund recorded: Rs. <?php echo e(number_format($payment->amount, 2)); ?> &middot; <?php echo e($payment->payment_no); ?></p>
                        <?php endif; ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <?php elseif($invoice->lab_order_id && (float)$invoice->balance_amount > 0): ?>
                    <a class="btn btn-primary" href="<?php echo e(route('lab-payment.show',$invoice->labOrder)); ?>">Confirm cash / card payment</a>
                <?php elseif((float)$invoice->balance_amount > 0): ?>
                    <form method="post" action="<?php echo e(route('staff.payments.store',$invoice)); ?>">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="payment_method" value="CASH">
                        <input type="hidden" name="amount" value="<?php echo e($invoice->balance_amount); ?>">
                        <input type="hidden" name="transaction_reference" value="">
                        <label class="check-row warning-check"><input type="checkbox" name="confirm_payment" value="1" required> I received Rs. <?php echo e(number_format($invoice->balance_amount,2)); ?> from this patient.</label>
                        <button class="btn btn-primary btn-block">Confirm hospital payment</button>
                    </form>
                <?php else: ?>
                    <div class="invoice-paid-message"><strong>Payment complete</strong><span>No outstanding balance.</span></div>
                <?php endif; ?>
            </article>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <?php if (isset($component)) { $__componentOriginal4f22a152e0729cd34293e65bd200d933 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4f22a152e0729cd34293e65bd200d933 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.empty','data' => ['title' => 'No matching unpaid booking','message' => 'Check the patient ID and try again.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('empty'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'No matching unpaid booking','message' => 'Check the patient ID and try again.']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal4f22a152e0729cd34293e65bd200d933)): ?>
<?php $attributes = $__attributesOriginal4f22a152e0729cd34293e65bd200d933; ?>
<?php unset($__attributesOriginal4f22a152e0729cd34293e65bd200d933); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal4f22a152e0729cd34293e65bd200d933)): ?>
<?php $component = $__componentOriginal4f22a152e0729cd34293e65bd200d933; ?>
<?php unset($__componentOriginal4f22a152e0729cd34293e65bd200d933); ?>
<?php endif; ?>
        <?php endif; ?>
        <?php echo e($invoices->links()); ?>

    </section>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\medicare\resources\views\staff\billing.blade.php ENDPATH**/ ?>