<x-admin-layout>
    <x-slot name="title">LGU Validators</x-slot>

    @php
        $filters = $filters ?? [];
    @endphp

    <div class="min-h-full bg-gray-50">
        <div class="p-3 sm:p-6 space-y-5">
            @if(session('success'))
                <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-semibold text-green-800">
                    {{ session('success') }}
                </div>
            @endif

            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">LGU Validators</h1>
                    <p class="mt-1 text-sm text-gray-500">Create and manage municipality-scoped LGU validator staff accounts.</p>
                </div>
                <a href="{{ route('admin.lgu-validators.create') }}" class="inline-flex items-center justify-center rounded-xl bg-green-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-green-700">
                    Add Validator
                </a>
            </div>

            <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm">
                    <p class="text-xs font-semibold uppercase text-gray-500">Total</p>
                    <p class="mt-2 text-2xl font-bold text-gray-900">{{ number_format($stats['total']) }}</p>
                </div>
                <div class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm">
                    <p class="text-xs font-semibold uppercase text-gray-500">Active</p>
                    <p class="mt-2 text-2xl font-bold text-green-700">{{ number_format($stats['active']) }}</p>
                </div>
                <div class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm">
                    <p class="text-xs font-semibold uppercase text-gray-500">Inactive</p>
                    <p class="mt-2 text-2xl font-bold text-gray-700">{{ number_format($stats['inactive']) }}</p>
                </div>
                <div class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm">
                    <p class="text-xs font-semibold uppercase text-gray-500">Municipalities</p>
                    <p class="mt-2 text-2xl font-bold text-gray-900">{{ number_format($stats['municipalities']) }}</p>
                </div>
            </div>

            <form method="GET" action="{{ route('admin.lgu-validators.index') }}" class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm">
                <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-5">
                    <div class="xl:col-span-2">
                        <label for="search" class="block text-sm font-medium text-gray-700">Search</label>
                        <input id="search" name="search" value="{{ $filters['search'] ?? '' }}" type="text" placeholder="Name, username, email" class="mt-1 w-full rounded-xl border-gray-200 text-sm focus:border-green-500 focus:ring-green-500">
                    </div>
                    <div>
                        <label for="municipality" class="block text-sm font-medium text-gray-700">Municipality</label>
                        <select id="municipality" name="municipality" class="mt-1 w-full rounded-xl border-gray-200 text-sm focus:border-green-500 focus:ring-green-500">
                            <option value="">All municipalities</option>
                            @foreach($municipalities as $municipality)
                                <option value="{{ $municipality }}" @selected(($filters['municipality'] ?? '') === $municipality)>{{ ucwords(strtolower($municipality)) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                        <select id="status" name="status" class="mt-1 w-full rounded-xl border-gray-200 text-sm focus:border-green-500 focus:ring-green-500">
                            <option value="">All statuses</option>
                            <option value="active" @selected(($filters['status'] ?? '') === 'active')>Active</option>
                            <option value="inactive" @selected(($filters['status'] ?? '') === 'inactive')>Inactive</option>
                        </select>
                    </div>
                    <div class="flex flex-wrap items-end gap-2">
                        <button class="inline-flex flex-1 items-center justify-center rounded-xl bg-green-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-green-700 sm:flex-none">Filter</button>
                        <a href="{{ route('admin.lgu-validators.index') }}" class="inline-flex flex-1 items-center justify-center rounded-xl border border-gray-200 px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50 sm:flex-none">Reset</a>
                    </div>
                </div>
            </form>

            <div class="overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm">
                <div class="hidden overflow-x-auto md:block">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">Validator</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">Municipality</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">Status</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">Created</th>
                                <th class="px-5 py-3 text-right text-xs font-semibold uppercase text-gray-500">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($validators as $validator)
                                <tr class="align-top">
                                    <td class="px-5 py-4">
                                        <p class="font-semibold text-gray-900">{{ $validator->name }}</p>
                                        <p class="mt-1 text-xs text-gray-500">{{ $validator->email }}</p>
                                        <p class="mt-1 text-xs text-gray-500">{{ $validator->username }}</p>
                                    </td>
                                    <td class="px-5 py-4 text-sm text-gray-700">{{ ucwords(strtolower($validator->municipality)) }}</td>
                                    <td class="px-5 py-4">
                                        <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $validator->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-700' }}">
                                            {{ $validator->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-4 text-sm text-gray-500">{{ $validator->created_at?->format('M d, Y') }}</td>
                                    <td class="px-5 py-4">
                                        <div class="flex flex-wrap justify-end gap-2">
                                            <a href="{{ route('admin.lgu-validators.edit', $validator) }}" class="rounded-lg border border-gray-200 px-3 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50">Edit</a>
                                            <form method="POST" action="{{ route('admin.lgu-validators.active', $validator) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button class="rounded-lg px-3 py-2 text-xs font-semibold {{ $validator->is_active ? 'bg-gray-100 text-gray-700 hover:bg-gray-200' : 'bg-green-600 text-white hover:bg-green-700' }}">
                                                    {{ $validator->is_active ? 'Deactivate' : 'Activate' }}
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-5 py-10 text-center text-sm text-gray-500">No LGU validator accounts found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="divide-y divide-gray-100 md:hidden">
                    @forelse($validators as $validator)
                        <div class="p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="font-semibold text-gray-900">{{ $validator->name }}</p>
                                    <p class="mt-1 break-all text-xs text-gray-500">{{ $validator->email }}</p>
                                    <p class="mt-1 text-xs text-gray-500">{{ ucwords(strtolower($validator->municipality)) }}</p>
                                </div>
                                <span class="shrink-0 rounded-full px-2.5 py-1 text-xs font-semibold {{ $validator->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-700' }}">{{ $validator->is_active ? 'Active' : 'Inactive' }}</span>
                            </div>
                            <div class="mt-4 flex flex-wrap gap-2">
                                <a href="{{ route('admin.lgu-validators.edit', $validator) }}" class="inline-flex flex-1 items-center justify-center rounded-lg border border-gray-200 px-3 py-2 text-xs font-semibold text-gray-700">Edit</a>
                                <form method="POST" action="{{ route('admin.lgu-validators.active', $validator) }}" class="flex-1">
                                    @csrf
                                    @method('PATCH')
                                    <button class="w-full rounded-lg px-3 py-2 text-xs font-semibold {{ $validator->is_active ? 'bg-gray-100 text-gray-700' : 'bg-green-600 text-white' }}">{{ $validator->is_active ? 'Deactivate' : 'Activate' }}</button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div class="p-8 text-center text-sm text-gray-500">No LGU validator accounts found.</div>
                    @endforelse
                </div>

                <div class="border-t border-gray-100 px-4 py-4">
                    {{ $validators->links() }}
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
