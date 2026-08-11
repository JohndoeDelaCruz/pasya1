@props([
    'eyebrow' => null,
    'title',
    'description' => null,
])

<header {{ $attributes->class(['pasya-page-header flex flex-col gap-3 border-b border-gray-200 pb-3 sm:flex-row sm:items-center sm:justify-between']) }}>
    <div class="min-w-0 max-w-3xl">
        @if($eyebrow)
            <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-green-700">{{ $eyebrow }}</p>
        @endif
        <h1 class="mt-0.5 text-xl font-semibold tracking-[-0.02em] text-gray-950 sm:text-2xl">{{ $title }}</h1>
        @if($description)
            <p class="mt-1 max-w-2xl text-xs leading-5 text-gray-500 sm:text-sm">{{ $description }}</p>
        @endif
    </div>

    @isset($actions)
        <div class="flex flex-wrap items-center gap-2">
            {{ $actions }}
        </div>
    @endisset
</header>
