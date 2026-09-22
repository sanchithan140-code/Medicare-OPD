<?php $__env->startSection('page-title','Edit Patient Profile'); ?>
<?php $__env->startSection('content'); ?>
<section class="panel">
    <div class="panel-head">
        <div><span class="patient-code">PATIENT ID <?php echo e($patient->patient_code); ?></span><h2>Edit <?php echo e($patient->full_name); ?></h2><p class="muted">Update personal and optional health-contact information. The permanent patient ID cannot be changed.</p></div>
        <a class="btn btn-secondary" href="<?php echo e(route('patient.profiles')); ?>">Back to profiles</a>
    </div>
    <?php if($errors->any()): ?>
        <div class="alert alert-error"><ul><?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><li><?php echo e($error); ?></li><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></ul></div>
    <?php endif; ?>
    <form method="post" action="<?php echo e(route('patient.profiles.update', $patient)); ?>">
        <?php echo csrf_field(); ?>
        <?php echo method_field('PUT'); ?>
        <div class="form-grid">
            <label>Relationship<select name="relationship_to_account" required><?php $__currentLoopData = ['Self','Mother','Father','Spouse','Son','Daughter','Child','Sibling','Other']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($value); ?>" <?php if(old('relationship_to_account', $patient->relationship_to_account) === $value): echo 'selected'; endif; ?>><?php echo e($value); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></label>
            <label>Sex<select name="sex" required><?php $__currentLoopData = ['MALE','FEMALE','OTHER','UNKNOWN']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($value); ?>" <?php if(old('sex', $patient->sex) === $value): echo 'selected'; endif; ?>><?php echo e($value); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></label>
            <label>First name<input name="first_name" value="<?php echo e(old('first_name', $patient->first_name)); ?>" minlength="2" maxlength="80" required></label>
            <label>Last name<input name="last_name" value="<?php echo e(old('last_name', $patient->last_name)); ?>" minlength="2" maxlength="80" required></label>
            <label>Date of birth<input type="date" name="dob" value="<?php echo e(old('dob', $patient->dob?->format('Y-m-d'))); ?>" min="1900-01-01" max="<?php echo e(now()->toDateString()); ?>"></label>
            <label>NIC (optional)<input name="nic_passport" value="<?php echo e(old('nic_passport', $patient->nic_passport)); ?>" maxlength="30"></label>
            <label>Email (optional)<input type="email" name="email" value="<?php echo e(old('email', $patient->email)); ?>" maxlength="190"></label>
            <label>Phone (optional)<input name="phone" value="<?php echo e(old('phone', $patient->phone)); ?>" inputmode="numeric" pattern="[0-9]{10}" minlength="10" maxlength="10" placeholder="0771234567"></label>
            <label class="span-2">Address (optional)<textarea name="address" maxlength="500"><?php echo e(old('address', $patient->address)); ?></textarea></label>
            <label>Blood group<select name="blood_group"><option value="">Unknown</option><?php $__currentLoopData = ['A+','A-','B+','B-','AB+','AB-','O+','O-','UNKNOWN']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($value); ?>" <?php if(old('blood_group', $patient->blood_group) === $value): echo 'selected'; endif; ?>><?php echo e($value); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></label>
            <div></div>
            <label>Emergency contact name<input name="emergency_contact_name" value="<?php echo e(old('emergency_contact_name', $emergencyContact?->contact_name)); ?>" maxlength="120"></label>
            <label>Emergency contact phone<input name="emergency_contact_phone" value="<?php echo e(old('emergency_contact_phone', $emergencyContact?->phone)); ?>" inputmode="numeric" pattern="[0-9]{10}" minlength="10" maxlength="10"></label>
            <label>Known allergen<input name="allergen" value="<?php echo e(old('allergen', $allergy?->allergen)); ?>" maxlength="150"></label>
            <label>Allergy reaction<input name="allergy_reaction" value="<?php echo e(old('allergy_reaction', $allergy?->reaction)); ?>" maxlength="255"></label>
        </div>
        <div class="form-actions"><button class="btn btn-primary">Save profile changes</button></div>
    </form>
</section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\medicare\resources\views\patient\profile-edit.blade.php ENDPATH**/ ?>