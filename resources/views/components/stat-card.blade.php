{{-- resources/views/components/stat-card.blade.php --}}
@props(['title', 'value'])

<div class="bg-white rounded-lg shadow p-6 flex flex-col">
    <h3 class="text-sm text-gray-500 mb-2">{{ $title }}</h3>
    <p class="text-3xl font-semibold text-gray-900">{{ $value }}</p>
</div>
