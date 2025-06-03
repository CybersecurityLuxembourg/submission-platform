<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <!-- Styles -->
        @livewireStyles
    </head>
    <body class="font-sans antialiased">

        <x-banner />

        <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @if (isset($header))
                <header class="bg-white dark:bg-gray-800 shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endif
            @if (session('success'))
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="mt-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg shadow-sm flex items-center justify-between">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span> {{ session('success') }}</span>
                        </div>
                        <button onclick="this.parentElement.remove()" class="text-green-700 hover:text-green-900">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                            </svg>
                        </button>
                    </div>
                </div>
            @elseif (session('error'))
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="mt-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg shadow-sm flex items-center justify-between">
                        <div class="flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-ban-fill" viewBox="0 0 16 16">
                                <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0M2.71 12.584q.328.378.706.707l9.875-9.875a7 7 0 0 0-.707-.707l-9.875 9.875Z"/>
                            </svg>
                            <span> {{ session('error') }}</span>
                        </div>
                        <button onclick="this.parentElement.remove()" class="text-red-700 hover:text-red-900">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                            </svg>
                        </button>
                    </div>
                </div>
            @endif

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>
        </div>

        @stack('modals')

        @livewireScripts

        <!-- Footer -->
        <footer class="bg-white dark:bg-gray-800 shadow-inner mt-auto py-6 border-t border-gray-200 dark:border-gray-700">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col lg:flex-row justify-between items-center space-y-4 lg:space-y-0">
                    <!-- Left section: Copyright and EU Logo -->
                    <div class="flex flex-col sm:flex-row items-center space-y-3 sm:space-y-0 sm:space-x-6 text-center sm:text-left">
                        <div class="text-sm text-gray-600 dark:text-gray-400">
                            &copy; {{ date('Y') }} Luxembourg House of Cybersecurity. All rights reserved.
                        </div>
                        <div class="flex flex-col items-center justify-center space-y-1">
                            <img 
                                src="{{ asset('img/co-funded-eu-logo.png') }}" 
                                alt="Co-funded by the European Union" 
                                class="h-12 w-auto opacity-95 hover:opacity-100 transition-all duration-200 eu-logo"
                                loading="lazy"
                                title="This project is co-funded by the European Union's Digital Europe Programme"
                            />
                            <div class="text-xs text-gray-500 dark:text-gray-100 text-center max-w-xs">
                                This project is co-funded by the European Union's Digital Europe Programme under Grant Agreement No. 1011227115.
                            </div>
                        </div>
                    </div>
                    
                    <!-- Right section: Links -->
                    <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-6 text-sm">
                        <a href="{{ route('terms.show') }}" class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 transition-colors duration-200 text-center sm:text-left">
                            Terms of Service
                        </a>
                        <a href="{{ route('policy.show') }}" class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 transition-colors duration-200 text-center sm:text-left">
                            Privacy Policy
                        </a>
                    </div>
                </div>
            </div>
        </footer>
    </body>
</html>


