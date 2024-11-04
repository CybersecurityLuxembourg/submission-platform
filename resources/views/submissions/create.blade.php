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

                    <div x-data="{
                            step: 1,
                            totalSteps: {{ $progressData['totalSteps'] }},
                            isMobile: window.innerWidth < 640,
                            steps: @js($progressData['steps']),
                            formValidation: false,
                            currentStep() {
                                return this.steps[this.step - 1];
                            },
                            isStepComplete(stepNumber) {
                                return this.step > stepNumber;
                            },
                            isCurrentStep(stepNumber) {
                                return this.step === stepNumber;
                            },
                            nextStep() {
                                if (this.step < this.totalSteps) {
                                    this.step++;
                                    window.scrollTo({ top: 0, behavior: 'smooth' });
                                }
                            },
                            previousStep() {
                                if (this.step > 1) {
                                    this.step--;
                                    window.scrollTo({ top: 0, behavior: 'smooth' });
                                }
                            }
                        }"
                         @resize.window="isMobile = window.innerWidth < 640"
                         @keydown.enter.prevent="nextStep()"
                    >

                        <!-- Modern Progress Navigation -->
                        <div class="mb-8">
                            <!-- Current Progress Header -->
                            <div class="relative mb-8">
                                <div class="flex items-center justify-between mb-2">
                                    <div class="flex items-center gap-3">
                                        <h2 class="text-lg sm:text-xl font-medium text-gray-900 dark:text-gray-100">
                                            <span x-text="currentStep().name"></span>
                                        </h2>
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                    <span x-text="step"></span>/<span x-text="totalSteps"></span>
                </span>
                                    </div>

                                    <!-- Previous/Next Quick Nav -->
                                    <div class="flex items-center gap-2">
                                        <button
                                            @click="previousStep()"
                                            x-show="step > 1"
                                            class="p-1 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-200"
                                            aria-label="Previous step">
                                            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor"
                                                 viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M15 19l-7-7 7-7"/>
                                            </svg>
                                        </button>
                                        <button
                                            @click="nextStep()"
                                            x-show="step < totalSteps"
                                            class="p-1 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-200"
                                            aria-label="Next step">
                                            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor"
                                                 viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M9 5l7 7-7 7"/>
                                            </svg>
                                        </button>
                                    </div>
                                </div>

                                <!-- Description if available -->
                                <div x-show="currentStep().description"
                                     x-transition:enter="transition ease-out duration-200"
                                     x-transition:enter-start="opacity-0 transform translate-y-1"
                                     x-transition:enter-end="opacity-100 transform translate-y-0"
                                     class="text-sm text-gray-500 dark:text-gray-400">
                                    <p x-text="currentStep().description"></p>
                                </div>
                            </div>

                            <!-- Progress Track -->
                            <div class="relative">
                                <!-- Background Track -->
                                <div class="overflow-hidden mb-8 w-full h-2 bg-gray-200 rounded-full dark:bg-gray-700">
                                    <!-- Animated Progress Bar -->
                                    <div
                                        class="h-2 rounded-full bg-gradient-to-r from-blue-500 to-blue-600 transition-all duration-500 ease-out dark:from-blue-600 dark:to-blue-700"
                                        :style="{ width: ((step - 1) / (totalSteps - 1) * 100) + '%' }">
                                    </div>
                                </div>

                                <!-- Step Indicators -->
                                <div class="absolute -top-2 w-full">
                                    <div class="relative flex justify-between">
                                        <template x-for="(stepItem, index) in steps" :key="index">
                                            <div class="relative flex flex-col items-center group">
                                                <!-- Step Button -->
                                                <button
                                                    @click="isStepComplete(index) ? $data.step = index + 1 : null"
                                                    :disabled="!isStepComplete(index)"
                                                    :class="{
                                'w-6 h-6 rounded-full transition-all duration-300 ease-in-out flex items-center justify-center': true,
                                'bg-blue-600 hover:bg-blue-700': isStepComplete(index + 1),
                                'ring-4 ring-white dark:ring-gray-800 bg-blue-600 scale-110': isCurrentStep(index + 1),
                                'bg-gray-300 dark:bg-gray-600': !isStepComplete(index + 1) && !isCurrentStep(index + 1),
                                'cursor-pointer': isStepComplete(index),
                                'cursor-default': !isStepComplete(index)
                            }"
                                                >
                                                    <!-- Step Number or Check Mark -->
                                                    <span :class="{
                                'text-xs font-medium': true,
                                'text-white': isStepComplete(index + 1) || isCurrentStep(index + 1),
                                'text-gray-600 dark:text-gray-300': !isStepComplete(index + 1) && !isCurrentStep(index + 1)
                            }"
                                                          x-text="index + 1"></span>
                                                </button>

                                                <!-- Step Label - Only visible on larger screens or current step -->


                                                <!-- Tooltip for mobile -->
                                                <div x-show="isMobile && !isCurrentStep(index + 1)"
                                                     class="absolute bottom-full mb-2 -translate-x-1/2 translate-y-3 left-1/2 opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none">
                                                    <div
                                                        class="bg-gray-900 text-white text-xs px-2 py-1 rounded shadow-lg">
                                                        <span x-text="stepItem.name"></span>
                                                    </div>
                                                    <div
                                                        class="w-2 h-2 bg-gray-900 transform rotate-45 translate-y-1 ml-[calc(50%-4px)]"></div>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </div>


                        <form action="{{ route('submissions.store', $form) }}" method="POST"
                              enctype="multipart/form-data" @submit="formValidation = true">
                            @csrf
                            @foreach($form->categories as $index => $category)
                                <div x-show.transition.opacity="step === {{ $index + 1 }}"
                                     class="space-y-6 border-b border-gray-200 dark:border-gray-700 pb-8 mb-8">

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

                                                @if($field->help_text)
                                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 mb-2">
                                                        {{ $field->help_text }}
                                                    </p>
                                                @endif

                                                @if($field->type === 'text')
                                                    <input type="text"
                                                           name="field_{{ $field->id }}"
                                                           id="field_{{ $field->id }}"
                                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                                           {{ $field->required ? 'required' : '' }}
                                                           {{ $field->char_limit ? 'maxlength='.$field->char_limit : '' }}
                                                           placeholder="{{ $field->placeholder ?? '' }}"
                                                           value="{{ old('field_'.$field->id) }}">

                                                @elseif($field->type === 'textarea')
                                                    <textarea name="field_{{ $field->id }}"
                                                              id="field_{{ $field->id }}"
                                                              rows="3"
                                                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                                              {{ $field->required ? 'required' : '' }}
                                                              {{ $field->char_limit ? 'maxlength='.$field->char_limit : '' }}
                                                              placeholder="{{ $field->placeholder ?? '' }}"
                                                    >{{ old('field_'.$field->id) }}</textarea>

                                                @elseif($field->type === 'select')
                                                    <select name="field_{{ $field->id }}"
                                                            id="field_{{ $field->id }}"
                                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                                        {{ $field->required ? 'required' : '' }}>
                                                        <option value="">Select an option</option>
                                                        @foreach(explode(',', $field->options) as $option)
                                                            <option value="{{ trim($option) }}"
                                                                {{ old('field_'.$field->id) === trim($option) ? 'selected' : '' }}>
                                                                {{ trim($option) }}
                                                            </option>
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
                                                                    {{ $field->required && $field->type === 'radio' ? 'required' : '' }}
                                                                    {{ in_array(trim($option), (array)old('field_'.$field->id, [])) ? 'checked' : '' }}>
                                                                <label for="field_{{ $field->id }}_{{ $loop->index }}"
                                                                       class="ml-3 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                                                    {{ trim($option) }}
                                                                </label>
                                                            </div>
                                                        @endforeach
                                                    </div>

                                                @elseif($field->type === 'file')
                                                    <input type="file"
                                                           name="field_{{ $field->id }}"
                                                           id="field_{{ $field->id }}"
                                                           class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 dark:text-gray-400 dark:file:bg-gray-700 dark:file:text-gray-300"
                                                           {{ $field->required ? 'required' : '' }}
                                                           accept="{{ $field->allowed_types ?? '.jpg,.jpeg,.png,.pdf,.doc,.docx,.xls,.xlsx' }}">
                                                @endif

                                                @if($field->char_limit)
                                                    <div class="mt-1 text-sm text-gray-500">
                                                        Maximum characters: {{ $field->char_limit }}
                                                    </div>
                                                @endif
                                            @endif

                                            @error('field_' . $field->id)
                                            <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
                                            @enderror
                                        </div>
                                    @endforeach
                                </div>
                            @endforeach

                            <!-- Navigation buttons -->
                            <div class="flex justify-between mt-6">
                                <button type="button"
                                        @click="previousStep()"
                                        x-show="step > 1"
                                        class="bg-gray-200 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-300 transition-colors">
                                    Previous
                                </button>
                                <button type="button"
                                        @click="nextStep()"
                                        x-show="step < totalSteps"
                                        class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition-colors">
                                    Next
                                </button>
                                <button type="submit"
                                        x-show="step === totalSteps"
                                        class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 transition-colors">
                                    Submit
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

</x-app-layout>
