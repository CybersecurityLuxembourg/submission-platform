@extends('layouts.app')

@section('content')
<h1 class="text-2xl font-semibold mb-6">Submission #{{ $submission->id }}</h1>

<div class="bg-white shadow rounded-lg p-6">
    @foreach($submission->values as $value)
    <div class="mb-4">
        <label class="block text-gray-700 font-semibold">{{ $value->field->label }}</label>
        <p class="mt-2">{{ $value->value }}</p>
    </div>
    @endforeach
</div>
@endsection
