<?php $__env->startSection('title','Login — MEDICARE'); ?>
<?php $__env->startSection('content'); ?>
<section class="auth-page">
    <aside class="auth-visual auth-visual-login">
        <a class="brand brand-light" href="<?php echo e(route('home')); ?>">
            <img class="brand-logo" src="<?php echo e(asset('assets/images/brand/mediland-hospital-logo.png')); ?>" alt="Mediland Hospital logo">
            <span><strong>MEDICARE</strong><small>Secure role-based access</small></span>
        </a>
        <div>
            <span class="kicker">Welcome back</span>
            <h1>Continue your healthcare journey securely.</h1>
            <p>We are committed to providing you with a seamless and secure experience.</p>
        </div>
    </aside>

    <main class="auth-panel">
        <form class="auth-card" method="post" action="<?php echo e(route('login.store')); ?>">
            <?php echo csrf_field(); ?>
            <span class="eyebrow">PORTAL LOGIN</span>
            <h2>Sign in to MEDICARE</h2>
            <p class="muted">Welcome back!</p>
            <?php if($errors->any()): ?>
                <div class="alert alert-error"><?php echo e($errors->first()); ?></div>
            <?php endif; ?>
            <?php if($lockSeconds > 0): ?>
            <div id="login-lockout" class="alert" role="status">
                Try again in <strong id="login-countdown"><?php echo e(sprintf('%02d:%02d', intdiv($lockSeconds, 60), $lockSeconds % 60)); ?></strong>.
            </div>
            <?php endif; ?>
            <label>Email address
                <input type="email" name="email" value="<?php echo e(old('email')); ?>" required autocomplete="email">
            </label>
            <label>Password
                <span class="password-wrap">
                    <input id="login-password" type="password" name="password" required autocomplete="current-password">
                    <button class="password-toggle" type="button" data-password-toggle="login-password" aria-label="Show password" aria-pressed="false">Show</button>
                </span>
            </label>
            <div class="form-row-between login-options">
                <label class="check-row">
                    <input type="checkbox" name="remember" value="1">
                    <span>Remember me</span>
                </label>
                <a href="<?php echo e(route('password.request')); ?>">Forgot password?</a>
            </div>
            <button id="login-submit" class="btn btn-primary btn-block" type="submit" <?php if($lockSeconds > 0): echo 'disabled'; endif; ?>>Sign in</button>
            <p class="auth-switch">New patient? <a href="<?php echo e(route('register')); ?>">Create account</a></p>
        </form>
    </main>
</section>
<script>
(() => {
    const seconds = <?php echo json_encode($lockSeconds, 15, 512) ?>;
    if (seconds <= 0) return;
    const deadline = Date.now() + seconds * 1000;
    const button = document.getElementById('login-submit');
    const notice = document.getElementById('login-lockout');
    const countdown = document.getElementById('login-countdown');
    const timer = setInterval(() => {
        const remaining = Math.max(0, Math.ceil((deadline - Date.now()) / 1000));
        countdown.textContent = `${String(Math.floor(remaining / 60)).padStart(2, '0')}:${String(remaining % 60).padStart(2, '0')}`;
        if (remaining === 0) {
            clearInterval(timer);
            button.disabled = false;
            notice.remove();
        }
    }, 250);
})();
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.public', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\medicare\resources\views/auth/login.blade.php ENDPATH**/ ?>