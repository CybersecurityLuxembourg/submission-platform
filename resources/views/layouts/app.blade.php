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

        <!-- Footer - NC3 Teal Theme -->
        <footer class="relative bg-gradient-to-b from-slate-50 to-slate-100 dark:from-slate-900 dark:to-slate-950 mt-auto border-t border-slate-200 dark:border-slate-800">
            <!-- Decorative top border - NC3 teal gradient -->
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-teal-500 via-cyan-500 to-teal-400"></div>
            
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Main Footer Content -->
                <div class="py-12 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 lg:gap-12">
                    <!-- Brand Section -->
                    <div class="lg:col-span-2">
                        <div class="flex items-center space-x-3 mb-4">
                            <div class="p-2 bg-teal-100 dark:bg-teal-900/50 rounded-xl">
                                <svg class="w-8 h-8 text-teal-600 dark:text-teal-400" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-slate-900 dark:text-white">NC3 Luxembourg</h3>
                                <p class="text-sm text-slate-500 dark:text-slate-400">National Cybersecurity Competence Center</p>
                            </div>
                        </div>
                        <p class="text-slate-600 dark:text-slate-400 text-sm leading-relaxed max-w-md mb-6">
                            Luxembourg House of Cybersecurity is committed to strengthening cybersecurity capabilities across Luxembourg through the NC3 initiative.
                        </p>
                        
                        <!-- EU Funding Badge -->
                        <div class="inline-flex items-center p-4 bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700">
                            <img 
                                src="{{ asset('img/co-funded-eu-logo.png') }}" 
                                alt="Co-funded by the European Union" 
                                class="h-14 w-auto eu-logo"
                                loading="lazy"
                                title="This project is co-funded by the European Union's Digital Europe Programme"
                            />
                            <div class="ml-4 text-xs text-slate-500 dark:text-slate-400 max-w-[200px] leading-relaxed">
                                Co-funded by the EU Digital Europe Programme<br/>
                                <span class="text-slate-400 dark:text-slate-500">Grant No. 1011227115</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Quick Links -->
                    <div>
                        <h4 class="text-sm font-semibold text-slate-900 dark:text-white uppercase tracking-wider mb-4">
                            Quick Links
                        </h4>
                        <ul class="space-y-3">
                            <li>
                                <a href="{{ url('/') }}" class="group flex items-center text-slate-600 dark:text-slate-400 hover:text-teal-600 dark:hover:text-teal-400 transition-colors duration-200 text-sm">
                                    <svg class="w-4 h-4 mr-2 text-slate-400 group-hover:text-teal-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                                    </svg>
                                    Home
                                </a>
                            </li>
                            @auth
                            <li>
                                <a href="{{ route('dashboard') }}" class="group flex items-center text-slate-600 dark:text-slate-400 hover:text-teal-600 dark:hover:text-teal-400 transition-colors duration-200 text-sm">
                                    <svg class="w-4 h-4 mr-2 text-slate-400 group-hover:text-teal-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                                    </svg>
                                    Dashboard
                                </a>
                            </li>
                            @endauth
                            <li>
                                <a href="https://nc3.lu" target="_blank" class="group flex items-center text-slate-600 dark:text-slate-400 hover:text-teal-600 dark:hover:text-teal-400 transition-colors duration-200 text-sm">
                                    <svg class="w-4 h-4 mr-2 text-slate-400 group-hover:text-teal-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                    </svg>
                                    NC3 Website
                                </a>
                            </li>
                        </ul>
                    </div>
                    
                    <!-- Legal Links -->
                    <div>
                        <h4 class="text-sm font-semibold text-slate-900 dark:text-white uppercase tracking-wider mb-4">
                            Legal
                        </h4>
                        <ul class="space-y-3">
                            <li>
                                <a href="{{ route('terms.show') }}" class="group flex items-center text-slate-600 dark:text-slate-400 hover:text-teal-600 dark:hover:text-teal-400 transition-colors duration-200 text-sm">
                                    <svg class="w-4 h-4 mr-2 text-slate-400 group-hover:text-teal-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    Terms of Service
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('policy.show') }}" class="group flex items-center text-slate-600 dark:text-slate-400 hover:text-teal-600 dark:hover:text-teal-400 transition-colors duration-200 text-sm">
                                    <svg class="w-4 h-4 mr-2 text-slate-400 group-hover:text-teal-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                    </svg>
                                    Privacy Policy
                                </a>
                            </li>
                            <li>
                                <button 
                                    onclick="localStorage.removeItem('cookie_consent_acknowledged'); location.reload();" 
                                    class="group flex items-center text-slate-600 dark:text-slate-400 hover:text-teal-600 dark:hover:text-teal-400 transition-colors duration-200 text-sm"
                                >
                                    <svg class="w-4 h-4 mr-2 text-slate-400 group-hover:text-teal-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    Cookie Settings
                                </button>
                            </li>
                        </ul>
                    </div>
                </div>
                
                <!-- Bottom Bar -->
                <div class="py-6 border-t border-slate-200 dark:border-slate-800">
                    <div class="flex flex-col sm:flex-row justify-between items-center space-y-4 sm:space-y-0">
                        <p class="text-sm text-slate-500 dark:text-slate-400">
                            &copy; {{ date('Y') }} <span class="font-medium text-slate-700 dark:text-slate-300">Luxembourg House of Cybersecurity</span>. All rights reserved.
                        </p>
                    </div>
                </div>
            </div>
        </footer>

        {{-- Cookie Consent Banner --}}
        <x-cookie-consent />
    </body>
</html>