@props([
    'padding' => true,
])

<section {{ $attributes->class([
    'pasya-surface border border-black/10 bg-white shadow-sm',
    'p-4 sm:p-5' => $padding,
]) }}>
    {{ $slot }}
</section>
