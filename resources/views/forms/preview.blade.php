<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ $form->title }} - Preview
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <p class="mb-4">{{ $form->description }}</p>

                    <form x-data="{ step: 1, totalSteps: {{ $form->categories->count() }} }">


                        @foreach($form->categories as $index => $category)
                            <div x-show="step === {{ $index + 1 }}">
                                <h3 class="text-lg font-semibold mb-4">{{ $category->name }}</h3>
                                <p class="mb-4">{{ $category->description }}</p>

                                @foreach($category->fields as $field)
                                    <div class="mb-4">
                                        <label class="block text-gray-700 dark:text-gray-300">{{ $field->label }}{{ $field->required ? '*' : '' }}</label>

                                        @if($field->type === 'text')
                                            <input type="text" class="w-full mt-2 p-2 border rounded dark:bg-gray-700 dark:border-gray-600" {{ $field->required ? 'required' : '' }}>
                                        @elseif($field->type === 'textarea')
                                            <textarea class="w-full mt-2 p-2 border rounded dark:bg-gray-700 dark:border-gray-600" {{ $field->required ? 'required' : '' }}></textarea>
                                        @elseif(in_array($field->type, ['select', 'checkbox', 'radio']))
                                            @php
                                                $options = explode(',', $field->options);
                                            @endphp

                                            @if($field->type === 'select')
                                                <select class="w-full mt-2 p-2 border rounded dark:bg-gray-700 dark:border-gray-600" {{ $field->required ? 'required' : '' }}>
                                                    @foreach($options as $option)
                                                        <option value="{{ trim($option) }}">{{ trim($option) }}</option>
                                                    @endforeach
                                                </select>
                                            @else
                                                @foreach($options as $option)
                                                    <div class="flex items-center mt-2">
                                                        <input type="{{ $field->type }}" name="field_{{ $field->id }}" value="{{ trim($option) }}" class="mr-2">
                                                        <label>{{ trim($option) }}</label>
                                                    </div>
                                                @endforeach
                                            @endif
                                        @elseif($field->type === 'file')
                                            <input type="file" class="w-full mt-2 p-2 border rounded dark:bg-gray-700 dark:border-gray-600" {{ $field->required ? 'required' : '' }}>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endforeach

                        <div class="mt-6 flex justify-between">
                            <button
                                x-show="step > 1"
                                @click="step--"
                                type="button"
                                class="px-4 py-2 bg-gray-300 text-gray-800 rounded hover:bg-gray-400 dark:bg-gray-600 dark:text-gray-200 dark:hover:bg-gray-500"
                            >
                                Previous
                            </button>
                            <button
                                x-show="step < totalSteps"
                                @click="step++"
                                type="button"
                                class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 dark:bg-blue-600 dark:hover:bg-blue-700"
                            >
                                Next
                            </button>
                            <button
                                x-show="step === totalSteps"
                                type="submit"
                                class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600 dark:bg-green-600 dark:hover:bg-green-700"
                                disabled
                            >
                                Submit
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
