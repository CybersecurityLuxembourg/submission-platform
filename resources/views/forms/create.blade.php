<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Create New Form
        </h2>
    </x-slot>


    <div class="bg-white shadow rounded-lg p-6">
        <form action="{{ route('forms.store') }}" method="POST">
            @csrf

            <div class="mb-4">
                <label class="block text-gray-700">Title</label>
                <input type="text" name="title" value="{{ old('title') }}" class="w-full mt-2 p-2 border rounded" required>
                @error('title')
                <span class="text-red-600 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <div class="mb-4">
                <label class="block text-gray-700">Description</label>
                <textarea name="description" class="w-full mt-2 p-2 border rounded">{{ old('description') }}</textarea>
                @error('description')
                <span class="text-red-600 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">Create Form</button>
        </form>
    </div>
</x-app-layout>
