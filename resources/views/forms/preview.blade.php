<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ $form->title }} - Preview
        </h2>
    </x-slot>

    <div class="bg-white shadow rounded-lg p-6">
        <p class="mb-4">{{ $form->description }}</p>

        <form>
            @foreach($form->fields as $field)
                <div class="mb-4">
                    <label class="block text-gray-700">{{ $field->label }}{{ $field->required ? '*' : '' }}</label>

                    @if($field->type === 'text')
                        <input type="text" class="w-full mt-2 p-2 border rounded" {{ $field->required ? 'required' : '' }}>
                    @elseif($field->type === 'textarea')
                        <textarea class="w-full mt-2 p-2 border rounded" {{ $field->required ? 'required' : '' }}></textarea>
                    @elseif(in_array($field->type, ['select', 'checkbox', 'radio']))
                        @php
                            $options = explode(',', $field->options);
                        @endphp

                        @if($field->type === 'select')
                            <select class="w-full mt-2 p-2 border rounded" {{ $field->required ? 'required' : '' }}>
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
                    @endif
                </div>
            @endforeach

            <button type="submit" class="px-4 py-2 bg-gray-600 text-white rounded" disabled>Submit</button>
        </form>
    </div>
</x-app-layout>>
