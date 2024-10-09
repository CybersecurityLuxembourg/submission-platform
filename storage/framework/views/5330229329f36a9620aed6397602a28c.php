<nav class="bg-white shadow">
    <div class="container mx-auto px-6 py-3">
        <div class="flex items-center justify-between">
            <div>
                <a href="<?php echo e(route('forms.index')); ?>" class="text-xl font-bold text-gray-800"><?php echo e(config('app.name', 'Form Builder')); ?></a>
            </div>
            <div>
                <?php if(auth()->guard()->check()): ?>
                <a href="<?php echo e(route('forms.index')); ?>" class="text-gray-800 mx-2">My Forms</a>
                <a href="<?php echo e(route('logout')); ?>" class="text-gray-800 mx-2"
                   onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                    Logout
                </a>
                <form id="logout-form" action="<?php echo e(route('logout')); ?>" method="POST" class="hidden">
                    <?php echo csrf_field(); ?>
                </form>
                <?php else: ?>
                <a href="<?php echo e(route('login')); ?>" class="text-gray-800 mx-2">Login</a>
                <a href="<?php echo e(route('register')); ?>" class="text-gray-800 mx-2">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>
<?php /**PATH C:\Users\phpar\Documents\GitHub\submission-platform\resources\views/layouts/navigation.blade.php ENDPATH**/ ?>