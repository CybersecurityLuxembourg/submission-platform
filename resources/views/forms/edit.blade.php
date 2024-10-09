<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Edit Form
        </h2>
    </x-slot>
    <div class="bg-white shadow rounded-lg p-6">
        <form action="{{ route('forms.update', $form) }}" method="POST">
            @csrf
            @method('PUT')

            <!-- Form Details -->
            <div class="mb-6">
                <div class="mb-4">
                    <label class="block text-gray-700">Title</label>
                    <input type="text" name="title" value="{{ old('title', $form->title) }}" class="w-full mt-2 p-2 border rounded" required>
                    @error('title')
                    <span class="text-red-600 text-sm">{{ $message }}</span>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700">Description</label>
                    <textarea name="description" class="w-full mt-2 p-2 border rounded">{{ old('description', $form->description) }}</textarea>
                    @error('description')
                    <span class="text-red-600 text-sm">{{ $message }}</span>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700">Status</label>
                    <select name="status" class="w-full mt-2 p-2 border rounded">
                        <option value="draft" {{ $form->status === 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="published" {{ $form->status === 'published' ? 'selected' : '' }}>Published</option>
                        <option value="archived" {{ $form->status === 'archived' ? 'selected' : '' }}>Archived</option>
                    </select>
                    @error('status')
                    <span class="text-red-600 text-sm">{{ $message }}</span>
                    @enderror
                </div>

                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded">Update Form</button>
            </div>
        </form>

        <!-- Form Fields -->
        <h2 class="text-xl font-semibold mb-4">Form Fields</h2>
        <table class="min-w-full mb-4">
            <thead>
            <tr>
                <th class="text-left py-2">Label</th>
                <th class="text-left py-2">Type</th>
                <th class="text-left py-2">Required</th>
                <th class="text-left py-2">Actions</th>
            </tr>
            </thead>
            <tbody>
            @foreach($form->fields as $field)
                <tr>
                    <td class="py-2">{{ $field->label }}</td>
                    <td class="py-2">{{ ucfirst($field->type) }}</td>
                    <td class="py-2">{{ $field->required ? 'Yes' : 'No' }}</td>
                    <td class="py-2">
                        <!-- Add edit and delete options for fields -->
                        <!-- This would require additional routes and methods -->
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>

        <!-- Add New Field -->
        <h3 class="text-lg font-semibold mb-2">Add New Field</h3>
        <form action="{{ route('form_fields.store', $form) }}" method="POST">
            @csrf

            <div class="mb-4">
                <label class="block text-gray-700">Label</label>
                <input type="text" name="label" value="{{ old('label') }}" class="w-full mt-2 p-2 border rounded" required>
                @error('label')
                <span class="text-red-600 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <div class="mb-4">
                <label class="block text-gray-700">Type</label>
                <select name="type" class="w-full mt-2 p-2 border rounded">
                    <option value="text">Text</option>
                    <option value="textarea">Textarea</option>
                    <option value="select">Select</option>
                    <option value="checkbox">Checkbox</option>
                    <option value="radio">Radio</option>
                </select>
                @error('type')
                <span class="text-red-600 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <!-- Options for select, checkbox, radio types -->
            <div class="mb-4">
                <label class="block text-gray-700">Options (comma-separated)</label>
                <input type="text" name="options" value="{{ old('options') }}" class="w-full mt-2 p-2 border rounded">
                @error('options')
                <span class="text-red-600 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <div class="mb-4 flex items-center">
                <input type="checkbox" name="required" value="1" class="mr-2" {{ old('required') ? 'checked' : '' }}>
                <label class="text-gray-700">Required</label>
            </div>

            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">Add Field</button>
        </form>
    </div>
</x-app-layout>
