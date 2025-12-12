<nav x-data="{ open: false, dropdownOpen: false }"
     class="bg-gradient-to-r from-slate-900 via-slate-800 to-slate-900 dark:from-slate-950 dark:via-slate-900 dark:to-slate-950 border-b border-slate-700/50 shadow-lg shadow-slate-900/20 sticky top-0 z-50 backdrop-blur-lg">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ url('/') }}" class="flex items-center space-x-3 group">
                        <div class="p-1.5 bg-teal-500/10 rounded-lg ring-1 ring-teal-500/20 group-hover:ring-teal-400/40 transition-all duration-300">
                            <x-application-logo class="block h-8 w-auto"/>
                        </div>
                    </a>
                </div>
                <!-- Navigation Links -->
                <div class="hidden space-x-1 lg:flex lg:ml-8">
                    <a href="{{ url('/') }}" class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-lg transition-all duration-200 {{ request()->is('/') ? 'text-white bg-teal-500/20 ring-1 ring-teal-500/30' : 'text-slate-300 hover:text-white hover:bg-white/5' }}">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                        {{ __('Home') }}
                    </a>
                    <a href="{{ route('forms.public_index') }}" class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-lg transition-all duration-200 {{ request()->routeIs('forms.public_index') ? 'text-white bg-teal-500/20 ring-1 ring-teal-500/30' : 'text-slate-300 hover:text-white hover:bg-white/5' }}">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        {{ __('Forms') }}
                    </a>
                    @auth
                        <a href="{{ route('dashboard') }}" class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-lg transition-all duration-200 {{ request()->routeIs('dashboard') ? 'text-white bg-teal-500/20 ring-1 ring-teal-500/30' : 'text-slate-300 hover:text-white hover:bg-white/5' }}">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                            </svg>
                            {{ __('Dashboard') }}
                        </a>
                        @if(auth()->user()->role === 'internal_evaluator' || auth()->user()->isAdmin() || auth()->user()->role === 'external_evaluator')
                        <a href="{{ route('forms.user_index') }}" class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-lg transition-all duration-200 {{ request()->routeIs('forms.user_index') ? 'text-white bg-teal-500/20 ring-1 ring-teal-500/30' : 'text-slate-300 hover:text-white hover:bg-white/5' }}">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                            </svg>
                            {{ __('My Forms') }}
                        </a>
                        @endif
                        @if(auth()->user()->isAdmin())
                        <a href="{{ url('/admin') }}" class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-lg transition-all duration-200 {{ request()->is('admin*') ? 'text-white bg-teal-500/20 ring-1 ring-teal-500/30' : 'text-slate-300 hover:text-white hover:bg-white/5' }}">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            {{ __('Admin') }}
                        </a>
                        @endif
                        <a href="{{ route('submissions.user') }}" class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-lg transition-all duration-200 {{ request()->routeIs('submissions.user') ? 'text-white bg-teal-500/20 ring-1 ring-teal-500/30' : 'text-slate-300 hover:text-white hover:bg-white/5' }}">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                            </svg>
                            {{ __('Submissions') }}
                        </a>
                    @endauth
                </div>
            </div>

            <!-- Settings Dropdown for authenticated users -->
            @auth
                <div class="hidden lg:flex lg:items-center lg:ml-6 lg:space-x-3">
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center px-4 py-2 text-sm font-medium text-slate-300 hover:text-white bg-white/5 hover:bg-white/10 rounded-lg ring-1 ring-white/10 hover:ring-white/20 transition-all duration-200">
                                <div class="w-7 h-7 rounded-full bg-gradient-to-br from-teal-400 to-cyan-500 flex items-center justify-center mr-2 text-xs font-bold text-slate-900">
                                    {{ substr(Auth::user()->name, 0, 1) }}
                                </div>
                                {{ Auth::user()->name }}
                                <svg class="ml-2 -mr-0.5 h-4 w-4 text-slate-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                </svg>
                            </button>
                        </x-slot>
                        <x-slot name="content">
                            <x-dropdown-link :href="route('profile.show')">
                                {{ __('Profile') }}
                            </x-dropdown-link>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-dropdown-link :href="route('logout')"
                                                 onclick="event.preventDefault(); this.closest('form').submit();">
                                    {{ __('Log Out') }}
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                </div>
            @else
                <!-- Login/Register for non-authenticated users -->
                <div class="hidden lg:flex lg:items-center lg:space-x-3">
                    <a href="{{ route('login') }}" class="inline-flex items-center px-4 py-2 text-sm font-medium text-slate-300 hover:text-white transition-colors duration-200">
                        {{ __('Login') }}
                    </a>
                    <a href="{{ route('register') }}" class="inline-flex items-center px-4 py-2 text-sm font-semibold text-slate-900 bg-gradient-to-r from-teal-400 to-cyan-400 hover:from-teal-300 hover:to-cyan-300 rounded-lg shadow-lg shadow-teal-500/20 hover:shadow-teal-500/30 transition-all duration-200">
                        {{ __('Register') }}
                    </a>
                </div>
            @endauth

            <!-- Hamburger -->
            <div class="flex items-center lg:hidden">
                <button @click="open = !open"
                        class="inline-flex items-center justify-center p-2 rounded-lg text-slate-400 hover:text-white hover:bg-white/10 focus:outline-none focus:ring-2 focus:ring-teal-500/50 transition duration-200">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': !open }" class="inline-flex"
                              stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M4 6h16M4 12h16M4 18h16"/>
                        <path :class="{'hidden': !open, 'inline-flex': open }" class="hidden" stroke-linecap="round"
                              stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': !open}" class="hidden lg:hidden bg-slate-800/95 backdrop-blur-lg border-t border-slate-700/50">
        <div class="pt-2 pb-3 space-y-1 px-3">
            <a href="{{ url('/') }}" class="flex items-center px-4 py-3 text-base font-medium rounded-lg transition-all duration-200 {{ request()->is('/') ? 'text-white bg-teal-500/20 ring-1 ring-teal-500/30' : 'text-slate-300 hover:text-white hover:bg-white/5' }}">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                {{ __('Home') }}
            </a>
            <a href="{{ route('forms.public_index') }}" class="flex items-center px-4 py-3 text-base font-medium rounded-lg transition-all duration-200 {{ request()->routeIs('forms.public_index') ? 'text-white bg-teal-500/20 ring-1 ring-teal-500/30' : 'text-slate-300 hover:text-white hover:bg-white/5' }}">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                {{ __('Available Forms') }}
            </a>
            @auth
                <a href="{{ route('dashboard') }}" class="flex items-center px-4 py-3 text-base font-medium rounded-lg transition-all duration-200 {{ request()->routeIs('dashboard') ? 'text-white bg-teal-500/20 ring-1 ring-teal-500/30' : 'text-slate-300 hover:text-white hover:bg-white/5' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                    </svg>
                    {{ __('Dashboard') }}
                </a>
                @if(auth()->user()->role === 'internal_evaluator' || auth()->user()->isAdmin() || auth()->user()->role === 'external_evaluator')
                    <a href="{{ route('forms.user_index') }}" class="flex items-center px-4 py-3 text-base font-medium rounded-lg transition-all duration-200 {{ request()->routeIs('forms.user_index') ? 'text-white bg-teal-500/20 ring-1 ring-teal-500/30' : 'text-slate-300 hover:text-white hover:bg-white/5' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                        </svg>
                        {{ __('My Forms') }}
                    </a>
                @endif
                @if(auth()->user()->isAdmin())
                    <a href="{{ url('/admin') }}" class="flex items-center px-4 py-3 text-base font-medium rounded-lg transition-all duration-200 {{ request()->is('admin*') ? 'text-white bg-teal-500/20 ring-1 ring-teal-500/30' : 'text-slate-300 hover:text-white hover:bg-white/5' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        {{ __('Admin') }}
                    </a>
                @endif
                <a href="{{ route('submissions.user') }}" class="flex items-center px-4 py-3 text-base font-medium rounded-lg transition-all duration-200 {{ request()->routeIs('submissions.user') ? 'text-white bg-teal-500/20 ring-1 ring-teal-500/30' : 'text-slate-300 hover:text-white hover:bg-white/5' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                    </svg>
                    {{ __('My Submissions') }}
                </a>
            @endauth
        </div>

        @auth
            <!-- Responsive Settings Options -->
            <div class="pt-4 pb-3 border-t border-slate-700/50">
                <div class="px-4 flex items-center">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-teal-400 to-cyan-500 flex items-center justify-center mr-3 text-sm font-bold text-slate-900">
                        {{ substr(Auth::user()->name, 0, 1) }}
                    </div>
                    <div>
                        <div class="font-medium text-base text-white">{{ Auth::user()->name }}</div>
                        <div class="font-medium text-sm text-slate-400">{{ Auth::user()->email }}</div>
                    </div>
                </div>

                <div class="mt-3 space-y-1 px-3">
                    <a href="{{ route('profile.show') }}" class="flex items-center px-4 py-3 text-base font-medium text-slate-300 hover:text-white hover:bg-white/5 rounded-lg transition-all duration-200">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        {{ __('Profile') }}
                    </a>

                    <!-- Authentication -->
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full flex items-center px-4 py-3 text-base font-medium text-red-400 hover:text-red-300 hover:bg-red-500/10 rounded-lg transition-all duration-200">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                            {{ __('Log Out') }}
                        </button>
                    </form>
                </div>
            </div>
        @else
            <!-- Login/Register for mobile -->
            <div class="pt-4 pb-3 border-t border-slate-700/50 px-3 space-y-2">
                <a href="{{ route('login') }}" class="flex items-center justify-center px-4 py-3 text-base font-medium text-slate-300 hover:text-white hover:bg-white/5 rounded-lg transition-all duration-200">
                    {{ __('Login') }}
                </a>
                <a href="{{ route('register') }}" class="flex items-center justify-center px-4 py-3 text-base font-semibold text-slate-900 bg-gradient-to-r from-teal-400 to-cyan-400 hover:from-teal-300 hover:to-cyan-300 rounded-lg transition-all duration-200">
                    {{ __('Register') }}
                </a>
            </div>
        @endauth
    </div>
</nav>
