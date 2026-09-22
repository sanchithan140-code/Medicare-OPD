<?php $__env->startSection('title','Account Settings — MEDICARE'); ?>
<?php $__env->startSection('page-title','Account Settings'); ?>
<?php $__env->startSection('content'); ?>
<div class="dashboard-grid align-start">
    <section class="panel">
        <span class="kicker">SECURE ACCOUNT</span>
        <h2>Personal details</h2>
        <form method="post" action="<?php echo e(route('account.update')); ?>" class="form-grid">
            <?php echo csrf_field(); ?> <?php echo method_field('put'); ?>
            <label class="span-2">Full name
                <input name="full_name" value="<?php echo e(old('full_name',auth()->user()->full_name)); ?>" minlength="3" maxlength="150" required autocomplete="name">
            </label>
            <label>Email address
                <input type="email" name="email" value="<?php echo e(auth()->user()->email); ?>" readonly autocomplete="email">
            </label>
            <label>Mobile number
                <input name="phone" value="<?php echo e(old('phone',auth()->user()->phone)); ?>" inputmode="numeric" pattern="[0-9]{10}" minlength="10" maxlength="10" required autocomplete="tel" placeholder="0771234567">
            </label>
            <div class="span-2 section-divider"><strong>Change password (optional)</strong></div>
            <label>Current password<input type="password" name="current_password" autocomplete="current-password"></label>
            <label>New password<input type="password" name="password" minlength="8" autocomplete="new-password"></label>
            <label>Confirm new password<input type="password" name="password_confirmation" minlength="8" autocomplete="new-password"></label>
            <div class="span-2 form-actions"><button class="btn btn-primary" type="submit">Save account settings</button></div>
        </form>
    </section>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\medicare\resources\views\account\edit.blade.php ENDPATH**/ ?>