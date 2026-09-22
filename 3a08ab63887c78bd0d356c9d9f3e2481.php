<?php $__env->startSection('title','Patient Registration — MEDICARE'); ?>
<?php $__env->startSection('content'); ?>
<section class="auth-page">
    <aside class="auth-visual auth-visual-register">
        <a class="brand brand-light" href="<?php echo e(route('home')); ?>"><img class="brand-logo" src="<?php echo e(asset('assets/images/brand/mediland-hospital-logo.png')); ?>" alt="Mediland Hospital"><span><strong>MEDICARE</strong><small>Patient registration</small></span></a>
        <div><span class="kicker">ONE ACCOUNT · MULTIPLE PROFILES</span><h1>Manage healthcare for yourself and your family.</h1><p>Welcome to MEDICARE! Please fill in the details below to create your account.</p></div>
    </aside>
    <main class="auth-panel">
        <form class="auth-card" method="post" action="<?php echo e(route('register.store')); ?>" novalidate>
            <?php echo csrf_field(); ?>
            <span class="eyebrow">PATIENT ACCOUNT</span><h2>Create your secure account</h2><p class="muted">Welcome!</p>
            <?php if($errors->any()): ?><div class="alert alert-error"><div><strong>Please correct these details:</strong><ul><?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><li><?php echo e($error); ?></li><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></ul></div></div><?php endif; ?>
            <div class="form-grid">
                <label class="span-2">Existing patient ID (if you visited before)
                    <input name="existing_patient_id" value="<?php echo e(old('existing_patient_id')); ?>" inputmode="numeric" maxlength="4" pattern="[0-9]{4}">
                    <small>Use the ID on your hospital receipt. Reception will verify your identity before linking your records. Leave blank only if you are a new patient.</small>
                </label>
                <label class="span-2">Full name
                    <input name="full_name" value="<?php echo e(old('full_name')); ?>" minlength="3" maxlength="150" pattern="[A-Za-zÀ-ž .'-]+" autocomplete="name" required>
                </label>
                <label>Mobile number
                    <input name="phone" value="<?php echo e(old('phone')); ?>" inputmode="numeric" pattern="07[0-9]{8}" minlength="10" maxlength="10" autocomplete="tel" required>
                   
                </label>
                <label>Email address
                    <input type="email" name="email" value="<?php echo e(old('email')); ?>" maxlength="190" autocomplete="email" required>
                   
                </label>
                <label>Password
                    <input type="password" name="password" minlength="8" maxlength="255" autocomplete="new-password" required>
                    
                </label>
                <label>Confirm password
                    <input type="password" name="password_confirmation" minlength="8" maxlength="255" autocomplete="new-password" required>
                </label>
            </div>
            <label class="check-row warning-check"><input type="checkbox" name="terms" value="1" required>I accept the Terms and Conditions.</label>
            <br>
            <button class="btn btn-primary btn-block" type="submit">Create Account</button>
            <p class="auth-switch">Already registered? <a href="<?php echo e(route('login')); ?>">Sign in</a></p>
        </form>
    </main>
</section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.public', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\medicare\resources\views\auth\register.blade.php ENDPATH**/ ?>