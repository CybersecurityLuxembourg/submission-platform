<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Submit Form') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h1 class="text-2xl font-semibold mb-6 text-gray-900 dark:text-gray-100">{{ $form->title }}</h1>

                    @if($form->description)
                        <div class="mb-6 text-gray-600 dark:text-gray-400">
                            {!! \App\Helpers\MarkdownHelper::toHtml($form->description) !!}
                        </div>
                    @endif
                    @livewire('submission-form', ['form' => $form])

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
