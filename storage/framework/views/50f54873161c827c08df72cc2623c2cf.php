<?php use App\Models\User; ?>
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

    <!-- Container -->
    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <!-- Form Details -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6 mb-8">
            <form action="<?php echo e(route('forms.update', $form)); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <?php echo method_field('PUT'); ?>

                <!-- Title Field -->
                <div class="mb-6">
                    <label class="block text-gray-700 dark:text-gray-300 font-medium mb-2">
                        Title
                    </label>
                    <input type="text" name="title" value="<?php echo e(old('title', $form->title)); ?>"
                           class="w-full mt-1 p-3 border border-gray-300 dark:border-gray-700 rounded-md bg-gray-50 dark:bg-gray-700
                        text-gray-900 dark:text-gray-100 focus:ring-blue-500 focus:border-blue-500"
                           required>
                    <?php $__errorArgs = ['title'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <span class="text-red-600 dark:text-red-400 text-sm"><?php echo e($message); ?></span>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                <!-- Description Field -->
                <div class="mb-6">
                    <label class="block text-gray-700 dark:text-gray-300 font-medium mb-2">
                        Description
                    </label>
                    <textarea name="description"
                              class="w-full mt-1 p-3 border border-gray-300 dark:border-gray-700 rounded-md bg-gray-50 dark:bg-gray-700
                        text-gray-900 dark:text-gray-100 focus:ring-blue-500 focus:border-blue-500"
                              rows="4"><?php echo e(old('description', $form->description)); ?></textarea>
                    <?php $__errorArgs = ['description'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <span class="text-red-600 dark:text-red-400 text-sm"><?php echo e($message); ?></span>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                <!-- Status Field -->
                <div class="mb-6">
                    <label class="block text-gray-700 dark:text-gray-300 font-medium mb-2">
                        Status
                    </label>
                    <select name="status"
                            class="w-full mt-1 p-3 border border-gray-300 dark:border-gray-700 rounded-md bg-gray-50 dark:bg-gray-700
                        text-gray-900 dark:text-gray-100 focus:ring-blue-500 focus:border-blue-500">
                        <option value="draft" <?php echo e($form->status === 'draft' ? 'selected' : ''); ?>>Draft</option>
                        <option value="published" <?php echo e($form->status === 'published' ? 'selected' : ''); ?>>Published
                        </option>
                        <option value="archived" <?php echo e($form->status === 'archived' ? 'selected' : ''); ?>>Archived</option>
                    </select>
                    <?php $__errorArgs = ['status'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <span class="text-red-600 dark:text-red-400 text-sm"><?php echo e($message); ?></span>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
                <div class="mb-6">
                    <label class="block text-gray-700 dark:text-gray-300 font-medium mb-2">
                        Visibility
                    </label>
                    <select name="visibility"
                            class="w-full mt-1 p-3 border border-gray-300 dark:border-gray-700 rounded-md bg-gray-50 dark:bg-gray-700
                text-gray-900 dark:text-gray-100 focus:ring-blue-500 focus:border-blue-500">
                        <option value="public" <?php echo e($form->visibility === 'public' ? 'selected' : ''); ?>>Public</option>
                        <option value="authenticated" <?php echo e($form->visibility === 'authenticated' ? 'selected' : ''); ?>>
                            Authenticated Users Only
                        </option>
                        <option value="private" <?php echo e($form->visibility === 'private' ? 'selected' : ''); ?>>Private</option>
                    </select>
                    <?php $__errorArgs = ['visibility'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <span class="text-red-600 dark:text-red-400 text-sm"><?php echo e($message); ?></span>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>


                <!-- Submit Button -->
                <div class="flex justify-end">
                    <button type="submit"
                            class="px-6 py-2 bg-green-600 hover:bg-green-700 text-white rounded-md transition duration-200">
                        Update Form
                    </button>
                </div>
            </form>
        </div>
        <div class="mt-8 bg-white dark:bg-gray-800 shadow rounded-lg p-6">
            <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-gray-100">Assign Users</h3>

            <!-- Currently Assigned Users -->
            <?php if($form->appointedUsers->isNotEmpty()): ?>
                <div class="mb-6">
                    <h4 class="text-md font-medium mb-2 text-gray-700 dark:text-gray-300">Currently Assigned Users</h4>
                    <div class="bg-gray-50 dark:bg-gray-700 rounded-md p-4">
                        <ul class="divide-y divide-gray-200 dark:divide-gray-600">
                            <?php $__currentLoopData = $form->appointedUsers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $assignedUser): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <li class="py-2 flex justify-between items-center">
                                    <div>
                                        <span class="text-gray-900 dark:text-gray-100"><?php echo e($assignedUser->name); ?></span>
                                        <span class="text-gray-500 dark:text-gray-400 text-sm ml-2">(<?php echo e($assignedUser->email); ?>)</span>
                                    </div>
                                    <div class="flex items-center">
                                <span class="text-sm <?php echo e($assignedUser->pivot->can_edit ? 'text-green-600 dark:text-green-400' : 'text-gray-500 dark:text-gray-400'); ?> mr-4">
                                    <?php echo e($assignedUser->pivot->can_edit ? 'Can Edit' : 'View Only'); ?>

                                </span>
                                        <form action="<?php echo e(route('forms.remove-user', [$form, $assignedUser])); ?>" method="POST" class="inline">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('DELETE'); ?>
                                            <button type="submit"
                                                    onclick="return confirm('Are you sure you want to remove this user?')"
                                                    class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-200">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </ul>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Assign New Users Form -->
            <form action="<?php echo e(route('forms.assign-users', $form)); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <div class="mb-4">
                    <label class="block text-gray-700 dark:text-gray-300 font-medium mb-2">Select Users</label>
                    <select name="user_ids[]" multiple
                            class="w-full mt-1 p-2 border rounded-md bg-white dark:bg-gray-700 border-gray-300 dark:border-gray-600 text-gray-900 dark:text-gray-100 focus:ring-blue-500 focus:border-blue-500">
                        <?php $__currentLoopData = User::whereIn('role', ['internal_evaluator', 'external_evaluator'])
                                    ->where('id', '!=', auth()->id())
                                    ->orderBy('name')
                                    ->get(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($user->id); ?>"
                                <?php echo e($form->appointedUsers->contains($user->id) ? 'selected' : ''); ?>>
                                <?php echo e($user->name); ?> (<?php echo e($user->email); ?>) - <?php echo e(ucfirst(str_replace('_', ' ', $user->role))); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="mb-4">
                    <label class="inline-flex items-center">
                        <input type="checkbox" name="can_edit" value="1"
                               class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                        <span class="ml-2 text-gray-700 dark:text-gray-300">Allow Editing</span>
                    </label>
                </div>
                <button type="submit"
                        class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50">
                    Assign Users
                </button>
            </form>
        </div>

        <div class="mt-8 mb-8  bg-white dark:bg-gray-800 shadow rounded-lg p-6">
            <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-gray-100">Access Links (Work in Progress not working)</h3>
            <form action="<?php echo e(route('forms.create-access-link', $form)); ?>" method="POST" class="mb-6">
                <?php echo csrf_field(); ?>
                <div class="mb-4">
                    <label class="block text-gray-700 dark:text-gray-300 font-medium mb-2">
                        Expiration Date (optional)
                    </label>
                    <input type="datetime-local" name="expires_at" disabled
                           class="w-full mt-1 p-2 border rounded-md bg-white dark:bg-gray-700 border-gray-300 dark:border-gray-600 text-gray-900 dark:text-gray-100 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <button type="submit" disabled
                        class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-opacity-50">
                    Create Access Link
                </button>
            </form>

            <?php if($form->accessLinks->isNotEmpty()): ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Access Link
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Expires At
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        <?php $__currentLoopData = $form->accessLinks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $link): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <input type="text" value="<?php echo e(route('form.access', $link->token)); ?>"
                                               class="flex-1 p-1 text-sm border rounded mr-2 bg-gray-50 dark:bg-gray-700"
                                               readonly>
                                        <button
                                            onclick="navigator.clipboard.writeText('<?php echo e(route('form.access', $link->token)); ?>')"
                                            class="px-2 py-1 text-sm bg-blue-500 text-white rounded hover:bg-blue-600">
                                            Copy
                                        </button>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-gray-700 dark:text-gray-300">
                                    <?php echo e($link->expires_at ? $link->expires_at->format('Y-m-d H:i') : 'Never'); ?>

                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <form action="<?php echo e(route('forms.delete-access-link', $link)); ?>" method="POST"
                                          class="inline">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('DELETE'); ?>
                                        <button type="submit"
                                                class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300 focus:outline-none">
                                            Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-gray-500 dark:text-gray-400 text-center py-4">No access links created yet.</p>
            <?php endif; ?>
        </div>
        <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('form-field-manager', ['form' => $form]);

$__html = app('livewire')->mount($__name, $__params, 'lw-4054841429-0', $__slots ?? [], get_defined_vars());

echo $__html;

unset($__html);
unset($__name);
unset($__params);
unset($__split);
if (isset($__slots)) unset($__slots);
?>

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