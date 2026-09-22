<aside class="daily-health-tip" aria-label="Health tip">
    <strong>Health tip</strong>
    <div class="health-tip-window"><p class="health-tip-text"><?php $__currentLoopData = collect(\App\Services\DailyHealthTipService::TIPS)->shuffle(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tip): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><span><?php echo e($tip); ?></span><?php if(!$loop->last): ?><span class="health-tip-separator" aria-hidden="true">&bull;</span><?php endif; ?> <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></p></div>
</aside>
<?php /**PATH C:\xampp\htdocs\medicare\resources\views\components\daily-health-tip.blade.php ENDPATH**/ ?>