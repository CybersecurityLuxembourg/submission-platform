<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Available Forms
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    @if($forms->isEmpty())
                        <p>No forms are currently available.</p>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            @foreach($forms as $form)
                                <div class="bg-white dark:bg-gray-700 rounded-lg shadow-md p-6">
                                    <h3 class="text-lg font-semibold mb-2">{{ $form->title }}</h3>
                                    <p class="text-gray-600 dark:text-gray-300 mb-4">{{ Str::limit($form->description, 100) }}</p>
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm text-gray-500 dark:text-gray-400">
                                            Created {{ $form->created_at->diffForHumans() }}
                                        </span>

                                        @if($form->visibility === 'public')
                                            {{-- Public forms are always accessible with a link --}}
                                            <a href="{{ route('submissions.create', $form) }}"
                                               class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 transition duration-200">
                                                View Form
                                            </a>
                                        @elseif($form->visibility === 'authenticated')
                                            @auth
                                                {{-- Authenticated users can access these forms --}}
                                                <a href="{{ route('submissions.create', $form) }}"
                                                   class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 transition duration-200">
                                                    View Form
                                                </a>
                                            @else
                                                {{-- Show login required button for guests --}}
                                                <a href="{{ route('login') }}"
                                                   class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400 transition duration-200">
                                                    Login Required
                                                </a>
                                            @endauth
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-6">
                            {{ $forms->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
