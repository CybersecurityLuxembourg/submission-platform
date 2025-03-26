@props([
    'label' => '',
    'value' => 0,
    'colSpan' => 1
])

<div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4" style="grid-column: span {{ $colSpan }}">
    <div class="text-sm text-gray-500 dark:text-gray-400">{{ $label }}</div>
    <div class="text-2xl font-semibold">{{ $value }}</div>
</div> 