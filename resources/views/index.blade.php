<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            National CyberSecurity Center Luxembourg Submission Platform
        </h2>
    </x-slot>

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
</x-app-layout>
