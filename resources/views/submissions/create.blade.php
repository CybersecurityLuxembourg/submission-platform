<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Submission Create') }}
        </h2>
    </x-slot>
    <h1 class="text-2xl font-semibold mb-6">{{ $form->title }}</h1>

    <div class="bg-white shadow rounded-lg p-6">
        <p class="mb-4">{{ $form->description }}</p>

        <form action="{{ route('submissions.store', $form) }}" method="POST">
            @csrf
            @foreach($form->fields as $field)
                <div class="mb-4">
                    <label class="block text-gray-700">{{ $field->label }}{{ $field->required ? '*' : '' }}</label>

                    @if($field->type === 'text')
                        <input type="text" name="field_{{ $field->id }}" class="w-full mt-2 p-2 border rounded" {{ $field->required ? 'required' : '' }}>
                    @elseif($field->type === 'textarea')
                        <textarea name="field_{{ $field->id }}" class="w-full mt-2 p-2 border rounded" {{ $field->required ? 'required' : '' }}></textarea>
                    @elseif(in_array($field->type, ['select', 'checkbox', 'radio']))
                        @php
                            $options = explode(',', $field->options);
                        @endphp

                        @if($field->type === 'select')
                            <select name="field_{{ $field->id }}" class="w-full mt-2 p-2 border rounded" {{ $field->required ? 'required' : '' }}>
                                @foreach($options as $option)
                                    <option value="{{ trim($option) }}">{{ trim($option) }}</option>
                                @endforeach
                            </select>
                        @else
                            @foreach($options as $option)
                                <div class="flex items-center mt-2">
                                    <input type="{{ $field->type }}" name="field_{{ $field->id }}{{ $field->type === 'checkbox' ? '[]' : '' }}" value="{{ trim($option) }}" class="mr-2">
                                    <label>{{ trim($option) }}</label>
                                </div>
                            @endforeach
                        @endif
                    @endif

                    @error('field_' . $field->id)
                    <span class="text-red-600 text-sm">{{ $message }}</span>
                    @enderror
                </div>
            @endforeach

            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">Submit</button>
        </form>
    </div>
</x-app-layout>
