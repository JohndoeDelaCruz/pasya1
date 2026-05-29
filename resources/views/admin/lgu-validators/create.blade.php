<x-admin-layout>
    <x-slot name="title">Create LGU Validator</x-slot>

    <div class="min-h-full bg-gray-50">
        <div class="p-3 sm:p-6">
            <div class="mb-5">
                <h1 class="text-2xl font-bold text-gray-900">Create LGU Validator</h1>
                <p class="mt-1 text-sm text-gray-500">Assign this staff account to exactly one Benguet municipality.</p>
            </div>

            <form method="POST" action="{{ route('admin.lgu-validators.store') }}" class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm sm:p-6">
                @include('admin.lgu-validators._form', ['validator' => null, 'submitLabel' => 'Create Validator'])
            </form>
        </div>
    </div>
</x-admin-layout>
