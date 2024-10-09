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
            Edit Form
        </h2>
     <?php $__env->endSlot(); ?>
    <div class="bg-white shadow rounded-lg p-6">
        <form action="<?php echo e(route('forms.update', $form)); ?>" method="POST">
            <?php echo csrf_field(); ?>
            <?php echo method_field('PUT'); ?>

            <!-- Form Details -->
            <div class="mb-6">
                <div class="mb-4">
                    <label class="block text-gray-700">Title</label>
                    <input type="text" name="title" value="<?php echo e(old('title', $form->title)); ?>" class="w-full mt-2 p-2 border rounded" required>
                    <?php $__errorArgs = ['title'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <span class="text-red-600 text-sm"><?php echo e($message); ?></span>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700">Description</label>
                    <textarea name="description" class="w-full mt-2 p-2 border rounded"><?php echo e(old('description', $form->description)); ?></textarea>
                    <?php $__errorArgs = ['description'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <span class="text-red-600 text-sm"><?php echo e($message); ?></span>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700">Status</label>
                    <select name="status" class="w-full mt-2 p-2 border rounded">
                        <option value="draft" <?php echo e($form->status === 'draft' ? 'selected' : ''); ?>>Draft</option>
                        <option value="published" <?php echo e($form->status === 'published' ? 'selected' : ''); ?>>Published</option>
                        <option value="archived" <?php echo e($form->status === 'archived' ? 'selected' : ''); ?>>Archived</option>
                    </select>
                    <?php $__errorArgs = ['status'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <span class="text-red-600 text-sm"><?php echo e($message); ?></span>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded">Update Form</button>
            </div>
        </form>

        <!-- Form Fields -->
        <h2 class="text-xl font-semibold mb-4">Form Fields</h2>
        <table class="min-w-full mb-4">
            <thead>
            <tr>
                <th class="text-left py-2">Label</th>
                <th class="text-left py-2">Type</th>
                <th class="text-left py-2">Required</th>
                <th class="text-left py-2">Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php $__currentLoopData = $form->fields; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $field): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td class="py-2"><?php echo e($field->label); ?></td>
                    <td class="py-2"><?php echo e(ucfirst($field->type)); ?></td>
                    <td class="py-2"><?php echo e($field->required ? 'Yes' : 'No'); ?></td>
                    <td class="py-2">
                        <!-- Add edit and delete options for fields -->
                        <!-- This would require additional routes and methods -->
                    </td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>

        <!-- Add New Field -->
        <h3 class="text-lg font-semibold mb-2">Add New Field</h3>
        <form action="<?php echo e(route('form_fields.store', $form)); ?>" method="POST">
            <?php echo csrf_field(); ?>

            <div class="mb-4">
                <label class="block text-gray-700">Label</label>
                <input type="text" name="label" value="<?php echo e(old('label')); ?>" class="w-full mt-2 p-2 border rounded" required>
                <?php $__errorArgs = ['label'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                <span class="text-red-600 text-sm"><?php echo e($message); ?></span>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <div class="mb-4">
                <label class="block text-gray-700">Type</label>
                <select name="type" class="w-full mt-2 p-2 border rounded">
                    <option value="text">Text</option>
                    <option value="textarea">Textarea</option>
                    <option value="select">Select</option>
                    <option value="checkbox">Checkbox</option>
                    <option value="radio">Radio</option>
                </select>
                <?php $__errorArgs = ['type'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                <span class="text-red-600 text-sm"><?php echo e($message); ?></span>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <!-- Options for select, checkbox, radio types -->
            <div class="mb-4">
                <label class="block text-gray-700">Options (comma-separated)</label>
                <input type="text" name="options" value="<?php echo e(old('options')); ?>" class="w-full mt-2 p-2 border rounded">
                <?php $__errorArgs = ['options'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                <span class="text-red-600 text-sm"><?php echo e($message); ?></span>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <div class="mb-4 flex items-center">
                <input type="checkbox" name="required" value="1" class="mr-2" <?php echo e(old('required') ? 'checked' : ''); ?>>
                <label class="text-gray-700">Required</label>
            </div>

            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">Add Field</button>
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
<?php endif; ?>
<?php /**PATH C:\Users\phpar\Documents\GitHub\submission-platform\resources\views/forms/edit.blade.php ENDPATH**/ ?>