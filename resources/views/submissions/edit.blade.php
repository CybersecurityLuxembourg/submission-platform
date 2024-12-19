<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Edit Submission') }}
            </h2>
            <span class="px-3 py-1 text-sm rounded-full
                @if($submission->status === 'draft') bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300
                @elseif($submission->status === 'ongoing') bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200
                @elseif($submission->status === 'submitted') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                @endif">
                {{ ucfirst($submission->status) }}
            </span>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">

                    @if($submission->status === 'submitted')
                        <div class="mb-6 p-4 bg-yellow-50 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200 rounded">
                            <strong>Note:</strong> This submission has already been submitted. Any changes will be logged.
                        </div>
                    @endif

                    <livewire:submission-form
                        :form="$submission->form"
                        :submission="$submission"
                        :edit-mode="true" />
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
