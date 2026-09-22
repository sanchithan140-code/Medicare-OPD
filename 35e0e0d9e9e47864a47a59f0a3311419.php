<?php $__env->startSection('page-title','Book for a Patient'); ?>
<?php $__env->startSection('content'); ?>
<div class="dashboard-grid align-start staff-booking-layout">
    <section class="panel">
        <span class="kicker">PHONE / WALK-IN BOOKING</span>
        <h2>Find available consultation slots</h2>
        <p class="muted">Use this workflow when a patient phones or visits reception and cannot book online.</p>
        <form method="get" action="<?php echo e(route('staff.appointments.book')); ?>" class="form-grid">
            <label>Doctor
                <select name="doctor_id" required>
                    <option value="">Select doctor</option>
                    <?php $__currentLoopData = $doctors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $doctor): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($doctor->doctor_id); ?>" <?php if($doctorId === $doctor->doctor_id): echo 'selected'; endif; ?>><?php echo e($doctor->user->full_name); ?> — <?php echo e($doctor->specialty->specialty_name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </label>
            <label>Date<input type="date" name="date" value="<?php echo e($date); ?>" min="<?php echo e(now()->toDateString()); ?>" required></label>
            <div class="span-2"><button class="btn btn-secondary">Show available slots</button></div>
        </form>
    </section>

    <section class="panel">
        <span class="kicker">CONFIRM BOOKING</span>
        <h2>Select patient and slot</h2>
        <form method="post" action="<?php echo e(route('staff.appointments.book.store')); ?>">
            <?php echo csrf_field(); ?>
            <label>Search by patient ID
                <input type="text" id="booking-patient-search" maxlength="50" autocomplete="off" aria-describedby="booking-patient-search-status">
            </label>
            <p id="booking-patient-search-status" class="muted" role="status" aria-live="polite"></p>
            <label>Patient profile
                <select name="patient_id" id="booking-patient-select" data-patient-codes="<?php echo e($patients->pluck('patient_code', 'patient_id')->toJson()); ?>" required>
                    <option value="">Select patient</option>
                    <?php $__currentLoopData = $patients; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $patient): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($patient->patient_id); ?>"><?php echo e($patient->full_name); ?> · <?php echo e($patient->patient_code); ?> · <?php echo e($patient->account?->is_walk_in ? 'Walk-in patient' : $patient->account?->email); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </label>
            <div class="slot-grid">
                <?php $__empty_1 = true; $__currentLoopData = $slots; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $slot): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <label class="slot-option">
                        <input type="radio" name="slot_id" value="<?php echo e($slot->slot_id); ?>" required>
                        <span><strong><?php echo e($slot->slot_start->format('h:i A')); ?></strong><small><?php echo e($slot->slot_start->format('d M Y')); ?> · <?php echo e($slot->schedule->doctor->user->full_name); ?></small></span>
                    </label>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <p class="muted span-all">Choose a doctor and date to display available slots.</p>
                <?php endif; ?>
            </div>
            <label>Booking note (optional)<textarea name="booking_notes" maxlength="500" placeholder="Phone booking, assistance required, preparation note..."></textarea></label>
            <button class="btn btn-primary btn-block" <?php if($slots->isEmpty()): echo 'disabled'; endif; ?>>Book & send patient confirmation</button>
        </form>
    </section>
</div>
<?php $__env->stopSection(); ?>
<?php $__env->startPush('scripts'); ?>
<script>
(() => {
    const search = document.getElementById('booking-patient-search');
    const select = document.getElementById('booking-patient-select');
    const status = document.getElementById('booking-patient-search-status');
    const options = Array.from(select.options).slice(1);
    const codes = JSON.parse(select.dataset.patientCodes);
    search.addEventListener('keydown', event => {
        if (event.key === 'Enter') {
            event.preventDefault();
            search.dispatchEvent(new Event('input'));
        }
    });
    search.addEventListener('input', () => {
        const query = search.value.trim().toLowerCase();
        const matches = options.filter(option => String(codes[option.value]).toLowerCase().includes(query));
        const previous = select.value;
        select.replaceChildren(new Option('Select patient', ''), ...matches);
        const exact = query ? matches.find(option => String(codes[option.value]).toLowerCase() === query) : null;
        select.value = exact ? exact.value : (!query && matches.some(option => option.value === previous) ? previous : '');
        status.textContent = !query ? '' : exact ? 'Patient found and selected.' : matches.length ? `${matches.length} matching patient(s). Select a profile below.` : 'No active patient found with this ID.';
    });
})();
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\medicare\resources\views\staff\book.blade.php ENDPATH**/ ?>