<!-- Hero Section -->
<div class="relative overflow-hidden">
    <!-- Background gradient and pattern -->
    <div class="absolute inset-0 bg-gradient-to-br from-indigo-600 via-purple-600 to-indigo-800 dark:from-indigo-900 dark:via-purple-900 dark:to-gray-900"></div>
    <div class="absolute inset-0 bg-[url('data:image/svg+xml,%3Csvg width=%2260%22 height=%2260%22 viewBox=%220 0 60 60%22 xmlns=%22http://www.w3.org/2000/svg%22%3E%3Cg fill=%22none%22 fill-rule=%22evenodd%22%3E%3Cg fill=%22%23ffffff%22 fill-opacity=%220.05%22%3E%3Cpath d=%22M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z%22/%3E%3C/g%3E%3C/g%3E%3C/svg%3E')] opacity-40"></div>
    
    <div class="relative p-8 lg:p-12">
        <div class="flex flex-col lg:flex-row items-center lg:items-start gap-8">
            <!-- Logo and Text Content -->
            <div class="flex-1 text-center lg:text-left">
                <div class="inline-flex items-center justify-center lg:justify-start mb-6">
                    <div class="p-3 bg-white/10 backdrop-blur-sm rounded-2xl ring-1 ring-white/20">
                        <x-application-logo class="block h-14 w-auto" />
                    </div>
                </div>
                
                <h1 class="text-3xl lg:text-4xl xl:text-5xl font-bold text-white leading-tight">
                    Welcome to the <span class="text-transparent bg-clip-text bg-gradient-to-r from-cyan-300 to-blue-300">NC3</span> Submission Platform
                </h1>
                
                <p class="mt-6 text-lg text-indigo-100 dark:text-indigo-200 leading-relaxed max-w-3xl">
                    Your secure gateway to the National Cybersecurity Competence Center's. Submit to forms through our streamlined and secure platform.
                </p>
                
        
            </div>
            
        </div>
    </div>
</div>

<!-- Available Forms Section -->
<div class="bg-gray-50 dark:bg-gray-900/50 p-6 lg:p-10">
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center">
            <svg class="w-6 h-6 mr-3 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            Available Applications
        </h2>
        <p class="mt-2 text-gray-600 dark:text-gray-400">Select a form below to start your application</p>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
        @forelse($forms ?? [] as $form)
        <div class="group relative bg-white dark:bg-gray-800 rounded-2xl shadow-sm hover:shadow-xl transition-all duration-300 overflow-hidden border border-gray-200 dark:border-gray-700 hover:border-indigo-300 dark:hover:border-indigo-600">
            <!-- Card accent top border -->
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-indigo-500 via-purple-500 to-indigo-600"></div>
            
            <div class="p-6">
                <div class="flex items-start justify-between mb-4">
                    <div class="p-3 bg-indigo-100 dark:bg-indigo-900/50 rounded-xl group-hover:bg-indigo-200 dark:group-hover:bg-indigo-800/50 transition-colors duration-300">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" class="w-6 h-6 stroke-indigo-600 dark:stroke-indigo-400">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                        </svg>
                    </div>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-400">
                        Open
                    </span>
                </div>
                
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors duration-300">
                    {{ $form->title }}
                </h3>
                
                <p class="text-gray-600 dark:text-gray-400 text-sm leading-relaxed mb-6 line-clamp-3">
                    {{ $form->description }}
                </p>
                
                <a href="{{ route('submissions.create', $form) }}" 
                   class="inline-flex items-center justify-center w-full px-4 py-3 text-sm font-semibold text-white bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 rounded-xl transition-all duration-300 shadow-sm hover:shadow-md group-hover:shadow-indigo-500/25">
                    Start Application
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" class="ms-2 w-5 h-5 fill-current transition-transform duration-300 group-hover:translate-x-1">
                        <path fill-rule="evenodd" d="M5 10a.75.75 0 01.75-.75h6.638L10.23 7.29a.75.75 0 111.04-1.08l3.5 3.25a.75.75 0 010 1.08l-3.5 3.25a.75.75 0 11-1.04-1.08l2.158-1.96H5.75A.75.75 0 015 10z" clip-rule="evenodd" />
                    </svg>
                </a>
            </div>
        </div>
        @empty
        <!-- Empty state -->
        <div class="col-span-full">
            <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-12 text-center">
                <div class="mx-auto w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mb-6">
                    <svg class="w-8 h-8 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">
                    No Forms Available
                </h3>
                <p class="text-gray-500 dark:text-gray-400 max-w-md mx-auto">
                    There are currently no application forms available. Please check back later or contact support for more information.
                </p>
            </div>
        </div>
        @endforelse
    </div>
</div>
