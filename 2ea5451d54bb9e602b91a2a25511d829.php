<?php $__env->startSection('page-title','Appointment Payment'); ?>
<?php $__env->startSection('content'); ?>
<div class="dashboard-grid align-start">
    <section class="panel">
        <span class="kicker">BOOKING RESERVED</span>
        <h2><?php echo e($appointment->doctor->user->full_name); ?></h2>
        <p><?php echo e($appointment->doctor->specialty->specialty_name); ?></p>
        <div class="detail-grid">
            <div><small>Patient</small><p><?php echo e($appointment->patient->full_name); ?> · <?php echo e($appointment->patient->patient_code); ?></p></div>
            <div><small>Appointment</small><p><?php echo e($appointment->appointment_no); ?></p></div>
            <div><small>Date and time</small><p><?php echo e($appointment->slot->slot_start->format('d M Y h:i A')); ?></p></div>
            <div><small>Amount due</small><p><strong>Rs. <?php echo e(number_format($invoice->balance_amount,2)); ?></strong></p></div>
        </div>
    </section>

    <section class="panel">
        <span class="kicker">PAYMENT METHOD</span>
        <h2>Complete payment</h2>
        <?php if($invoice->status==='VOID'): ?>
            <p>Invoice cancelled. No payment is due.</p>
            <a class="btn btn-secondary" href="<?php echo e(route('patient.appointments')); ?>">Back to appointments</a>
        <?php elseif((float)$invoice->balance_amount > 0): ?>
            <form class="card-payment-form" data-card-payment method="post" action="<?php echo e(route('patient.payments.card',$invoice)); ?>">
                <?php echo csrf_field(); ?>
                <label>Cardholder name<input name="cardholder_name" autocomplete="cc-name" maxlength="100" required></label>
                <label>Card number<input name="card_number" inputmode="numeric" autocomplete="cc-number" minlength="19" maxlength="19" pattern="[0-9]{4} [0-9]{4} [0-9]{4} [0-9]{4}" required></label>
                <div class="form-grid">
                    <label>Expiry (MM/YY)<input name="expiry" inputmode="numeric" placeholder="MM/YY" autocomplete="cc-exp" maxlength="5" pattern="(0[1-9]|1[0-2])/[0-9]{2}" required></label>
                    <label>CVV<input type="password" name="cvv" inputmode="numeric" autocomplete="cc-csc" pattern="[0-9]{3,4}" minlength="3" maxlength="4" required></label>
                </div>
                <button class="btn btn-primary btn-block">Pay Rs. <?php echo e(number_format($invoice->balance_amount,2)); ?> by card</button>
            </form>
            <div class="payment-alternative"><span>or</span></div>
            <form method="post" action="<?php echo e(route('patient.payments.hospital',$invoice)); ?>">
                <?php echo csrf_field(); ?>
                <button class="btn btn-secondary btn-block">Pay directly at hospital</button>
            </form>
            
        <?php else: ?>
            <?php if (isset($component)) { $__componentOriginal51ed764111e345fc11534f121cfeb451 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal51ed764111e345fc11534f121cfeb451 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.status','data' => ['value' => 'PAID']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('status'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['value' => 'PAID']); ?>
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
            <p>This appointment invoice has been paid.</p>
            <a class="btn btn-primary" href="<?php echo e(route('patient.payments')); ?>">View receipt</a>
        <?php endif; ?>
    </section>
</div>
<?php $__env->stopSection(); ?>
<?php $__env->startPush('styles'); ?>
<style>
.card-payment-form{display:grid;gap:20px;margin-top:24px}
.card-payment-form label{margin:0}
.card-payment-form .form-grid{gap:18px}
.payment-alternative{display:flex;align-items:center;gap:16px;margin:22px 0;color:#6c7e95;font-size:14px}
.payment-alternative::before,.payment-alternative::after{content:"";height:1px;background:#e0e8f2;flex:1}
</style>
<?php $__env->stopPush(); ?>
<?php $__env->startPush('scripts'); ?>
<script src="<?php echo e(asset('assets/js/card-payment.js')); ?>"></script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\medicare\resources\views/patient/appointment-payment.blade.php ENDPATH**/ ?>