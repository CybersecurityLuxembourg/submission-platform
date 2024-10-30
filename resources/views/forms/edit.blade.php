@php use App\Models\User; @endphp
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
                        <option value="authenticated" {{ $form->visibility === 'authenticated' ? 'selected' : '' }}>
                            Authenticated Users Only
                        </option>
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
        <div class="mt-8 bg-white dark:bg-gray-800 shadow rounded-lg p-6">
            <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-gray-100">Assign Users</h3>

            <!-- Currently Assigned Users -->
            @if($form->appointedUsers->isNotEmpty())
                <div class="mb-6">
                    <h4 class="text-md font-medium mb-2 text-gray-700 dark:text-gray-300">Currently Assigned Users</h4>
                    <div class="bg-gray-50 dark:bg-gray-700 rounded-md p-4">
                        <ul class="divide-y divide-gray-200 dark:divide-gray-600">
                            @foreach($form->appointedUsers as $assignedUser)
                                <li class="py-2 flex justify-between items-center">
                                    <div>
                                        <span class="text-gray-900 dark:text-gray-100">{{ $assignedUser->name }}</span>
                                        <span class="text-gray-500 dark:text-gray-400 text-sm ml-2">({{ $assignedUser->email }})</span>
                                    </div>
                                    <div class="flex items-center">
                                <span class="text-sm {{ $assignedUser->pivot->can_edit ? 'text-green-600 dark:text-green-400' : 'text-gray-500 dark:text-gray-400' }} mr-4">
                                    {{ $assignedUser->pivot->can_edit ? 'Can Edit' : 'View Only' }}
                                </span>
                                        <form action="{{ route('forms.remove-user', [$form, $assignedUser]) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    onclick="return confirm('Are you sure you want to remove this user?')"
                                                    class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-200">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <!-- Assign New Users Form -->
            <form action="{{ route('forms.assign-users', $form) }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label class="block text-gray-700 dark:text-gray-300 font-medium mb-2">Select Users</label>
                    <select name="user_ids[]" multiple
                            class="w-full mt-1 p-2 border rounded-md bg-white dark:bg-gray-700 border-gray-300 dark:border-gray-600 text-gray-900 dark:text-gray-100 focus:ring-blue-500 focus:border-blue-500">
                        @foreach(User::whereIn('role', ['internal_evaluator', 'external_evaluator'])
                                    ->where('id', '!=', auth()->id())
                                    ->orderBy('name')
                                    ->get() as $user)
                            <option value="{{ $user->id }}"
                                {{ $form->appointedUsers->contains($user->id) ? 'selected' : '' }}>
                                {{ $user->name }} ({{ $user->email }}) - {{ ucfirst(str_replace('_', ' ', $user->role)) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-4">
                    <label class="inline-flex items-center">
                        <input type="checkbox" name="can_edit" value="1"
                               class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                        <span class="ml-2 text-gray-700 dark:text-gray-300">Allow Editing</span>
                    </label>
                </div>
                <button type="submit"
                        class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50">
                    Assign Users
                </button>
            </form>
        </div>

        <div class="mt-8 mb-8  bg-white dark:bg-gray-800 shadow rounded-lg p-6">
            <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-gray-100">Access Links (Work in Progress not working)</h3>
            <form action="{{ route('forms.create-access-link', $form) }}" method="POST" class="mb-6">
                @csrf
                <div class="mb-4">
                    <label class="block text-gray-700 dark:text-gray-300 font-medium mb-2">
                        Expiration Date (optional)
                    </label>
                    <input type="datetime-local" name="expires_at" disabled
                           class="w-full mt-1 p-2 border rounded-md bg-white dark:bg-gray-700 border-gray-300 dark:border-gray-600 text-gray-900 dark:text-gray-100 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <button type="submit" disabled
                        class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-opacity-50">
                    Create Access Link
                </button>
            </form>

            @if($form->accessLinks->isNotEmpty())
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Access Link
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Expires At
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($form->accessLinks as $link)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <input type="text" value="{{ route('form.access', $link->token) }}"
                                               class="flex-1 p-1 text-sm border rounded mr-2 bg-gray-50 dark:bg-gray-700"
                                               readonly>
                                        <button
                                            onclick="navigator.clipboard.writeText('{{ route('form.access', $link->token) }}')"
                                            class="px-2 py-1 text-sm bg-blue-500 text-white rounded hover:bg-blue-600">
                                            Copy
                                        </button>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-gray-700 dark:text-gray-300">
                                    {{ $link->expires_at ? $link->expires_at->format('Y-m-d H:i') : 'Never' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <form action="{{ route('forms.delete-access-link', $link) }}" method="POST"
                                          class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300 focus:outline-none">
                                            Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-gray-500 dark:text-gray-400 text-center py-4">No access links created yet.</p>
            @endif
        </div>
        @livewire('form-field-manager', ['form' => $form])

    </div>
</x-app-layout>
