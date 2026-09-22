<?php $__env->startSection('title','Users & Access — MEDICARE'); ?>
<?php $__env->startSection('page-title','Users & Access Control'); ?>
<?php $__env->startSection('content'); ?>
<div class="users-access-layout"><section class="panel users-create-panel"><span class="kicker">ROLE-BASED ACCESS</span><h2>Create portal user</h2><form method="post" action="<?php echo e(route('admin.users.store')); ?>"><?php echo csrf_field(); ?><label>Role<select name="role_id" required><?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($role->role_id); ?>"><?php echo e($role->display_name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></label><label>Full name<input name="full_name" minlength="3" maxlength="150" required></label><label>Email<input type="email" name="email" maxlength="190" required></label><label>Mobile number<input name="phone" inputmode="numeric" pattern="[0-9]{10}" minlength="10" maxlength="10" placeholder="0771234567" required></label><label>Temporary password<input type="password" name="password" minlength="8" required><small>Upper/lowercase, number and symbol required.</small></label><button class="btn btn-primary">Create portal user</button></form></section>
<section class="table-card users-directory"><div class="users-directory-heading"><div><span class="kicker">PORTAL DIRECTORY</span><h2>User accounts</h2></div><span class="users-count"><?php echo e($users->total()); ?> users</span></div><div class="table-scroll" tabindex="0" role="region" aria-label="User accounts"><table><thead><tr><th>User</th><th>Role</th><th>Status</th><th>Verified</th><th>Action</th></tr></thead><tbody><?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><tr><td><strong><?php echo e($user->full_name); ?></strong><small><?php echo e($user->email); ?></small><small><?php echo e($user->phone); ?></small></td><td><?php echo e($user->role->display_name); ?></td><td><?php if (isset($component)) { $__componentOriginal51ed764111e345fc11534f121cfeb451 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal51ed764111e345fc11534f121cfeb451 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.status','data' => ['value' => $user->account_status]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('status'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($user->account_status)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal51ed764111e345fc11534f121cfeb451)): ?>
<?php $attributes = $__attributesOriginal51ed764111e345fc11534f121cfeb451; ?>
<?php unset($__attributesOriginal51ed764111e345fc11534f121cfeb451); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal51ed764111e345fc11534f121cfeb451)): ?>
<?php $component = $__componentOriginal51ed764111e345fc11534f121cfeb451; ?>
<?php unset($__componentOriginal51ed764111e345fc11534f121cfeb451); ?>
<?php endif; ?></td><td><?php echo e($user->email_verified_at?'Yes':'No'); ?></td><td><?php if($user->user_id!==auth()->id() && $user->role->role_name!=='main_admin'): ?><form method="post" action="<?php echo e(route('admin.users.toggle',$user)); ?>"><?php echo csrf_field(); ?><button class="btn btn-sm <?php echo e($user->account_status==='ACTIVE'?'btn-danger':'btn-secondary'); ?>"><?php echo e($user->account_status==='ACTIVE'?'Disable':'Activate'); ?></button></form><?php else: ?><span class="muted">Protected</span><?php endif; ?></td></tr><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></tbody></table></div><?php echo e($users->links()); ?></section></div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\medicare\resources\views\admin\users.blade.php ENDPATH**/ ?>