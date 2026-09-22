<?php $__env->startSection('page-title','Walk-in Booking'); ?>
<?php $__env->startSection('content'); ?>
<section class="panel">
<h2>Find patient and doctor</h2>
<p class="muted">Search existing patients first to keep their history under one ID.</p>
<form method="get" action="<?php echo e(route('staff.walk-in')); ?>#walk-in-results" class="filter-bar">
<label>Patient ID, name, phone or NIC<input name="q" value="<?php echo e(request('q')); ?>" maxlength="100"></label>
<label>Doctor<select name="doctor_id" required><option value="">Select doctor</option><?php $__currentLoopData = $doctors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $doctor): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($doctor->doctor_id); ?>" <?php if(request('doctor_id')==$doctor->doctor_id): echo 'selected'; endif; ?>><?php echo e($doctor->user->full_name); ?> &middot; Rs. <?php echo e(number_format($doctor->consultation_fee,2)); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></label>
<label>Date<input type="date" name="date" value="<?php echo e(request('date',today()->toDateString())); ?>" min="<?php echo e(today()->toDateString()); ?>" required></label>
<button type="submit" class="btn btn-secondary">Search</button>
</form></section>
<section class="panel" id="walk-in-results" style="scroll-margin-top:110px">
<h2>Patient, appointment and payment</h2><?php if(request()->has('doctor_id')): ?>
<div class="summary-box" role="status">
    <p><strong><?php echo e($patients->count()); ?> matching patient(s) found.</strong>
    <?php if($patients->count()===1): ?><?php echo e($patients->first()->full_name); ?> (ID <?php echo e($patients->first()->patient_code); ?>) is selected below.<?php endif; ?></p>
    <?php if($slots->isEmpty()): ?><p>No bookable slots for this doctor on <?php echo e(request('date')); ?>. Choose another date or doctor. Booking closes when a session starts.</p>
    <?php else: ?><p><?php echo e($slots->count()); ?> available slots. Select a time below to continue.</p><?php endif; ?>
</div>
<?php endif; ?>
<form method="post" action="<?php echo e(route('staff.walk-in.store')); ?>"><?php echo csrf_field(); ?>
<label>Existing patient<select name="patient_id" id="walk-in-patient"><option value="">Create a new walk-in patient</option><?php $__currentLoopData = $patients; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $patient): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($patient->patient_id); ?>" <?php if(old('patient_id',$patients->count()===1 ? $patients->first()->patient_id : '')==$patient->patient_id): echo 'selected'; endif; ?>><?php echo e($patient->patient_code); ?> &middot; <?php echo e($patient->full_name); ?> &middot; <?php echo e($patient->phone); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></label>
<?php if(request('q') && $patients->isEmpty()): ?><p class="muted">No matching patient found. Check the ID or search another detail before creating a new record.</p><?php endif; ?>
<fieldset id="walk-in-new-patient"><legend>New patient details &mdash; fill only when creating a new patient</legend>
<div class="form-grid">
<label>First name<input name="first_name" maxlength="70" value="<?php echo e(old('first_name')); ?>"></label>
<label>Last name<input name="last_name" maxlength="70" value="<?php echo e(old('last_name')); ?>"></label>
<label>Date of birth<input type="date" name="dob" value="<?php echo e(old('dob')); ?>" max="<?php echo e(today()->toDateString()); ?>"></label>
<label>Sex<select name="sex"><?php $__currentLoopData = ['UNKNOWN','MALE','FEMALE','OTHER']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sex): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option <?php if(old('sex')==$sex): echo 'selected'; endif; ?>><?php echo e($sex); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></label>
<label>Phone (optional)<input name="phone" value="<?php echo e(old('phone')); ?>" maxlength="10"></label>
<label>NIC / passport (optional)<input name="nic_passport" value="<?php echo e(old('nic_passport')); ?>" maxlength="30"></label>
</div></fieldset>
<h3>Appointment slot</h3>
<div class="slot-grid"><?php $__empty_1 = true; $__currentLoopData = $slots; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $slot): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><label class="slot-option"><input type="radio" name="slot_id" value="<?php echo e($slot->slot_id); ?>" required><span><?php echo e($slot->slot_start->format('d M Y h:i A')); ?><small>Room <?php echo e($slot->schedule->room_no ?: 'TBA'); ?></small></span></label><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><p>Select a doctor and date with available slots.</p><?php endif; ?></div>
<h3>Payment at hospital</h3>
<div class="form-grid"><label>Payment method<select name="payment_method"><option value="CASH">Cash</option><option value="CARD">Card &mdash; hospital terminal</option></select></label>
<label>Terminal reference (optional)<input name="transaction_reference" maxlength="100"></label></div>
<p>For card payments, complete payment on the hospital card machine first. Do not enter card numbers or CVV here.</p>
<label class="check-row"><input type="checkbox" name="payment_received" value="1" required>I have received the full consultation fee shown above.</label>
<div class="form-actions" style="margin-top:20px"><button class="btn btn-primary" <?php if($slots->isEmpty()): echo 'disabled'; endif; ?>>Confirm booking &amp; create receipt</button></div>
</form></section>
<?php $__env->stopSection(); ?>
<?php $__env->startPush('scripts'); ?>
<script>
(() => {
    const patient = document.getElementById('walk-in-patient');
    const fields = document.getElementById('walk-in-new-patient');
    const update = () => {
        fields.hidden = Boolean(patient.value);
        fields.disabled = Boolean(patient.value);
    };
    patient.addEventListener('change', update);
    update();
})();
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\medicare\resources\views/staff/walk-in.blade.php ENDPATH**/ ?>