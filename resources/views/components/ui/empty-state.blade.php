@props([
    'title',
    'description' => null,
])

<div {{ $attributes->class(['pasya-empty-state flex flex-col items-center justify-center px-5 py-12 text-center']) }}>
    @isset($icon)
        <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-xl bg-gray-100 text-gray-500">
            {{ $icon }}
        </div>
    @endisset
    <h3 class="text-base font-semibold text-gray-900">{{ $title }}</h3>
    @if($description)
        <p class="mt-2 max-w-md text-sm leading-6 text-gray-500">{{ $description }}</p>
    @endif
    @isset($actions)
        <div class="mt-5 flex flex-wrap justify-center gap-2">
            {{ $actions }}
        </div>
    @endisset
</div>
