<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            My Forms
        </h2>
    </x-slot>
    <div class="flex justify-between items-center mb-6">
        <a href="{{ route('forms.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded">Create New Form</a>
    </div>

    <div class="bg-white shadow rounded-lg p-6">
        @if($forms->isEmpty())
            <p>You have not created any forms yet.</p>
        @else
            <table class="min-w-full">
                <thead>
                <tr>
                    <th class="text-left py-2">Title</th>
                    <th class="text-left py-2">Status</th>
                    <th class="text-left py-2">Created At</th>
                    <th class="text-left py-2">Actions</th>
                </tr>
                </thead>
                <tbody>
                @foreach($forms as $form)
                    <tr>
                        <td class="py-2">{{ $form->title }}</td>
                        <td class="py-2 capitalize">{{ $form->status }}</td>
                        <td class="py-2">{{ $form->created_at->format('M d, Y') }}</td>
                        <td class="py-2">
                            <a href="{{ route('forms.edit', $form) }}" class="text-blue-600">Edit</a>
                            |
                            <a href="{{ route('submissions.index', $form) }}" class="text-green-600">Submissions</a>
                            |
                            <form action="{{ route('forms.destroy', $form) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600">Delete</button>
                            </form>
                            @if($form->status === 'published')
                                |
                                <a href="{{ route('forms.preview', $form) }}" class="text-indigo-600">Preview</a>
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @endif
    </div>
</x-app-layout>
