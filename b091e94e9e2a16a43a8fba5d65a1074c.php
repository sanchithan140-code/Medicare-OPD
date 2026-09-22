<?php $__env->startSection('title','Reset Password — MEDICARE'); ?>
<?php $__env->startSection('content'); ?>
<section class="simple-auth"><div class="auth-card">
    <div class="otp-icon">✓</div><span class="eyebrow">NEW PASSWORD</span><h2>Complete password reset</h2>
    <p class="muted">The reset OTP expires in 3 minutes. Five incorrect attempts lock OTP access for one hour.</p>
    <p class="otp-timer" data-otp-timer data-expires-at="<?php echo e($expiresAt); ?>" data-locked-until="<?php echo e($lockedUntil ?? 0); ?>">Time remaining: <strong>03:00</strong></p>
    <?php if($errors->any()): ?><div class="alert alert-error"><ul><?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><li><?php echo e($error); ?></li><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></ul></div><?php endif; ?>
    <form method="post" action="<?php echo e(route('password.update')); ?>"><?php echo csrf_field(); ?>
        <label>Email<input type="email" name="email" value="<?php echo e(old('email',$email)); ?>" maxlength="190" required></label>
        <label>Reset OTP<input name="otp" inputmode="numeric" autocomplete="one-time-code" minlength="6" maxlength="6" pattern="[0-9]{6}" required></label>
        <label>New password<input type="password" name="password" minlength="8" autocomplete="new-password" required><small>Upper/lowercase, a number and a symbol.</small></label>
        <label>Confirm password<input type="password" name="password_confirmation" minlength="8" autocomplete="new-password" required></label>
        <button class="btn btn-primary btn-block">Update password</button>
    </form>
    <form class="inline-form" method="post" action="<?php echo e(route('password.email')); ?>"><?php echo csrf_field(); ?>
        <input type="hidden" name="email" value="<?php echo e($email); ?>">
        <button class="btn btn-ghost btn-block" type="submit" data-otp-resend <?php if(! $canResend): echo 'disabled'; endif; ?>>Resend reset OTP</button>
    </form>
</div></section>
<?php $__env->stopSection(); ?>
<?php $__env->startPush('scripts'); ?>
<script src="<?php echo e(asset('assets/js/otp-timer.js')); ?>"></script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.public', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\medicare\resources\views\auth\reset.blade.php ENDPATH**/ ?>