<?php $__env->startSection('page-title','Walk-in Receipts'); ?>
<?php $__env->startSection('content'); ?>
<section class="panel">
    <h2>All walk-in receipts</h2>
    <p class="muted">Receipts are saved automatically when a walk-in booking is completed.</p>
    <form method="get" action="<?php echo e(route('staff.walkin-receipts')); ?>" class="filter-bar">
        <label>Patient ID, name or receipt number<input name="q" value="<?php echo e(request('q')); ?>" maxlength="100"></label>
        <label>Receipt date<input type="date" name="date" value="<?php echo e(request('date')); ?>"></label>
        <button class="btn btn-primary">Search</button>
        <a class="btn btn-secondary" href="<?php echo e(route('staff.walkin-receipts')); ?>">Clear filters</a>
    </form>
</section>
<div class="table-card"><div class="table-scroll"><table>
<thead><tr><th>Receipt / Date</th><th>Patient / ID</th><th>Doctor</th><th>Paid</th><th>Method</th><th>Receipt</th></tr></thead>
<tbody><?php $__empty_1 = true; $__currentLoopData = $invoices; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $invoice): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
<tr><td><strong><?php echo e($invoice->invoice_no); ?></strong><small><?php echo e($invoice->issued_at?->format('d M Y h:i A')); ?></small></td>
<td><?php echo e($invoice->patient->full_name); ?><small><?php echo e($invoice->patient->patient_code); ?></small></td>
<td><?php echo e($invoice->appointment?->doctor?->user?->full_name); ?></td>
<td>Rs. <?php echo e(number_format($invoice->paid_amount,2)); ?></td>
<td><?php echo e($invoice->payments->where('status','COMPLETED')->pluck('payment_method')->unique()->join(', ')); ?></td>
<td><a class="btn btn-secondary btn-sm" href="<?php echo e(route('staff.receipt',$invoice)); ?>">View / print</a></td></tr>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><tr><td colspan="6">No walk-in receipts found.</td></tr><?php endif; ?></tbody>
</table></div></div>
<?php echo e($invoices->links()); ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\medicare\resources\views/staff/walk-in-receipts.blade.php ENDPATH**/ ?>