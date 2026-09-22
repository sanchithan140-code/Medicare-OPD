<?php if($paginator->hasPages()): ?>
    <nav class="portal-pagination" aria-label="Pagination">
        <p class="portal-pagination-summary">
            Showing <strong><?php echo e($paginator->firstItem()); ?></strong> to <strong><?php echo e($paginator->lastItem()); ?></strong>
            of <strong><?php echo e($paginator->total()); ?></strong> results
        </p>
        <ul class="portal-pagination-links">
            <li>
                <?php if($paginator->onFirstPage()): ?>
                    <span class="portal-page disabled" aria-disabled="true">&lsaquo; Previous</span>
                <?php else: ?>
                    <a class="portal-page" href="<?php echo e($paginator->previousPageUrl()); ?>" rel="prev">&lsaquo; Previous</a>
                <?php endif; ?>
            </li>
            <?php $__currentLoopData = $elements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $element): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php if(is_string($element)): ?>
                    <li class="portal-page-number"><span class="portal-page disabled"><?php echo e($element); ?></span></li>
                <?php elseif(is_array($element)): ?>
                    <?php $__currentLoopData = $element; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $page => $url): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li class="portal-page-number">
                            <?php if($page == $paginator->currentPage()): ?>
                                <span class="portal-page current" aria-current="page"><?php echo e($page); ?></span>
                            <?php else: ?>
                                <a class="portal-page" href="<?php echo e($url); ?>" aria-label="Go to page <?php echo e($page); ?>"><?php echo e($page); ?></a>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <li>
                <?php if($paginator->hasMorePages()): ?>
                    <a class="portal-page" href="<?php echo e($paginator->nextPageUrl()); ?>" rel="next">Next &rsaquo;</a>
                <?php else: ?>
                    <span class="portal-page disabled" aria-disabled="true">Next &rsaquo;</span>
                <?php endif; ?>
            </li>
        </ul>
    </nav>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\medicare\resources\views/vendor/pagination/bootstrap-5.blade.php ENDPATH**/ ?>