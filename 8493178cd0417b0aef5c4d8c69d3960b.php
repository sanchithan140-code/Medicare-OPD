<?php $__env->startSection('title','Family Profiles — MEDICARE'); ?>
<?php $__env->startSection('page-title','Family Patient Profiles'); ?>
<?php $__env->startSection('content'); ?>
<div class="page-actions">
    <div><h2>Separate records for every family member</h2><p class="muted">Each profile receives a permanent four-digit patient ID.</p></div>
    <button class="btn btn-primary" data-modal-open="profile-modal">Add profile</button>
</div>
<div class="profile-grid">
    <?php $__empty_1 = true; $__currentLoopData = $patients; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $patient): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <article class="family-profile-card">
            <div class="family-profile-heading">
                <div class="family-profile-avatar" aria-hidden="true"><?php echo e(mb_strtoupper(mb_substr($patient->first_name, 0, 1))); ?></div>
                <div class="family-profile-identity">
                    <h3><?php echo e($patient->full_name); ?></h3>
                    <p><?php echo e($patient->relationship_to_account); ?> &middot; <?php echo e(ucfirst(strtolower($patient->sex))); ?><?php if($patient->dob): ?> &middot; <?php echo e($patient->dob->age); ?> years <?php endif; ?></p>
                </div>
                <span class="family-profile-id">ID <?php echo e($patient->patient_code); ?></span>
            </div>
            <dl class="family-profile-details">
                <div><dt>Phone number</dt><dd><?php echo e($patient->phone ?: 'Not provided'); ?></dd></div>
                <div><dt>Blood group</dt><dd><?php echo e($patient->blood_group && $patient->blood_group !== 'UNKNOWN' ? $patient->blood_group : 'Not provided'); ?></dd></div>
            </dl>
            <div class="family-profile-footer">
                <a class="btn btn-sm btn-secondary" href="<?php echo e(route('patient.profiles.edit', $patient)); ?>" aria-label="Edit profile for <?php echo e($patient->full_name); ?>">Edit profile <span aria-hidden="true">&rarr;</span></a>
            </div>
        </article>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <?php if (isset($component)) { $__componentOriginal4f22a152e0729cd34293e65bd200d933 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4f22a152e0729cd34293e65bd200d933 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.empty','data' => ['title' => 'Create the first patient profile','message' => 'Your account needs at least one profile before booking.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('empty'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Create the first patient profile','message' => 'Your account needs at least one profile before booking.']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal4f22a152e0729cd34293e65bd200d933)): ?>
<?php $attributes = $__attributesOriginal4f22a152e0729cd34293e65bd200d933; ?>
<?php unset($__attributesOriginal4f22a152e0729cd34293e65bd200d933); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal4f22a152e0729cd34293e65bd200d933)): ?>
<?php $component = $__componentOriginal4f22a152e0729cd34293e65bd200d933; ?>
<?php unset($__componentOriginal4f22a152e0729cd34293e65bd200d933); ?>
<?php endif; ?>
    <?php endif; ?>
</div>
<div class="modal" id="profile-modal">
    <div class="modal-card modal-card-lg">
        <div class="panel-head"><div><span class="kicker">NEW PATIENT RECORD</span><h2>Add family patient profile</h2></div><button type="button" class="icon-btn" data-modal-close aria-label="Close">×</button></div>
        <form method="post" action="<?php echo e(route('patient.profiles.store')); ?>"><?php echo csrf_field(); ?>
            <div class="form-grid">
                <label>Relationship<select name="relationship_to_account" required><?php $__currentLoopData = ['Self','Mother','Father','Spouse','Son','Daughter','Child','Sibling','Other']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($value); ?>" <?php if(old('relationship_to_account') === $value): echo 'selected'; endif; ?>><?php echo e($value); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></label>
                <label>Sex<select name="sex" required><?php $__currentLoopData = ['MALE','FEMALE','OTHER','UNKNOWN']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($value); ?>" <?php if(old('sex') === $value): echo 'selected'; endif; ?>><?php echo e($value); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></label>
                <label>First name<input name="first_name" value="<?php echo e(old('first_name')); ?>" minlength="2" maxlength="80" required></label>
                <label>Last name<input name="last_name" value="<?php echo e(old('last_name')); ?>" minlength="2" maxlength="80" required></label>
                <label>Date of birth<input type="date" name="dob" value="<?php echo e(old('dob')); ?>" min="1900-01-01" max="<?php echo e(now()->toDateString()); ?>"></label>
                <label>NIC (optional)<input name="nic_passport" value="<?php echo e(old('nic_passport')); ?>" maxlength="30" placeholder="200012345678 or 901234567V"></label>
                <label>Email<input type="email" name="email" value="<?php echo e(old('email')); ?>" maxlength="190"></label>
                <label>Phone<input name="phone" value="<?php echo e(old('phone')); ?>" inputmode="numeric" pattern="[0-9]{10}" minlength="10" maxlength="10" placeholder="0771234567"></label>
                <label class="span-2">Address<textarea name="address" maxlength="500"><?php echo e(old('address')); ?></textarea></label>
                <label>Blood group<select name="blood_group"><option value="">Unknown</option><?php $__currentLoopData = ['A+','A-','B+','B-','AB+','AB-','O+','O-','UNKNOWN']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($value); ?>" <?php if(old('blood_group') === $value): echo 'selected'; endif; ?>><?php echo e($value); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></label>
                <label>Emergency contact name<input name="emergency_contact_name" value="<?php echo e(old('emergency_contact_name')); ?>" maxlength="120"></label>
                <label>Emergency contact phone<input name="emergency_contact_phone" value="<?php echo e(old('emergency_contact_phone')); ?>" inputmode="numeric" pattern="[0-9]{10}" minlength="10" maxlength="10"></label>
                <label>Known allergen<input name="allergen" value="<?php echo e(old('allergen')); ?>" maxlength="150"></label>
                <label class="span-2">Allergy reaction<input name="allergy_reaction" value="<?php echo e(old('allergy_reaction')); ?>" maxlength="255"></label>
            </div>
            <div class="form-actions"><button class="btn btn-primary">Create secure profile</button></div>
        </form>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\medicare\resources\views\patient\profiles.blade.php ENDPATH**/ ?>