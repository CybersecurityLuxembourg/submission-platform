@props(['formStats'])

<div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
    <div class="p-6 text-gray-900 dark:text-gray-100">
        @if($formStats->isEmpty())
            <p class="text-gray-600 dark:text-gray-300">You don't have any forms yet.</p>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($formStats as $stat)
                    <div class="bg-white dark:bg-gray-700 rounded-lg shadow-md p-6">
                        <div class="flex justify-between items-start mb-4">
                            <h3 class="text-lg font-semibold">{{ $stat['form']->title }}</h3>
                            @if($stat['form']->user_id === Auth::id())
                                <span class="px-2 py-1 text-xs font-semibold bg-blue-100 text-blue-800 rounded-full">Owner</span>
                            @else
                                <span class="px-2 py-1 text-xs font-semibold bg-green-100 text-green-800 rounded-full">Assignee</span>
                            @endif
                        </div>
                        
                        <p class="text-gray-600 dark:text-gray-300 mb-4">{{ Str::limit($stat['form']->description, 100) }}</p>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <x-statistics-card label="Draft Applications" :value="$stat['draft_count']" />
                            <x-statistics-card label="Submitted Applications" :value="$stat['submitted_count']" />
                        </div>

                        <div class="mt-4 flex justify-end space-x-2">
                            <a href="{{ route('submissions.index', $stat['form']) }}" 
                               class="text-sm text-blue-600 dark:text-blue-400 hover:underline">
                                View Submissions
                            </a>
                            @if($stat['form']->user_id === Auth::id())
                                <span class="text-gray-300 dark:text-gray-500">|</span>
                                <a href="{{ route('forms.edit', $stat['form']) }}" 
                                   class="text-sm text-blue-600 dark:text-blue-400 hover:underline">
                                    Edit Form
                                </a>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div> 