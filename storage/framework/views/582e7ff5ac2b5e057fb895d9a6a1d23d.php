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
            <?php echo e($form->title); ?> - Preview
        </h2>
     <?php $__env->endSlot(); ?>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <p class="mb-4"><?php echo e($form->description); ?></p>

                    <form x-data="{ step: 1, totalSteps: <?php echo e($form->categories->count()); ?> }">
                        <!-- Progress bar -->
                        <div class="mb-4">
                            <div class="overflow-hidden h-2 mb-4 text-xs flex rounded bg-gray-200 dark:bg-gray-700">
                                <div :style="'width:' + (step / totalSteps * 100) + '%'" class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-blue-500"></div>
                            </div>
                            <div class="text-center">
                                Step <span x-text="step"></span> of <span x-text="totalSteps"></span>
                            </div>
                        </div>

                        <?php $__currentLoopData = $form->categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div x-show="step === <?php echo e($index + 1); ?>">
                                <h3 class="text-lg font-semibold mb-4"><?php echo e($category->name); ?></h3>
                                <p class="mb-4"><?php echo e($category->description); ?></p>

                                <?php $__currentLoopData = $category->fields; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $field): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="mb-4">
                                        <label class="block text-gray-700 dark:text-gray-300"><?php echo e($field->label); ?><?php echo e($field->required ? '*' : ''); ?></label>

                                        <?php if($field->type === 'text'): ?>
                                            <input type="text" class="w-full mt-2 p-2 border rounded dark:bg-gray-700 dark:border-gray-600" <?php echo e($field->required ? 'required' : ''); ?>>
                                        <?php elseif($field->type === 'textarea'): ?>
                                            <textarea class="w-full mt-2 p-2 border rounded dark:bg-gray-700 dark:border-gray-600" <?php echo e($field->required ? 'required' : ''); ?>></textarea>
                                        <?php elseif(in_array($field->type, ['select', 'checkbox', 'radio'])): ?>
                                            <?php
                                                $options = explode(',', $field->options);
                                            ?>

                                            <?php if($field->type === 'select'): ?>
                                                <select class="w-full mt-2 p-2 border rounded dark:bg-gray-700 dark:border-gray-600" <?php echo e($field->required ? 'required' : ''); ?>>
                                                    <?php $__currentLoopData = $options; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <option value="<?php echo e(trim($option)); ?>"><?php echo e(trim($option)); ?></option>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                </select>
                                            <?php else: ?>
                                                <?php $__currentLoopData = $options; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <div class="flex items-center mt-2">
                                                        <input type="<?php echo e($field->type); ?>" name="field_<?php echo e($field->id); ?>" value="<?php echo e(trim($option)); ?>" class="mr-2">
                                                        <label><?php echo e(trim($option)); ?></label>
                                                    </div>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            <?php endif; ?>
                                        <?php elseif($field->type === 'file'): ?>
                                            <input type="file" class="w-full mt-2 p-2 border rounded dark:bg-gray-700 dark:border-gray-600" <?php echo e($field->required ? 'required' : ''); ?>>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                        <div class="mt-6 flex justify-between">
                            <button
                                x-show="step > 1"
                                @click="step--"
                                type="button"
                                class="px-4 py-2 bg-gray-300 text-gray-800 rounded hover:bg-gray-400 dark:bg-gray-600 dark:text-gray-200 dark:hover:bg-gray-500"
                            >
                                Previous
                            </button>
                            <button
                                x-show="step < totalSteps"
                                @click="step++"
                                type="button"
                                class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 dark:bg-blue-600 dark:hover:bg-blue-700"
                            >
                                Next
                            </button>
                            <button
                                x-show="step === totalSteps"
                                type="submit"
                                class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600 dark:bg-green-600 dark:hover:bg-green-700"
                                disabled
                            >
                                Submit
                            </button>
                        </div>
                    </form>
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
<?php /**PATH C:\Users\phpar\Documents\GitHub\submission-platform\resources\views/forms/preview.blade.php ENDPATH**/ ?>