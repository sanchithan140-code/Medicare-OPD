<?php $__env->startSection('title','Verify Email — MEDICARE'); ?>
<?php $__env->startSection('content'); ?>
<section class="simple-auth"><div class="auth-card">
    <div class="otp-icon">✉</div><span class="eyebrow">EMAIL VERIFICATION</span><h2>Enter your six-digit OTP</h2>
    <p class="muted">The code was sent to <strong><?php echo e($email); ?></strong>. It expires in 3 minutes. Five incorrect attempts lock OTP access for one hour.</p>
    <p class="otp-timer" data-otp-timer data-expires-at="<?php echo e($expiresAt); ?>" data-locked-until="<?php echo e($lockedUntil ?? 0); ?>">Time remaining: <strong>03:00</strong></p>
    <?php if($errors->any()): ?><div class="alert alert-error"><?php echo e($errors->first()); ?></div><?php endif; ?>
    <form method="post" action="<?php echo e(route('verify.store')); ?>"><?php echo csrf_field(); ?><input type="hidden" name="email" value="<?php echo e($email); ?>"><label>OTP code<input name="otp" inputmode="numeric" autocomplete="one-time-code" minlength="6" maxlength="6" pattern="[0-9]{6}" placeholder="000000" required></label><button class="btn btn-primary btn-block">Verify & activate account</button></form>
    <form class="inline-form" method="post" action="<?php echo e(route('verify.resend')); ?>"><?php echo csrf_field(); ?><input type="hidden" name="email" value="<?php echo e($email); ?>"><button class="btn btn-ghost btn-block" type="submit" data-otp-resend <?php if(! $canResend): echo 'disabled'; endif; ?>>Resend OTP</button></form>
</div></section>
<?php $__env->stopSection(); ?>
<?php $__env->startPush('scripts'); ?>
<script src="<?php echo e(asset('assets/js/otp-timer.js')); ?>"></script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.public', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\medicare\resources\views\auth\verify.blade.php ENDPATH**/ ?>