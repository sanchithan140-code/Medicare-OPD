<?php $__env->startSection('page-title','Find a Doctor'); ?>
<?php $__env->startSection('content'); ?>
<section class="panel">
    <form class="filter-bar" method="get">
        <input name="q" value="<?php echo e(request('q')); ?>" placeholder="Doctor, specialty or qualification">
        <select name="specialty_id">
            <option value="">All specialties</option>
            <?php $__currentLoopData = $specialties; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $specialty): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($specialty->specialty_id); ?>" <?php if(request('specialty_id') == $specialty->specialty_id): echo 'selected'; endif; ?>><?php echo e($specialty->specialty_name); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
        <button class="btn btn-primary">Search</button>
    </form>
</section>

<div class="doctor-grid">
    <?php $__empty_1 = true; $__currentLoopData = $doctors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $doctor): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <article class="doctor-card">
            <div class="doctor-photo">
                <img src="<?php echo e(asset('images/doctor-a-rahman.png')); ?>" alt="Portrait of <?php echo e($doctor->user->full_name); ?>">
            </div>
            <div class="doctor-body">
                <span class="kicker"><?php echo e($doctor->specialty->specialty_name); ?></span>
                <h3><?php echo e($doctor->user->full_name); ?></h3>
                <p><?php echo e($doctor->qualifications_summary ?: 'Visiting consultant at Mediland Hospital'); ?></p>
                <div class="doctor-meta">
                    <span>Fee: Rs. <?php echo e(number_format($doctor->consultation_fee,2)); ?></span>
                    <span class="availability-dot">● Active</span>
                </div>
                <a class="btn btn-primary btn-block" href="<?php echo e(route('patient.appointments.book',['doctor_id'=>$doctor->doctor_id])); ?>">Check availability</a>
            </div>
        </article>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <?php if (isset($component)) { $__componentOriginal4f22a152e0729cd34293e65bd200d933 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4f22a152e0729cd34293e65bd200d933 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.empty','data' => ['title' => 'No matching doctors','message' => 'Change the specialty or search term.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('empty'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'No matching doctors','message' => 'Change the specialty or search term.']); ?>
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
<?php echo e($doctors->links()); ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\medicare\resources\views/patient/doctors.blade.php ENDPATH**/ ?>