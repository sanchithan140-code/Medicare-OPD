<?php $__env->startSection('page-title','Book an Appointment'); ?>
<?php $__env->startSection('content'); ?>
<div class="dashboard-grid align-start">
    <section class="panel">
        <div class="panel-head"><div><span class="kicker">STEP 1</span><h2>Choose a doctor</h2></div></div>
        <form method="get" action="<?php echo e(route('patient.appointments.book')); ?>">
            <div class="form-grid">
                <label>Doctor
                    <select name="doctor_id" required>
                        <?php $__currentLoopData = $doctors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $doctor): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($doctor->doctor_id); ?>" <?php if($selectedDoctor?->doctor_id === $doctor->doctor_id): echo 'selected'; endif; ?>>
                                <?php echo e($doctor->user->full_name); ?> — <?php echo e($doctor->specialty->specialty_name); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </label>
                <label>Availability period
                    <input value="<?php echo e($rangeStart->format('d M Y')); ?> – <?php echo e($rangeEnd->format('d M Y')); ?>" readonly>
                </label>
            </div>
            <?php if($recommendationId): ?>
                <input type="hidden" name="recommendation_id" value="<?php echo e($recommendationId); ?>">
            <?php endif; ?>
            <button class="btn btn-secondary">Show available slots</button>
        </form>

        <?php if($selectedDoctor): ?>
            <div class="doctor-summary">
                <div class="doctor-avatar"><?php echo e(strtoupper(substr($selectedDoctor->user->full_name, 0, 1))); ?></div>
                <div>
                    <h3><?php echo e($selectedDoctor->user->full_name); ?></h3>
                    <p><?php echo e($selectedDoctor->specialty->specialty_name); ?> · Rs. <?php echo e(number_format($selectedDoctor->consultation_fee, 2)); ?></p>
                </div>
            </div>
            <div class="availability-days">
                <?php $__empty_1 = true; $__currentLoopData = $availability; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $availableDate => $daySlots): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <a class="availability-day <?php echo e($selectedDate === $availableDate ? 'selected' : ''); ?>"
                       href="<?php echo e(route('patient.appointments.book', array_filter(['doctor_id' => $selectedDoctor->doctor_id, 'date' => $availableDate, 'recommendation_id' => $recommendationId]))); ?>#booking-slots">
                        <span class="date-tile">
                            <strong><?php echo e($daySlots->first()->slot_start->format('d')); ?></strong>
                            <span><?php echo e($daySlots->first()->slot_start->format('M')); ?></span>
                        </span>
                        <span>
                            <strong><?php echo e($daySlots->first()->slot_start->format('l, d M Y')); ?></strong>
                            <small><?php echo e($daySlots->first()->slot_start->format('h:i A')); ?> – <?php echo e($daySlots->last()->slot_start->format('h:i A')); ?> · <?php echo e($daySlots->count()); ?> slots</small>
                        </span>
                        <b>Choose →</b>
                    </a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <p class="muted">No available dates for this doctor during the next 20 days.</p>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </section>

    <?php if($selectedDate): ?>
    <section class="panel" id="booking-slots">
        <div class="panel-head"><div><span class="kicker">STEP 2</span><h2>Select profile and slot</h2></div></div>
        <form method="post" action="<?php echo e(route('patient.appointments.store')); ?>">
            <?php echo csrf_field(); ?>
            <?php if($recommendationId): ?>
                <input type="hidden" name="recommendation_id" value="<?php echo e($recommendationId); ?>">
            <?php endif; ?>
            <label>Patient profile
                <select name="patient_id" required>
                    <?php $__currentLoopData = $patients; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $patient): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($patient->patient_id); ?>" <?php if((string) old('patient_id', $accountPatientId) === (string) $patient->patient_id): echo 'selected'; endif; ?>><?php echo e($patient->patient_id === $accountPatientId ? auth()->user()->full_name.' (You)' : $patient->full_name.' ('.$patient->relationship_to_account.')'); ?> · ID <?php echo e($patient->patient_code); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </label>
            <?php $__empty_1 = true; $__currentLoopData = $slots->groupBy(fn ($slot) => $slot->slot_start->toDateString()); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $daySlots): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="panel-head">
                    <div>
                        <span class="kicker"><?php echo e($daySlots->first()->slot_start->format('l')); ?></span>
                        <h3><?php echo e($daySlots->first()->slot_start->format('d M Y')); ?></h3>
                    </div>
                    <span class="muted"><?php echo e($daySlots->count()); ?> available</span>
                </div>
                <div class="slot-grid">
                    <?php $__currentLoopData = $daySlots; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $slot): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <label class="slot-option" data-schedule-expires="<?php echo e(\Carbon\Carbon::parse($slot->schedule->schedule_date->toDateString().' '.$slot->schedule->start_time)->getTimestamp()); ?>">
                            <input type="radio" name="slot_id" value="<?php echo e($slot->slot_id); ?>" required>
                            <span><strong><?php echo e($slot->slot_start->format('h:i A')); ?></strong><small>Available</small></span>
                        </label>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <p class="muted"><?php echo e($selectedDoctor ? 'No available slots for this doctor during the next 20 days.' : 'Choose a doctor to display availability for the next 20 days.'); ?></p>
            <?php endif; ?>
            <button class="btn btn-primary btn-block" <?php if($slots->isEmpty()): echo 'disabled'; endif; ?>>Confirm appointment</button>
        </form>
    </section>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?><?php if (isset($component)) { $__componentOriginal7efc54c829dd0d3789fae18771bdd23a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal7efc54c829dd0d3789fae18771bdd23a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.schedule-expiry-script','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('schedule-expiry-script'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal7efc54c829dd0d3789fae18771bdd23a)): ?>
<?php $attributes = $__attributesOriginal7efc54c829dd0d3789fae18771bdd23a; ?>
<?php unset($__attributesOriginal7efc54c829dd0d3789fae18771bdd23a); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal7efc54c829dd0d3789fae18771bdd23a)): ?>
<?php $component = $__componentOriginal7efc54c829dd0d3789fae18771bdd23a; ?>
<?php unset($__componentOriginal7efc54c829dd0d3789fae18771bdd23a); ?>
<?php endif; ?><?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\medicare\resources\views\patient\book.blade.php ENDPATH**/ ?>