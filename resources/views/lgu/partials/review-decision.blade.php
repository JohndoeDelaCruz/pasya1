@php
    $visibleStatus = match ($status) {
        'approved' => 'Verified',
        'rejected' => 'Needs correction',
        default => 'Pending review',
    };
@endphp

<div class="rounded-xl border border-gray-200 bg-gray-50 p-4">
    <h4 class="text-sm font-semibold text-gray-900">Review decision</h4>

    @if($status === 'pending')
        <p class="mt-1 text-xs leading-5 text-gray-600">{{ $verificationHelp }}</p>

        <form method="POST" action="{{ $approveRoute }}" class="mt-3">
            @csrf
            <button :disabled="!online" aria-label="Verify {{ $subject }}" class="w-full rounded-lg bg-green-700 px-3 py-2.5 text-sm font-semibold text-white transition hover:bg-green-800 disabled:cursor-not-allowed disabled:bg-gray-300">
                Verify {{ $actionLabel }}
            </button>
        </form>

        <details class="mt-3 border-t border-gray-200 pt-3">
            <summary class="cursor-pointer text-center text-sm font-semibold text-orange-800">Return for correction</summary>
            <form method="POST" action="{{ $returnRoute }}" class="mt-3 space-y-3">
                @csrf
                <div>
                    <label for="{{ $notesId }}" class="block text-xs font-semibold text-gray-700">Correction required</label>
                    <textarea id="{{ $notesId }}" name="notes" required rows="3" placeholder="Explain what the farmer needs to correct" class="mt-1 w-full rounded-lg border-gray-300 text-sm focus:border-orange-600 focus:ring-orange-600"></textarea>
                    <p class="mt-1 text-xs leading-5 text-gray-500">This note is shown to the farmer with the returned submission.</p>
                </div>
                <button :disabled="!online" class="w-full rounded-lg border border-orange-300 bg-white px-3 py-2.5 text-sm font-semibold text-orange-800 transition hover:bg-orange-50 disabled:cursor-not-allowed disabled:border-gray-200 disabled:text-gray-400">
                    Return to farmer
                </button>
            </form>
        </details>
    @else
        <p class="mt-2 text-sm font-semibold {{ $status === 'approved' ? 'text-green-800' : 'text-orange-800' }}">{{ $visibleStatus }}</p>
        <p class="mt-1 text-xs leading-5 text-gray-600">
            {{ $status === 'approved' ? $verifiedEffect : 'This submission is outside official reporting until the farmer corrects it and it is verified.' }}
        </p>
        <p class="mt-2 text-xs text-gray-500">Reviewed {{ $validatedAt?->format('M d, Y') ?? 'date unavailable' }}</p>

        @if($validationNotes)
            <div class="mt-3 border-t border-gray-200 pt-3">
                <p class="text-xs font-semibold text-gray-600">Review note</p>
                <p class="mt-1 text-xs leading-5 text-gray-700">{{ $validationNotes }}</p>
            </div>
        @endif
    @endif
</div>
