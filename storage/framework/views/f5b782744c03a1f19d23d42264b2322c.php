<?php if (isset($component)) { $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54 = $attributes; } ?>
<?php $component = App\View\Components\AppLayout::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('app-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AppLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
     <?php $__env->slot('header', null, []); ?> 
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            My Forms
        </h2>
     <?php $__env->endSlot(); ?>
    <div class="flex justify-between items-center mb-6">
        <a href="<?php echo e(route('forms.create')); ?>" class="px-4 py-2 bg-blue-600 text-white rounded">Create New Form</a>
    </div>

    <div class="bg-white shadow rounded-lg p-6">
        <?php if($forms->isEmpty()): ?>
            <p>You have not created any forms yet.</p>
        <?php else: ?>
            <table class="min-w-full">
                <thead>
                <tr>
                    <th class="text-left py-2">Title</th>
                    <th class="text-left py-2">Status</th>
                    <th class="text-left py-2">Created At</th>
                    <th class="text-left py-2">Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php $__currentLoopData = $forms; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $form): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td class="py-2"><?php echo e($form->title); ?></td>
                        <td class="py-2 capitalize"><?php echo e($form->status); ?></td>
                        <td class="py-2"><?php echo e($form->created_at->format('M d, Y')); ?></td>
                        <td class="py-2">
                            <a href="<?php echo e(route('forms.edit', $form)); ?>" class="text-blue-600">Edit</a>
                            |
                            <a href="<?php echo e(route('submissions.index', $form)); ?>" class="text-green-600">Submissions</a>
                            |
                            <form action="<?php echo e(route('forms.destroy', $form)); ?>" method="POST" class="inline-block" onsubmit="return confirm('Are you sure?');">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('DELETE'); ?>
                                <button type="submit" class="text-red-600">Delete</button>
                            </form>
                            <?php if($form->status === 'published'): ?>
                                |
                                <a href="<?php echo e(route('forms.preview', $form)); ?>" class="text-indigo-600">Preview</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $attributes = $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $component = $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?>
<?php /**PATH C:\Users\phpar\Documents\GitHub\submission-platform\resources\views/forms/index.blade.php ENDPATH**/ ?>