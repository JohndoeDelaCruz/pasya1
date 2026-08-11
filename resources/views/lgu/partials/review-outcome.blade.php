@php
    $isVerified = $status === 'approved';
@endphp

<div class="rounded-xl border {{ $isVerified ? 'border-green-200 bg-green-50/60' : 'border-orange-200 bg-orange-50/60' }} p-4">
    <p class="text-xs font-semibold uppercase tracking-wider {{ $isVerified ? 'text-green-700' : 'text-orange-700' }}">Review outcome</p>
    <p class="mt-2 text-sm font-semibold {{ $isVerified ? 'text-green-900' : 'text-orange-900' }}">{{ $isVerified ? 'Verified' : 'Needs correction' }}</p>
    <p class="mt-1 text-xs leading-5 text-gray-600">
        {{ $isVerified ? $verifiedEffect : 'Not included in official reporting until corrected and verified.' }}
    </p>
    <p class="mt-3 border-t {{ $isVerified ? 'border-green-200' : 'border-orange-200' }} pt-3 text-xs text-gray-500">
        Reviewed {{ $validatedAt?->format('M d, Y h:i A') ?? 'date unavailable' }}
    </p>

    @if($validationNotes)
        <div class="mt-3">
            <p class="text-xs font-semibold text-gray-700">Review note</p>
            <p class="mt-1 text-xs leading-5 text-gray-700">{{ $validationNotes }}</p>
        </div>
    @endif
</div>
