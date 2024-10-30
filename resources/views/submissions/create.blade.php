<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Submit Form') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h1 class="text-2xl font-semibold mb-6">{{ $form->title }}</h1>

                    @if($form->description)
                        <p class="mb-6 text-gray-600 dark:text-gray-400">{{ $form->description }}</p>
                    @endif

                    <form action="{{ route('submissions.store', $form) }}" method="POST" enctype="multipart/form-data"
                          x-data="{
                            step: 1,
                            totalSteps: {{ $form->categories->count() }},
                            percentageComplete: 0,
                            updatePercentage() {
                                this.percentageComplete = (this.step / this.totalSteps) * 100;
                            }
                          }"
                          x-init="updatePercentage"
                          @step-changed="updatePercentage"
                    >
                        @csrf

                        <!-- Progress bar -->
                        <div class="mb-8">
                            <div class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700 mb-2">
                                <div class="bg-blue-600 h-2.5 rounded-full"
                                     x-bind:style="`width: ${percentageComplete}%`"></div>
                            </div>
                            <div class="text-sm text-gray-600 dark:text-gray-400">
                                Progress: <span x-text="`${Math.round(percentageComplete)}%`"></span>
                            </div>
                        </div>

                        @foreach($form->categories as $index => $category)
                            <div x-show="step === {{ $index + 1 }}"
                                 x-transition:enter="transition ease-out duration-300"
                                 x-transition:enter-start="opacity-0 transform scale-90"
                                 x-transition:enter-end="opacity-100 transform scale-100">
                                <h3 class="text-lg font-semibold mb-4">{{ $category->name }}</h3>
                                <p class="mb-6 text-gray-600 dark:text-gray-400">{{ $category->description }}</p>

                                @foreach($category->fields as $field)
                                    <div class="mb-6">
                                        @if($field->type === 'header')
                                            <h4 class="text-lg font-medium text-gray-900 dark:text-gray-100 mt-4 mb-2">{{ $field->content }}</h4>
                                        @elseif($field->type === 'description')
                                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ $field->content }}</p>
                                        @else
                                            <label for="field_{{ $field->id }}"
                                                   class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                                {{ $field->label }}
                                                @if($field->required)
                                                    <span class="text-red-500">*</span>
                                                @endif
                                            </label>
                                        @endif
                                        @if($field->type === 'text')
                                            <input type="text"
                                                   name="field_{{ $field->id }}"
                                                   id="field_{{ $field->id }}"
                                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                                {{ $field->required ? 'required' : '' }}
                                                {{ $field->char_limit ? 'maxlength='.$field->char_limit : '' }}>
                                            @if($field->char_limit)
                                                <div class="mt-1 text-sm text-gray-500">
                                                    Maximum characters: {{ $field->char_limit }}
                                                </div>
                                            @endif
                                        @elseif($field->type === 'textarea')
                                            <textarea name="field_{{ $field->id }}"
                                                      id="field_{{ $field->id }}"
                                                      rows="3"
                                                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
              {{ $field->required ? 'required' : '' }}
                                                {{ $field->char_limit ? 'maxlength='.$field->char_limit : '' }}></textarea>
                                            @if($field->char_limit)
                                                <div class="mt-1 text-sm text-gray-500">
                                                    Maximum characters: {{ $field->char_limit }}
                                                </div>
                                            @endif
                                        @elseif($field->type === 'select')
                                            <select name="field_{{ $field->id }}" id="field_{{ $field->id }}"
                                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                                {{ $field->required ? 'required' : '' }}>
                                                <option value="">Select an option</option>
                                                @foreach(explode(',', $field->options) as $option)
                                                    <option value="{{ trim($option) }}">{{ trim($option) }}</option>
                                                @endforeach
                                            </select>
                                        @elseif(in_array($field->type, ['checkbox', 'radio']))
                                            <div class="mt-2 space-y-2">
                                                @foreach(explode(',', $field->options) as $option)
                                                    <div class="flex items-center">
                                                        <input type="{{ $field->type }}"
                                                               id="field_{{ $field->id }}_{{ $loop->index }}"
                                                               name="field_{{ $field->id }}{{ $field->type === 'checkbox' ? '[]' : '' }}"
                                                               value="{{ trim($option) }}"
                                                               class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 dark:border-gray-600"
                                                            {{ $field->required && $field->type === 'radio' ? 'required' : '' }}>
                                                        <label for="field_{{ $field->id }}_{{ $loop->index }}"
                                                               class="ml-3 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                                            {{ trim($option) }}
                                                        </label>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @elseif($field->type === 'file')
                                            <input type="file" name="field_{{ $field->id }}" id="field_{{ $field->id }}"
                                                   class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 dark:text-gray-400 dark:file:bg-gray-700 dark:file:text-gray-300"
                                                   {{ $field->required ? 'required' : '' }}
                                                   accept=".jpg,.jpeg,.png,.pdf,.doc,.docx,.xls,.xlsx">
                                        @elseif($field->type === 'header')
                                            <h4 class="text-lg font-medium text-gray-900 dark:text-gray-100 mt-4 mb-2">{{ $field->label }}</h4>
                                        @elseif($field->type === 'description')
                                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ $field->label }}</p>
                                        @endif

                                        @error('field_' . $field->id)
                                        <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
                                        @enderror
                                    </div>
                                @endforeach
                            </div>
                        @endforeach

                        <div class="mt-8 flex justify-between">
                            <button
                                x-show="step > 1"
                                @click.prevent="step--; $dispatch('step-changed')"
                                type="button"
                                class="px-4 py-2 bg-gray-300 text-gray-800 rounded hover:bg-gray-400 dark:bg-gray-600 dark:text-gray-200 dark:hover:bg-gray-500"
                            >
                                Previous
                            </button>
                            <button
                                x-show="step < totalSteps"
                                @click.prevent="step++; $dispatch('step-changed')"
                                type="button"
                                class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 dark:bg-blue-600 dark:hover:bg-blue-700"
                            >
                                Next
                            </button>
                            <button
                                x-show="step === totalSteps"
                                type="submit"
                                class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600 dark:bg-green-600 dark:hover:bg-green-700"
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
