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

    <div class="bg-white shadow rounded-lg p-6">
        <p class="mb-4"><?php echo e($form->description); ?></p>

        <form>
            <?php $__currentLoopData = $form->fields; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $field): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="mb-4">
                    <label class="block text-gray-700"><?php echo e($field->label); ?><?php echo e($field->required ? '*' : ''); ?></label>

                    <?php if($field->type === 'text'): ?>
                        <input type="text" class="w-full mt-2 p-2 border rounded" <?php echo e($field->required ? 'required' : ''); ?>>
                    <?php elseif($field->type === 'textarea'): ?>
                        <textarea class="w-full mt-2 p-2 border rounded" <?php echo e($field->required ? 'required' : ''); ?>></textarea>
                    <?php elseif(in_array($field->type, ['select', 'checkbox', 'radio'])): ?>
                        <?php
                            $options = explode(',', $field->options);
                        ?>

                        <?php if($field->type === 'select'): ?>
                            <select class="w-full mt-2 p-2 border rounded" <?php echo e($field->required ? 'required' : ''); ?>>
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
                    <?php endif; ?>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

            <button type="submit" class="px-4 py-2 bg-gray-600 text-white rounded" disabled>Submit</button>
        </form>
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
<?php endif; ?>>
<?php /**PATH C:\Users\phpar\Documents\GitHub\submission-platform\resources\views/forms/preview.blade.php ENDPATH**/ ?>