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
            <?php echo e(__('Submission #') . $submission->id); ?>

        </h2>
     <?php $__env->endSlot(); ?>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-semibold mb-4"><?php echo e($form->title); ?></h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">
                        Submitted on: <?php echo e($submission->created_at->format('Y-m-d H:i:s')); ?>

                    </p>

                    <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="mb-10">
                            <h3 class="text-xl font-bold mb-4"><?php echo e($category['name']); ?></h3>
                            <?php if($category['description']): ?>
                                <p class="text-gray-600 dark:text-gray-400 mb-6"><?php echo e($category['description']); ?></p>
                            <?php endif; ?>

                            <?php $__currentLoopData = $category['fields']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $field): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="mb-6">
                                    <h4 class="text-md font-medium text-gray-700 dark:text-gray-300"><?php echo e($field['label']); ?></h4>
                                    <?php if($field['type'] === 'file'): ?>
                                        <?php if($field['displayValue']): ?>
                                            <a href="<?php echo e($field['displayValue']); ?>" target="_blank" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                                                View Uploaded File
                                            </a>
                                        <?php else: ?>
                                            <p class="text-gray-500 dark:text-gray-400">No file uploaded</p>
                                        <?php endif; ?>
                                    <?php elseif($field['type'] === 'checkbox'): ?>
                                        <p class="mt-1"><?php echo e($field['displayValue'] ?: 'No'); ?></p>
                                    <?php elseif($field['type'] === 'radio' || $field['type'] === 'select'): ?>
                                        <p class="mt-1"><?php echo e($field['displayValue'] ?: 'Not selected'); ?></p>
                                    <?php elseif($field['type'] === 'textarea'): ?>
                                        <pre class="mt-1 whitespace-pre-wrap"><?php echo e($field['displayValue'] ?: 'N/A'); ?></pre>
                                    <?php else: ?>
                                        <p class="mt-1"><?php echo e($field['displayValue'] ?: 'N/A'); ?></p>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                    <div class="mt-8">
                        <a href="<?php echo e(route('submissions.index', $form)); ?>" class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                            Back to Submissions
                        </a>
                    </div>
                </div>
            </div>
        </div>
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
<?php /**PATH C:\Users\phpar\Documents\GitHub\submission-platform\resources\views/submissions/show.blade.php ENDPATH**/ ?>