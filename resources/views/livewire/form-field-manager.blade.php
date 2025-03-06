<div>
    <!-- Add Category Form -->
    <div class="bg-white dark:bg-gray-800 shadow-lg rounded-lg p-6 mb-8">
        <h2 class="text-2xl font-semibold mb-4 text-gray-700 dark:text-gray-300">Add New Category</h2>
        <form wire:submit.prevent="addCategory">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="category-name"
                           class="block text-sm font-medium text-gray-700 dark:text-gray-300">Name</label>
                    <input type="text" id="category-name" wire:model.defer="newCategory.name"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    @error('newCategory.name') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label for="category-description"
                           class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
                    <textarea id="category-description" wire:model.defer="newCategory.description" rows="3"
                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-white"></textarea>
                    @error('newCategory.description') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                </div>
            </div>
            <div class="flex justify-end mt-6">
                <button type="submit"
                        class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-colors duration-300">
                    Add Category
                </button>
            </div>
        </form>
    </div>

    <!-- Add Field Form -->
    <div class="bg-white dark:bg-gray-800 shadow-lg rounded-lg p-6 mb-8">
        <h2 class="text-2xl font-semibold mb-4 text-gray-700 dark:text-gray-300">Add New Field</h2>
        <form wire:submit.prevent="addField">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="field-category" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Category</label>
                    <select id="field-category" wire:model.live="newField.category_id"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        <option value="">Select a category</option>
                        @foreach($categories as $category)
                            <option value="{{ $category['id'] }}">{{ $category['name'] }}</option>
                        @endforeach
                    </select>
                    @error('newField.category_id') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label for="field-type"
                           class="block text-sm font-medium text-gray-700 dark:text-gray-300">Type</label>
                    <select id="field-type" wire:model.live="newField.type"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        <option value="">Select Type</option>
                        <option value="header">Header</option>
                        <option value="description">Description</option>
                        <option value="text">Text</option>
                        <option value="textarea">Textarea</option>
                        <option value="select">Select</option>
                        <option value="checkbox">Checkbox</option>
                        <option value="radio">Radio</option>
                        <option value="file">File</option>
                    </select>
                    @error('newField.type') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                </div>
            </div>

            <!-- Conditional fields based on type -->
            @if($newField['type'])
                @if(in_array($newField['type'], ['header', 'description']))
                    <div class="mt-6">
                        <label for="field-content" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ ucfirst($newField['type']) }} Content
                        </label>
                        @if($newField['type'] === 'description')
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400 mb-2">
                                You can use markdown formatting: **bold**, *italic*, and bullet points (start line with "- ").
                            </p>
                        @endif
                        <textarea id="field-content" wire:model.live="newField.content" rows="3"
                                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-white"></textarea>
                        @error('newField.content') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                        
                        @if($newField['type'] === 'description' && $newField['content'])
                            <div class="mt-4">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Preview:</label>
                                <div class="p-3 bg-gray-50 dark:bg-gray-700 rounded border border-gray-200 dark:border-gray-600">
                                    {!! \App\Helpers\MarkdownHelper::toHtml($newField['content']) !!}
                                </div>
                                <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                                    You can use **bold**, *italic*, and bullet points (start line with "- ") for formatting.
                                </p>
                            </div>
                        @endif
                    </div>
                @else
                    <div class="mt-6">
                        <label for="field-label" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Label</label>
                        <input type="text" id="field-label" wire:model.live="newField.label"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        @error('newField.label') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                    </div>

                    @if(in_array($newField['type'], ['select', 'checkbox', 'radio']))
                        <div class="mt-6">
                            <label for="field-options"
                                   class="block text-sm font-medium text-gray-700 dark:text-gray-300">Options
                                (comma-separated)</label>
                            <input type="text" id="field-options" wire:model.live="newField.options"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            @error('newField.options') <span
                                class="text-red-600 text-sm">{{ $message }}</span> @enderror
                        </div>
                    @endif
                    @if(in_array($newField['type'], ['text', 'textarea']))
                        <div class="mt-6">
                            <label for="field-char-limit"
                                   class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Character Limit (optional)
                            </label>
                            <input type="number"
                                   id="field-char-limit"
                                   wire:model.live="newField.char_limit"
                                   min="1"
                                   placeholder="No limit"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            @error('newField.char_limit')
                            <span class="text-red-600 text-sm">{{ $message }}</span>
                            @enderror
                        </div>
                    @endif

                    <div class="mt-6 flex items-center">
                        <input type="checkbox" id="field-required" wire:model.live="newField.required"
                               class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        <label for="field-required"
                               class="ml-2 block text-sm text-gray-700 dark:text-gray-300">Required</label>
                    </div>
                @endif
            @endif

            <div class="flex justify-end mt-6">
                <button type="submit"
                        class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition-colors duration-300">
                    Add Field
                </button>
            </div>
        </form>
    </div>


    <!-- Form Structure -->
    <div class="bg-white dark:bg-gray-800 shadow-lg rounded-lg p-6">
        <h2 class="text-2xl font-semibold mb-4 text-gray-700 dark:text-gray-300">Form Structure</h2>
        <div class="space-y-4">
            @foreach($categories as $index => $category)
                <div class="bg-gray-100 dark:bg-gray-700 rounded-lg">
                    <div class="flex items-center justify-between p-4">
                        <h4 class="text-lg font-medium text-gray-700 dark:text-gray-300">{{ $category['name'] }}</h4>
                        <div class="flex space-x-2">
                            @if($index > 0)
                                <button wire:click="moveCategoryUp({{ $category['id'] }})"
                                        class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-200">
                                    Move Up
                                </button>
                            @endif
                            @if($index < count($categories) - 1)
                                <button wire:click="moveCategoryDown({{ $category['id'] }})"
                                        class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-200">
                                    Move Down
                                </button>
                            @endif
                            <button wire:click="editCategory({{ $category['id'] }})"
                                    class="text-green-600 hover:text-green-800 dark:text-green-400 dark:hover:text-green-200">
                                Edit
                            </button>
                            <button wire:click="confirmDeleteCategory({{ $category['id'] }})"
                                    class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-200">
                                Delete
                            </button>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-gray-600 p-4 rounded-b-lg space-y-2">
                        @foreach($category['fields'] as $fieldIndex => $field)
                            <div
                                class="flex items-center justify-between py-2 px-2 bg-gray-50 dark:bg-gray-700 rounded-md">
                                <div class="flex items-center">
                                    @if($field['type'] == 'header')
                                        <span
                                            class="font-semibold text-gray-800 dark:text-gray-100 text-lg">{{ $field['content'] }}</span>
                                    @elseif($field['type'] == 'description')
                                        <span class="text-gray-600 dark:text-gray-300">{{ $field['content'] }}</span>
                                    @else
                                        <span
                                            class="font-medium text-gray-700 dark:text-gray-300">{{ $field['label'] }}</span>
                                        <span
                                            class="text-gray-500 dark:text-gray-400 ml-2">({{ $field['type'] }})</span>
                                    @endif
                                </div>
                                <div class="flex space-x-2">
                                    @if($fieldIndex > 0)
                                        <button wire:click="moveFieldUp({{ $field['id'] }})"
                                                class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-200">
                                            Move Up
                                        </button>
                                    @endif
                                    @if($fieldIndex < count($category['fields']) - 1)
                                        <button wire:click="moveFieldDown({{ $field['id'] }})"
                                                class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-200">
                                            Move Down
                                        </button>
                                    @endif
                                    <button wire:click="editField({{ $field['id'] }})"
                                            class="text-green-600 hover:text-green-800 dark:text-green-400 dark:hover:text-green-200">
                                        Edit
                                    </button>
                                    <button wire:click="confirmDeleteField({{ $field['id'] }})"
                                            class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-200">
                                        Delete
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    @if($confirmingCategoryDeletion)
        <div class="fixed z-10 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div
                    class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                    Delete Category
                                </h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500">
                                        Are you sure you want to delete this category? This action cannot be undone.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="button" wire:click="deleteCategory"
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Delete
                        </button>
                        <button type="button" wire:click="$set('confirmingCategoryDeletion', false)"
                                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Field Deletion Confirmation Modal -->
    @if($confirmingFieldDeletion)
        <div class="fixed z-10 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div
                    class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                    Delete Field
                                </h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500">
                                        Are you sure you want to delete this field? This action cannot be undone.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="button" wire:click="deleteField"
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Delete
                        </button>
                        <button type="button" wire:click="$set('confirmingFieldDeletion', false)"
                                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if($editingCategory)
        <div class="fixed z-10 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div
                    class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <form wire:submit.prevent="updateCategory">
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <div class="mb-4">
                                <label for="edit-category-name"
                                       class="block text-sm font-medium text-gray-700">Name</label>
                                <input type="text" id="edit-category-name" wire:model.defer="categoryBeingEdited.name"
                                       class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                @error('categoryBeingEdited.name') <span
                                    class="text-red-600 text-sm">{{ $message }}</span> @enderror
                            </div>
                            <div class="mb-4">
                                <label for="edit-category-description" class="block text-sm font-medium text-gray-700">Description</label>
                                <textarea id="edit-category-description"
                                          wire:model.defer="categoryBeingEdited.description" rows="3"
                                          class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"></textarea>
                                @error('categoryBeingEdited.description') <span
                                    class="text-red-600 text-sm">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="submit"
                                    class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                                Update
                            </button>
                            <button type="button" wire:click="$set('editingCategory', false)"
                                    class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
    <!-- Field Edit Modal -->
    @if($editingField)
        <div class="fixed z-10 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div
                    class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <form wire:submit.prevent="updateField">
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <div class="mb-4">
                                <label for="edit-field-type"
                                       class="block text-sm font-medium text-gray-700">Type</label>
                                <select id="edit-field-type" wire:model="fieldBeingEdited.type"
                                        class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    <option value="header">Header</option>
                                    <option value="description">Description</option>
                                    <option value="text">Text</option>
                                    <option value="textarea">Textarea</option>
                                    <option value="select">Select</option>
                                    <option value="checkbox">Checkbox</option>
                                    <option value="radio">Radio</option>
                                    <option value="file">File</option>
                                </select>
                                @error('fieldBeingEdited.type') <span
                                    class="text-red-600 text-sm">{{ $message }}</span> @enderror
                            </div>

                            @if(in_array($fieldBeingEdited['type'], ['header', 'description']))
                                <div class="mb-4">
                                    <label for="edit-field-content" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Content</label>
                                    @if($fieldBeingEdited['type'] === 'description')
                                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400 mb-2">
                                            You can use markdown formatting: **bold**, *italic*, and bullet points (start line with "- ").
                                        </p>
                                    @endif
                                    <textarea id="edit-field-content" wire:model.live="fieldBeingEdited.content"
                                              rows="3"
                                              class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md dark:bg-gray-700 dark:border-gray-600 dark:text-white"></textarea>
                                    @error('fieldBeingEdited.content') <span
                                        class="text-red-600 text-sm">{{ $message }}</span> @enderror
                                        
                                    @if($fieldBeingEdited['type'] === 'description' && $fieldBeingEdited['content'])
                                        <div class="mt-4">
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Preview:</label>
                                            <div class="p-3 bg-gray-50 dark:bg-gray-700 rounded border border-gray-200 dark:border-gray-600">
                                                {!! \App\Helpers\MarkdownHelper::toHtml($fieldBeingEdited['content']) !!}
                                            </div>
                                            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                                                You can use **bold**, *italic*, and bullet points (start line with "- ") for formatting.
                                            </p>
                                        </div>
                                    @endif
                                </div>
                            @else
                                <div class="mb-4">
                                    <label for="edit-field-label"
                                           class="block text-sm font-medium text-gray-700">Label</label>
                                    <input type="text" id="edit-field-label" wire:model.defer="fieldBeingEdited.label"
                                           class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                    @error('fieldBeingEdited.label') <span
                                        class="text-red-600 text-sm">{{ $message }}</span> @enderror
                                </div>

                                @if(in_array($fieldBeingEdited['type'], ['select', 'checkbox', 'radio']))
                                    <div class="mb-4">
                                        <label for="edit-field-options" class="block text-sm font-medium text-gray-700">Options
                                            (comma-separated)</label>
                                        <input type="text" id="edit-field-options"
                                               wire:model.defer="fieldBeingEdited.options"
                                               class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                        @error('fieldBeingEdited.options') <span
                                            class="text-red-600 text-sm">{{ $message }}</span> @enderror
                                    </div>
                                @endif

                                <div class="mb-4">
                                    <label for="edit-field-required" class="flex items-center">
                                        <input type="checkbox" id="edit-field-required"
                                               wire:model.defer="fieldBeingEdited.required"
                                               class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                                        <span class="ml-2 text-sm text-gray-700">Required</span>
                                    </label>
                                </div>


                                @if(in_array($fieldBeingEdited['type'], ['text', 'textarea']))
                                    <div class="mb-4">
                                        <label for="edit-field-char-limit"
                                               class="block text-sm font-medium text-gray-700">
                                            Character Limit (optional)
                                        </label>
                                        <input type="number"
                                               id="edit-field-char-limit"
                                               wire:model.defer="fieldBeingEdited.char_limit"
                                               min="1"
                                               placeholder="No limit"
                                               class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                        @error('fieldBeingEdited.char_limit')
                                        <span class="text-red-600 text-sm">{{ $message }}</span>
                                        @enderror
                                    </div>
                                @endif

                                <!-- In the Form Structure display, add this after the field type display -->
                                @if(isset($field['char_limit']) && $field['char_limit'] && in_array($field['type'], ['text', 'textarea']))
                                    <span class="text-gray-500 dark:text-gray-400 ml-2">
                                    (max {{ $field['char_limit'] }} characters)
                                    </span>
                                @endif
                            @endif
                        </div>
                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="submit"
                                    class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                                Update
                            </button>
                            <button type="button" wire:click="$set('editingField', false)"
                                    class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>


