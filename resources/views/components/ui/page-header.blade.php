@props([
    'eyebrow' => null,
    'title',
    'description' => null,
])

<header {{ $attributes->class(['pasya-page-header flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between']) }}>
    <div class="min-w-0 max-w-3xl">
        @if($eyebrow)
            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-green-700">{{ $eyebrow }}</p>
        @endif
        <h1 class="mt-1 text-2xl font-semibold tracking-[-0.025em] text-gray-950 sm:text-3xl">{{ $title }}</h1>
        @if($description)
            <p class="mt-2 max-w-2xl text-sm leading-6 text-gray-600">{{ $description }}</p>
        @endif
    </div>

    @isset($actions)
        <div class="flex flex-wrap items-center gap-2">
            {{ $actions }}
        </div>
    @endisset
</header>
