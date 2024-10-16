<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Edit Form
        </h2>
    </x-slot>

    <!-- Container -->
    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <!-- Form Details -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6 mb-8">
            <form action="{{ route('forms.update', $form) }}" method="POST">
                @csrf
                @method('PUT')

                <!-- Title Field -->
                <div class="mb-6">
                    <label class="block text-gray-700 dark:text-gray-300 font-medium mb-2">
                        Title
                    </label>
                    <input type="text" name="title" value="{{ old('title', $form->title) }}"
                           class="w-full mt-1 p-3 border border-gray-300 dark:border-gray-700 rounded-md bg-gray-50 dark:bg-gray-700
                        text-gray-900 dark:text-gray-100 focus:ring-blue-500 focus:border-blue-500"
                           required>
                    @error('title')
                    <span class="text-red-600 dark:text-red-400 text-sm">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Description Field -->
                <div class="mb-6">
                    <label class="block text-gray-700 dark:text-gray-300 font-medium mb-2">
                        Description
                    </label>
                    <textarea name="description"
                              class="w-full mt-1 p-3 border border-gray-300 dark:border-gray-700 rounded-md bg-gray-50 dark:bg-gray-700
                        text-gray-900 dark:text-gray-100 focus:ring-blue-500 focus:border-blue-500"
                              rows="4">{{ old('description', $form->description) }}</textarea>
                    @error('description')
                    <span class="text-red-600 dark:text-red-400 text-sm">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Status Field -->
                <div class="mb-6">
                    <label class="block text-gray-700 dark:text-gray-300 font-medium mb-2">
                        Status
                    </label>
                    <select name="status"
                            class="w-full mt-1 p-3 border border-gray-300 dark:border-gray-700 rounded-md bg-gray-50 dark:bg-gray-700
                        text-gray-900 dark:text-gray-100 focus:ring-blue-500 focus:border-blue-500">
                        <option value="draft" {{ $form->status === 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="published" {{ $form->status === 'published' ? 'selected' : '' }}>Published
                        </option>
                        <option value="archived" {{ $form->status === 'archived' ? 'selected' : '' }}>Archived</option>
                    </select>
                    @error('status')
                    <span class="text-red-600 dark:text-red-400 text-sm">{{ $message }}</span>
                    @enderror
                </div>
                <div class="mb-6">
                    <label class="block text-gray-700 dark:text-gray-300 font-medium mb-2">
                        Visibility
                    </label>
                    <select name="visibility"
                            class="w-full mt-1 p-3 border border-gray-300 dark:border-gray-700 rounded-md bg-gray-50 dark:bg-gray-700
                text-gray-900 dark:text-gray-100 focus:ring-blue-500 focus:border-blue-500">
                        <option value="public" {{ $form->visibility === 'public' ? 'selected' : '' }}>Public</option>
                        <option value="authenticated" {{ $form->visibility === 'authenticated' ? 'selected' : '' }}>Authenticated Users Only</option>
                        <option value="private" {{ $form->visibility === 'private' ? 'selected' : '' }}>Private</option>
                    </select>
                    @error('visibility')
                    <span class="text-red-600 dark:text-red-400 text-sm">{{ $message }}</span>
                    @enderror
                </div>
                <!-- Submit Button -->
                <div class="flex justify-end">
                    <button type="submit"
                            class="px-6 py-2 bg-green-600 hover:bg-green-700 text-white rounded-md transition duration-200">
                        Update Form
                    </button>
                </div>
            </form>
        </div>

        @livewire('form-field-manager', ['form' => $form])

    </div>
</x-app-layout>
