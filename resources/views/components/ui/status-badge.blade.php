@props([
    'tone' => 'neutral',
])

@php
    $toneClass = match ($tone) {
        'success' => 'border-green-200 bg-green-50 text-green-800',
        'warning' => 'border-amber-200 bg-amber-50 text-amber-900',
        'danger' => 'border-red-200 bg-red-50 text-red-800',
        'info' => 'border-blue-200 bg-blue-50 text-blue-800',
        default => 'border-gray-200 bg-gray-50 text-gray-700',
    };
@endphp

<span {{ $attributes->class(['inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-semibold', $toneClass]) }}>
    {{ $slot }}
</span>
