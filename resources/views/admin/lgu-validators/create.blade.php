<x-admin-layout>
    <x-slot name="title">Create LGU Validator</x-slot>

    <div class="min-h-full bg-gray-50">
        <div class="p-3 sm:p-6">
            <div class="admin-feature-page-header mb-5 border-b border-gray-200 pb-5">
                <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-gray-500">Review access</p>
                <h1 class="mt-1 text-2xl font-semibold tracking-tight text-gray-950 sm:text-3xl">Create LGU validator</h1>
                <p class="mt-2 text-sm leading-6 text-gray-600">Assign review access to one Benguet municipality, with optional barangay-level scope.</p>
            </div>

            <form method="POST" action="{{ route('admin.lgu-validators.store') }}" class="rounded-xl border border-gray-200 bg-white p-4 sm:p-6 [&_input:not([type=checkbox])]:min-h-11 [&_select]:min-h-11">
                @include('admin.lgu-validators._form', ['validator' => null, 'submitLabel' => 'Create Validator'])
            </form>
        </div>
    </div>
</x-admin-layout>
