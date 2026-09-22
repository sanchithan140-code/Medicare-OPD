MEDICARE | Mediland Hospital OPD
<?php echo e($heading); ?>


<?php $__currentLoopData = $lines; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $line): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<?php echo e($line); ?>


<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php if($actionText && $actionUrl): ?>
<?php echo e($actionText); ?>: <?php echo e($actionUrl); ?>


<?php endif; ?>
Kind regards,
Mediland Hospital Team
Kalmunai, Sri Lanka
<?php /**PATH C:\xampp\htdocs\medicare\resources\views\emails\hospital-message-text.blade.php ENDPATH**/ ?>