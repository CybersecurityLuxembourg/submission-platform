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
            National CyberSecurity Center Luxembourg Submission Platform
        </h2>
     <?php $__env->endSlot(); ?>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h1 class="text-3xl font-bold mb-6">Welcome to Your Submission Platform</h1>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="bg-blue-100 dark:bg-blue-900 p-6 rounded-lg shadow">
                            <h2 class="text-xl font-semibold mb-4">Create New Form</h2>
                            <p class="mb-4">Start building your custom form with our easy-to-use form builder.</p>
                            <a href="#" class="inline-block bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded">
                                Get Started
                            </a>
                        </div>

                        <div class="bg-green-100 dark:bg-green-900 p-6 rounded-lg shadow">
                            <h2 class="text-xl font-semibold mb-4">Manage Submissions</h2>
                            <p class="mb-4">View and manage all your form submissions in one place.</p>
                            <a href="#" class="inline-block bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded">
                                View Submissions
                            </a>
                        </div>
                    </div>

                    <div class="mt-8">
                        <h2 class="text-2xl font-semibold mb-4">Recent Activity</h2>
                        <ul class="bg-gray-100 dark:bg-gray-700 rounded-lg shadow">
                            <li class="border-b border-gray-200 dark:border-gray-600 p-4">
                                <span class="font-semibold">Contact Form:</span> 5 new submissions
                            </li>
                            <li class="border-b border-gray-200 dark:border-gray-600 p-4">
                                <span class="font-semibold">Feedback Survey:</span> 12 responses received
                            </li>
                            <li class="p-4">
                                <span class="font-semibold">Event Registration:</span> 8 new registrations
                            </li>
                        </ul>
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
<?php /**PATH C:\Users\phpar\Documents\GitHub\submission-platform\resources\views/index.blade.php ENDPATH**/ ?>