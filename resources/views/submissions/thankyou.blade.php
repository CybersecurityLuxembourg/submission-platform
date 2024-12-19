<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Thank You!
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-8 text-center">
                    <!-- Decorative Element -->
                    <div class="mb-6">
                        <svg class="w-16 h-16 mx-auto text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>

                    <!-- Main Message -->
                    <h3 class="text-3xl font-bold text-gray-900 dark:text-gray-100 mb-4">
                        Thank You for Your Submission!
                    </h3>


                    <!-- Decorative Divider -->
                    <div class="flex items-center justify-center mb-8">
                        <div class="h-0.5 w-16 bg-gray-300 dark:bg-gray-600"></div>
                        <div class="mx-4">
                            <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <div class="h-0.5 w-16 bg-gray-300 dark:bg-gray-600"></div>
                    </div>

                    <!-- Return Link -->
                    <a href="/" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 transition duration-200 inline-flex items-center">
                        Return Home
                        <svg class="ml-2 -mr-1 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
