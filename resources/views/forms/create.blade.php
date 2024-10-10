<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Create New Form
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <form action="{{ route('forms.store') }}" method="POST" x-data="{ categories: [{ name: '', description: '', percentage_start: 0, percentage_end: 100 }] }">
                    @csrf

                    <div class="mb-6">
                        <label for="title" class="block text-gray-700 dark:text-gray-300 font-medium mb-2">Title</label>
                        <input type="text" name="title" id="title" value="{{ old('title') }}"
                               class="w-full mt-1 p-2 border rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                               required>
                        @error('title')
                        <span class="text-red-600 text-sm">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="mb-6">
                        <label for="description" class="block text-gray-700 dark:text-gray-300 font-medium mb-2">Description</label>
                        <textarea name="description" id="description"
                                  class="w-full mt-1 p-2 border rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                  rows="3">{{ old('description') }}</textarea>
                        @error('description')
                        <span class="text-red-600 text-sm">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="mb-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Form Categories</h3>
                        <template x-for="(category, index) in categories" :key="index">
                            <div class="mb-4 p-4 border rounded-md dark:border-gray-600">
                                <div class="mb-3">
                                    <label :for="'category_name_'+index" class="block text-gray-700 dark:text-gray-300 font-medium mb-2">Category Name</label>
                                    <input type="text" :name="'categories['+index+'][name]'" :id="'category_name_'+index" x-model="category.name"
                                           class="w-full mt-1 p-2 border rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                           required>
                                </div>
                                <div class="mb-3">
                                    <label :for="'category_description_'+index" class="block text-gray-700 dark:text-gray-300 font-medium mb-2">Category Description</label>
                                    <textarea :name="'categories['+index+'][description]'" :id="'category_description_'+index" x-model="category.description"
                                              class="w-full mt-1 p-2 border rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                              rows="2"></textarea>
                                </div>
                                <div class="flex space-x-4 mb-3">
                                    <div class="flex-1">
                                        <label :for="'category_percentage_start_'+index" class="block text-gray-700 dark:text-gray-300 font-medium mb-2">Start Percentage</label>
                                        <input type="number" :name="'categories['+index+'][percentage_start]'" :id="'category_percentage_start_'+index" x-model="category.percentage_start"
                                               class="w-full mt-1 p-2 border rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                               min="0" max="100" required>
                                    </div>
                                    <div class="flex-1">
                                        <label :for="'category_percentage_end_'+index" class="block text-gray-700 dark:text-gray-300 font-medium mb-2">End Percentage</label>
                                        <input type="number" :name="'categories['+index+'][percentage_end]'" :id="'category_percentage_end_'+index" x-model="category.percentage_end"
                                               class="w-full mt-1 p-2 border rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                               min="0" max="100" required>
                                    </div>
                                </div>
                                <button type="button" @click="categories = categories.filter((_, i) => i !== index)"
                                        class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300"
                                        x-show="categories.length > 1">
                                    Remove Category
                                </button>
                            </div>
                        </template>
                        <button type="button" @click="categories.push({ name: '', description: '', percentage_start: 0, percentage_end: 100 })"
                                class="mt-2 px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-opacity-50 dark:bg-green-600 dark:hover:bg-green-700">
                            Add Category
                        </button>
                    </div>

                    <div class="mt-8">
                        <button type="submit"
                                class="px-6 py-3 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 dark:bg-blue-700 dark:hover:bg-blue-800">
                            Create Form
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
