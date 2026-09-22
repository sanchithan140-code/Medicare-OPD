<?php $__env->startSection('page-title','Reports & Analytics'); ?>
<?php $__env->startSection('content'); ?>
<div class="page-actions">
    <div><h2>Hospital operational analytics</h2><p class="muted">Appointments, completed transactions and finalized diagnosis information.</p></div>
    <form method="post" action="<?php echo e(route('admin.analytics.generate')); ?>"><?php echo csrf_field(); ?><button class="btn btn-primary">Generate weekly trends</button></form>
</div>

<div class="stat-grid stat-grid-3">
    <div class="stat-card accent"><span>Total incoming amount</span><strong>Rs. <?php echo e(number_format($totalRevenue,2)); ?></strong><a href="#transactions">View transactions</a></div>
    <div class="stat-card"><span>Completed transactions</span><strong><?php echo e($payments->count()); ?></strong><a href="#transactions">Latest 100 shown</a></div>
    <div class="stat-card"><span>Payment methods</span><strong><?php echo e($paymentByMethod->count()); ?></strong></div>
</div>

<div class="dashboard-grid">
    <section class="panel">
        <h2>Appointments by status</h2>
        <?php ($maxA=max(1,(int)$appointmentByStatus->max('total'))); ?>
        <?php $__empty_1 = true; $__currentLoopData = $appointmentByStatus; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><div class="metric-bar"><div><strong><?php echo e(str_replace('_',' ',$row->status)); ?></strong><span><?php echo e($row->total); ?></span></div><svg width="<?php echo e(($row->total/$maxA)*100); ?>%" height="11" aria-hidden="true" focusable="false"></svg></div><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><p>No appointments yet.</p><?php endif; ?>
    </section>
    <section class="panel">
        <h2>Incoming payments by method</h2>
        <?php ($maxP=max(1,(float)$paymentByMethod->max('total'))); ?>
        <?php $__empty_1 = true; $__currentLoopData = $paymentByMethod; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><div class="metric-bar"><div><strong><?php echo e(str_replace('_',' ',$row->payment_method)); ?></strong><span>Rs. <?php echo e(number_format($row->total,2)); ?></span></div><svg width="<?php echo e(($row->total/$maxP)*100); ?>%" height="11" aria-hidden="true" focusable="false"></svg></div><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><p>No completed payments yet.</p><?php endif; ?>
    </section>
</div>

<section class="panel" id="transactions">
    <div class="panel-head"><div><span class="kicker">FINANCIAL LEDGER</span><h2>Incoming transactions</h2></div></div>
    <div class="table-scroll"><table>
        <thead><tr><th>Payment</th><th>Date</th><th>Patient</th><th>Booking / Invoice</th><th>Method</th><th>Reference</th><th>Amount</th></tr></thead>
        <tbody>
        <?php $__empty_1 = true; $__currentLoopData = $payments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $payment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <tr>
                <td><?php echo e($payment->payment_no); ?></td>
                <td><?php echo e($payment->paid_at?->format('d M Y h:i A')); ?></td>
                <td><?php echo e($payment->invoice->patient->full_name); ?><small><?php echo e($payment->invoice->patient->patient_code); ?></small></td>
                <td><?php echo e($payment->invoice->labOrder?->lab_order_no ?: $payment->invoice->appointment?->appointment_no ?: '—'); ?><small><?php echo e($payment->invoice->invoice_no); ?></small></td>
                <td><?php echo e(str_replace('_',' ',$payment->payment_method)); ?></td>
                <td><?php echo e($payment->transaction_reference ?: 'Hospital reception'); ?></td>
                <td><strong>Rs. <?php echo e(number_format($payment->amount,2)); ?></strong></td>
            </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><tr><td colspan="7">No completed transactions recorded.</td></tr><?php endif; ?>
        </tbody>
    </table></div>
</section>

<section class="panel">
    <h2>Weekly disease trends</h2>
    <div class="table-scroll"><table><thead><tr><th>Week</th><th>Condition</th><th>Cases</th><th>Share</th><th>Spike</th><th>Health insight</th></tr></thead><tbody><?php $__currentLoopData = $trends; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $trend): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><tr><td><?php echo e($trend->week_start?->format('d M Y')); ?></td><td><?php echo e($trend->condition->condition_name); ?></td><td><?php echo e($trend->case_count); ?></td><td><?php echo e($trend->percentage); ?>%</td><td><?php echo e($trend->is_spike?'YES':'No'); ?></td><td><?php echo e($trend->ai_commentary ?: '—'); ?></td></tr><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></tbody></table></div>
    <?php echo e($trends->links()); ?>

</section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\medicare\resources\views/admin/analytics.blade.php ENDPATH**/ ?>