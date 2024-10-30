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
            <?php echo e(__('Submit Form')); ?>

        </h2>
     <?php $__env->endSlot(); ?>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h1 class="text-2xl font-semibold mb-6"><?php echo e($form->title); ?></h1>

                    <?php if($form->description): ?>
                        <p class="mb-6 text-gray-600 dark:text-gray-400"><?php echo e($form->description); ?></p>
                    <?php endif; ?>

                    <form action="<?php echo e(route('submissions.store', $form)); ?>" method="POST" enctype="multipart/form-data"
                          x-data="{
                            step: 1,
                            totalSteps: <?php echo e($form->categories->count()); ?>,
                            percentageComplete: 0,
                            updatePercentage() {
                                this.percentageComplete = (this.step / this.totalSteps) * 100;
                            }
                          }"
                          x-init="updatePercentage"
                          @step-changed="updatePercentage"
                    >
                        <?php echo csrf_field(); ?>

                        <!-- Progress bar -->
                        <div class="mb-8">
                            <div class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700 mb-2">
                                <div class="bg-blue-600 h-2.5 rounded-full"
                                     x-bind:style="`width: ${percentageComplete}%`"></div>
                            </div>
                            <div class="text-sm text-gray-600 dark:text-gray-400">
                                Progress: <span x-text="`${Math.round(percentageComplete)}%`"></span>
                            </div>
                        </div>

                        <?php $__currentLoopData = $form->categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div x-show="step === <?php echo e($index + 1); ?>"
                                 x-transition:enter="transition ease-out duration-300"
                                 x-transition:enter-start="opacity-0 transform scale-90"
                                 x-transition:enter-end="opacity-100 transform scale-100">
                                <h3 class="text-lg font-semibold mb-4"><?php echo e($category->name); ?></h3>
                                <p class="mb-6 text-gray-600 dark:text-gray-400"><?php echo e($category->description); ?></p>

                                <?php $__currentLoopData = $category->fields; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $field): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="mb-6">
                                        <?php if($field->type === 'header'): ?>
                                            <h4 class="text-lg font-medium text-gray-900 dark:text-gray-100 mt-4 mb-2"><?php echo e($field->content); ?></h4>
                                        <?php elseif($field->type === 'description'): ?>
                                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400"><?php echo e($field->content); ?></p>
                                        <?php else: ?>
                                            <label for="field_<?php echo e($field->id); ?>"
                                                   class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                                <?php echo e($field->label); ?>

                                                <?php if($field->required): ?>
                                                    <span class="text-red-500">*</span>
                                                <?php endif; ?>
                                            </label>
                                        <?php endif; ?>
                                        <?php if($field->type === 'text'): ?>
                                            <input type="text"
                                                   name="field_<?php echo e($field->id); ?>"
                                                   id="field_<?php echo e($field->id); ?>"
                                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                                <?php echo e($field->required ? 'required' : ''); ?>

                                                <?php echo e($field->char_limit ? 'maxlength='.$field->char_limit : ''); ?>>
                                            <?php if($field->char_limit): ?>
                                                <div class="mt-1 text-sm text-gray-500">
                                                    Maximum characters: <?php echo e($field->char_limit); ?>

                                                </div>
                                            <?php endif; ?>
                                        <?php elseif($field->type === 'textarea'): ?>
                                            <textarea name="field_<?php echo e($field->id); ?>"
                                                      id="field_<?php echo e($field->id); ?>"
                                                      rows="3"
                                                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
              <?php echo e($field->required ? 'required' : ''); ?>

                                                <?php echo e($field->char_limit ? 'maxlength='.$field->char_limit : ''); ?>></textarea>
                                            <?php if($field->char_limit): ?>
                                                <div class="mt-1 text-sm text-gray-500">
                                                    Maximum characters: <?php echo e($field->char_limit); ?>

                                                </div>
                                            <?php endif; ?>
                                        <?php elseif($field->type === 'select'): ?>
                                            <select name="field_<?php echo e($field->id); ?>" id="field_<?php echo e($field->id); ?>"
                                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                                <?php echo e($field->required ? 'required' : ''); ?>>
                                                <option value="">Select an option</option>
                                                <?php $__currentLoopData = explode(',', $field->options); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <option value="<?php echo e(trim($option)); ?>"><?php echo e(trim($option)); ?></option>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </select>
                                        <?php elseif(in_array($field->type, ['checkbox', 'radio'])): ?>
                                            <div class="mt-2 space-y-2">
                                                <?php $__currentLoopData = explode(',', $field->options); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <div class="flex items-center">
                                                        <input type="<?php echo e($field->type); ?>"
                                                               id="field_<?php echo e($field->id); ?>_<?php echo e($loop->index); ?>"
                                                               name="field_<?php echo e($field->id); ?><?php echo e($field->type === 'checkbox' ? '[]' : ''); ?>"
                                                               value="<?php echo e(trim($option)); ?>"
                                                               class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 dark:border-gray-600"
                                                            <?php echo e($field->required && $field->type === 'radio' ? 'required' : ''); ?>>
                                                        <label for="field_<?php echo e($field->id); ?>_<?php echo e($loop->index); ?>"
                                                               class="ml-3 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                                            <?php echo e(trim($option)); ?>

                                                        </label>
                                                    </div>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </div>
                                        <?php elseif($field->type === 'file'): ?>
                                            <input type="file" name="field_<?php echo e($field->id); ?>" id="field_<?php echo e($field->id); ?>"
                                                   class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 dark:text-gray-400 dark:file:bg-gray-700 dark:file:text-gray-300"
                                                   <?php echo e($field->required ? 'required' : ''); ?>

                                                   accept=".jpg,.jpeg,.png,.pdf,.doc,.docx,.xls,.xlsx">
                                        <?php elseif($field->type === 'header'): ?>
                                            <h4 class="text-lg font-medium text-gray-900 dark:text-gray-100 mt-4 mb-2"><?php echo e($field->label); ?></h4>
                                        <?php elseif($field->type === 'description'): ?>
                                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400"><?php echo e($field->label); ?></p>
                                        <?php endif; ?>

                                        <?php $__errorArgs = ['field_' . $field->id];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <p class="mt-2 text-sm text-red-600 dark:text-red-500"><?php echo e($message); ?></p>
                                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                        <div class="mt-8 flex justify-between">
                            <button
                                x-show="step > 1"
                                @click.prevent="step--; $dispatch('step-changed')"
                                type="button"
                                class="px-4 py-2 bg-gray-300 text-gray-800 rounded hover:bg-gray-400 dark:bg-gray-600 dark:text-gray-200 dark:hover:bg-gray-500"
                            >
                                Previous
                            </button>
                            <button
                                x-show="step < totalSteps"
                                @click.prevent="step++; $dispatch('step-changed')"
                                type="button"
                                class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 dark:bg-blue-600 dark:hover:bg-blue-700"
                            >
                                Next
                            </button>
                            <button
                                x-show="step === totalSteps"
                                type="submit"
                                class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600 dark:bg-green-600 dark:hover:bg-green-700"
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
<?php /**PATH C:\Users\phpar\Documents\GitHub\submission-platform\resources\views/submissions/create.blade.php ENDPATH**/ ?>