<?php $__env->startSection('title','Patient Management — MEDICARE'); ?>
<?php $__env->startSection('page-title','Patient Management'); ?>
<?php $__env->startSection('content'); ?>
<div class="page-actions"><form class="filter-bar"><input name="q" value="<?php echo e(request('q')); ?>" maxlength="100" placeholder="Patient ID, name or phone"><button class="btn btn-secondary">Search</button></form><div class="button-row"><a class="btn btn-secondary" href="<?php echo e(route('staff.appointments.book')); ?>">Book appointment</a><button class="btn btn-primary" data-modal-open="patient-modal">Register walk-in profile</button></div></div>
<div class="table-card"><div class="table-scroll"><table><thead><tr><th>ID</th><th>Patient</th><th>Account</th><th>Relationship</th><th>Phone</th><th>Status</th></tr></thead><tbody><?php $__currentLoopData = $patients; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $patient): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><tr><td><span class="patient-code"><?php echo e($patient->patient_code); ?></span></td><td><?php echo e($patient->full_name); ?></td><td><?php echo e($patient->account?->is_walk_in ? 'Walk-in patient' : $patient->account?->email); ?></td><td><?php echo e($patient->relationship_to_account); ?></td><td><?php echo e($patient->phone ?: '—'); ?></td><td><?php if (isset($component)) { $__componentOriginal51ed764111e345fc11534f121cfeb451 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal51ed764111e345fc11534f121cfeb451 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.status','data' => ['value' => $patient->active?'ACTIVE':'INACTIVE']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('status'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($patient->active?'ACTIVE':'INACTIVE')]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal51ed764111e345fc11534f121cfeb451)): ?>
<?php $attributes = $__attributesOriginal51ed764111e345fc11534f121cfeb451; ?>
<?php unset($__attributesOriginal51ed764111e345fc11534f121cfeb451); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal51ed764111e345fc11534f121cfeb451)): ?>
<?php $component = $__componentOriginal51ed764111e345fc11534f121cfeb451; ?>
<?php unset($__componentOriginal51ed764111e345fc11534f121cfeb451); ?>
<?php endif; ?></td></tr><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></tbody></table></div></div><?php echo e($patients->links()); ?>

<div class="modal" id="patient-modal"><div class="modal-card"><div class="panel-head"><h2>Create patient under an existing account</h2><button type="button" class="icon-btn" data-modal-close>×</button></div><form method="post" action="<?php echo e(route('staff.patients.store')); ?>"><?php echo csrf_field(); ?><div class="form-grid"><label class="span-2">Registered account email<input type="email" name="account_email" maxlength="190" required></label><label>First name<input name="first_name" minlength="2" maxlength="80" required></label><label>Last name<input name="last_name" minlength="2" maxlength="80" required></label><label>Relationship<select name="relationship_to_account" required><?php $__currentLoopData = ['Self','Mother','Father','Spouse','Son','Daughter','Child','Sibling','Other']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option><?php echo e($value); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></label><label>Sex<select name="sex" required><?php $__currentLoopData = ['MALE','FEMALE','OTHER','UNKNOWN']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option><?php echo e($value); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></label><label>Date of birth<input type="date" name="dob" min="1900-01-01" max="<?php echo e(now()->toDateString()); ?>"></label><label>Phone<input name="phone" inputmode="numeric" pattern="[0-9]{10}" minlength="10" maxlength="10" placeholder="0771234567"></label></div><button class="btn btn-primary">Create patient profile</button></form></div></div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\medicare\resources\views\staff\patients.blade.php ENDPATH**/ ?>