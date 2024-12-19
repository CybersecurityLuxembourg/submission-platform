<div>
    <div class="mb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <!-- Search -->
        <div class="flex-1 min-w-0">
            <div class="relative rounded-md shadow-sm">
                <input type="text"
                       wire:model.debounce.300ms="search"
                       class="block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 pr-10 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                       placeholder="Search submissions...">
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                    <svg class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Status Filter -->
        <div class="flex-shrink-0">
            <select wire:model="statusFilter"
                    class="rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm focus:ring-blue-500 focus:border-blue-500">
                <option value="all">All Submissions</option>
                <option value="draft">Drafts</option>
                <option value="ongoing">In Progress</option>
                <option value="submitted">Submitted</option>
                <option value="under_review">Under Review</option>
                <option value="completed">Completed</option>
            </select>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider cursor-pointer" wire:click="sortBy('id')">
                        ID
                        @if ($sortField === 'id')
                            <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                        @endif
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                        Submitted By
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider cursor-pointer" wire:click="sortBy('status')">
                        Status
                        @if ($sortField === 'status')
                            <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                        @endif
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider cursor-pointer" wire:click="sortBy('last_activity')">
                        Last Activity
                        @if ($sortField === 'last_activity')
                            <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                        @endif
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                        Actions
                    </th>
                </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                @forelse($submissions as $submission)
                    <tr wire:key="submission-{{ $submission->id }}">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                            {{ $submission->id }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            {{ $submission->user ? $submission->user->name : 'Anonymous' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs rounded-full
                                    @if($submission->status === 'draft') bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300
                                    @elseif($submission->status === 'ongoing') bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200
                                    @elseif($submission->status === 'submitted') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                                    @elseif($submission->status === 'under_review') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                                    @elseif($submission->status === 'completed') bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200
                                    @endif">
                                    {{ ucfirst($submission->status) }}
                                </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            {{ $submission->last_activity ? $submission->last_activity->diffForHumans() : $submission->updated_at->diffForHumans() }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                            @if(!in_array($submission->status, ['draft', 'ongoing']))
                                <a href="{{ route('submissions.show', ['form' => $form, 'submission' => $submission]) }}"
                                   class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">
                                    View
                                </a>

                                @if($submission->status !== 'draft')
                                    @can('export', $submission)
                                        <a href="{{ route('submissions.export.single.pdf', ['form' => $submission->form, 'submission' => $submission]) }}"
                                           class="text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300">
                                            Export PDF
                                        </a>
                                    @endcan
                                @endif
                            @endif

                            @if($submission->status === 'under_review' && auth()->user()->can('review', $submission))
                                <a href="{{ route('submissions.review', ['form' => $form, 'submission' => $submission]) }}"
                                   class="text-yellow-600 hover:text-yellow-900 dark:text-yellow-400 dark:hover:text-yellow-300">
                                    Review
                                </a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                            No submissions found.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-6 py-4">
            {{ $submissions->links() }}
        </div>
    </div>
</div>
