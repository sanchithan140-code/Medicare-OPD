 <?php $__env->startSection('page-title','Administration Dashboard'); ?> <?php $__env->startSection('content'); ?><div class="stat-grid stat-grid-4"><div class="stat-card"><span>Portal users</span><strong><?php echo e($users); ?></strong><a href="<?php echo e(route('admin.users')); ?>">Manage users</a></div><div class="stat-card"><span>Active doctors</span><strong><?php echo e($doctors); ?></strong><a href="<?php echo e(route('admin.doctors')); ?>">Doctors</a></div><div class="stat-card"><span>Total appointments</span><strong><?php echo e($appointments); ?></strong><a href="<?php echo e(route('staff.appointments')); ?>">Operations</a></div><div class="stat-card"><span>Recorded revenue</span><strong>Rs. <?php echo e(number_format($revenue,2)); ?></strong><a href="<?php echo e(route('admin.analytics')); ?>">Analytics</a></div></div><div class="dashboard-grid"><section class="panel"><div class="panel-head"><h2>Weekly disease trends</h2><a class="btn btn-secondary btn-sm" href="<?php echo e(route('admin.analytics')); ?>">View analytics</a></div><?php $__empty_1 = true; $__currentLoopData = $trends; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><div class="trend-row"><div><strong><?php echo e($t->condition->condition_name); ?></strong><small><?php echo e($t->case_count); ?> finalized case(s)</small></div><b><?php echo e($t->percentage); ?>%</b></div><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><p class="muted">No trend data generated.</p><?php endif; ?></section><section class="panel admin-review-card">
    <div class="admin-review-heading">
        <span class="kicker">Doctor leave</span>
        <span class="admin-review-status <?php echo e($pendingLeaves > 0 ? 'is-pending' : ''); ?>"><?php echo e($pendingLeaves > 0 ? 'Needs review' : 'Up to date'); ?></span>
    </div>
    <h2><?php echo e($pendingLeaves > 0 ? $pendingLeaves.' pending leave '.($pendingLeaves == 1 ? 'request' : 'requests') : 'No pending leave requests'); ?></h2>
    <p class="muted"><?php echo e($pendingLeaves > 0 ? 'Review requests and manage affected appointment slots.' : 'New doctor leave requests will appear here for review.'); ?></p>
    <a class="btn btn-secondary btn-sm" href="<?php echo e(route('admin.doctors')); ?>"><?php echo e($pendingLeaves > 0 ? 'Review leave requests' : 'Manage doctors'); ?></a>
    <div class="admin-audit-section">
        <div>
            <h3>Activity &amp; audit log</h3>
            <p class="muted">Review recorded account activity and sensitive actions.</p>
        </div>
        <a class="admin-audit-link" href="<?php echo e(route('admin.audit')); ?>">View audit log <span aria-hidden="true">&rarr;</span></a>
    </div>
</section></div><?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\medicare\resources\views\admin\dashboard.blade.php ENDPATH**/ ?>