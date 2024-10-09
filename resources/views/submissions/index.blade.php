<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Submissions for {{ $form->title }}
        </h2>
    </x-slot>

    <div class="bg-white shadow rounded-lg p-6">
        @if($submissions->isEmpty())
            <p>No submissions yet.</p>
        @else
            <table class="min-w-full">
                <thead>
                <tr>
                    <th class="text-left py-2">ID</th>
                    <th class="text-left py-2">Submitted At</th>
                    <th class="text-left py-2">Actions</th>
                </tr>
                </thead>
                <tbody>
                @foreach($submissions as $submission)
                    <pre>
                        {{$submission}}
                    </pre>
                    <tr>
                        <td class="py-2">{{ $submission->id }}</td>
                        <td class="py-2">{{ $submission->created_at->format('M d, Y H:i') }}</td>
                        <td class="py-2">
                                <!-- Additional actions can be added here -->
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @endif
    </div>
</x-app-layout>
