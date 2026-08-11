<x-admin-layout>
    <x-slot name="title">Account Management</x-slot>

    <div class="admin-feature-farmer-accounts space-y-5 p-3 sm:p-6">
        {{-- Header --}}
        <div class="admin-feature-page-header flex flex-col gap-4 border-b border-gray-200 pb-5 xl:flex-row xl:items-end xl:justify-between">
            <div class="max-w-2xl">
                <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-gray-500">Access administration</p>
                <h1 class="mt-1 text-2xl font-semibold tracking-tight text-gray-950 sm:text-3xl">Farmer accounts</h1>
                <p class="mt-2 text-sm leading-6 text-gray-600">Create identities, maintain contact and municipality data, or archive access when an account should no longer sign in.</p>
            </div>
            <div class="flex flex-col sm:flex-row gap-3 self-start sm:self-auto">
                <a href="{{ route('admin.farmers.archived') }}"
                   class="inline-flex min-h-11 items-center justify-center self-start rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-800 transition hover:bg-gray-50 sm:self-auto">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                    </svg>
                    <span>Archived Accounts</span>
                    @if($stats['archived_farmers'] > 0)
                        <span class="ml-2 rounded-full bg-amber-700 px-2 py-0.5 text-xs font-bold text-white">{{ number_format($stats['archived_farmers']) }}</span>
                    @endif
                </a>
                <form method="POST" action="{{ route('admin.farmers.import') }}" enctype="multipart/form-data" class="self-start sm:self-auto">
                    @csrf
                    <input type="file"
                           id="farmers_file"
                           name="farmers_file"
                           accept=".xlsx,.xls"
                           required
                           class="sr-only"
                           onchange="this.form.submit()">
                    <label for="farmers_file"
                           class="inline-flex min-h-11 cursor-pointer items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-800 transition hover:bg-gray-50">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1M12 4v12m0-12l4 4m-4-4L8 8"/>
                        </svg>
                        <span>Import Excel</span>
                    </label>
                </form>
                <a href="{{ route('admin.farmers.create') }}" 
                   class="inline-flex min-h-11 items-center self-start rounded-lg bg-gray-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-gray-800 sm:self-auto">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span class="hidden sm:inline">Create Farmer Account</span>
                    <span class="sm:hidden">New Farmer</span>
                </a>
            </div>
        </div>

        <div class="admin-feature-consequence-note rounded-xl border border-gray-200 bg-gray-50 p-4">
            <p class="text-sm font-semibold text-gray-900">Account changes affect sign-in and record ownership</p>
            <p class="mt-1 text-xs leading-5 text-gray-600">Verify the farmer ID and municipality before saving. Archiving disables sign-in but keeps the account recoverable.</p>
        </div>

        {{-- Success Message --}}
        @if(session('success'))
            <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-6 rounded-r-lg">
                <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
            </div>
        @endif

        @if($errors->has('farmers_file'))
            <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded-r-lg">
                <p class="text-sm font-medium text-red-800">Please check the import file.</p>
                @error('farmers_file')
                    <p class="text-sm text-red-700 mt-1">{{ $message }}</p>
                @enderror
            </div>
        @endif

        {{-- Stats Cards --}}
        <div class="admin-feature-account-summary grid grid-cols-1 gap-3 md:grid-cols-3">
            <div class="rounded-xl border border-gray-200 bg-white p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Total Farmers</p>
                        <p class="text-2xl font-bold text-gray-800">{{ number_format($stats['total_farmers']) }}</p>
                    </div>
                    <div class="bg-blue-100 rounded-full p-3">
                        <svg class="w-6 h-6 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Municipalities</p>
                        <p class="text-2xl font-bold text-gray-800">{{ number_format($stats['total_municipalities']) }}</p>
                    </div>
                    <div class="bg-green-100 rounded-full p-3">
                        <svg class="w-6 h-6 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Cooperatives</p>
                        <p class="text-2xl font-bold text-gray-800">{{ number_format($stats['total_cooperatives']) }}</p>
                    </div>
                    <div class="bg-yellow-100 rounded-full p-3">
                        <svg class="w-6 h-6 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        {{-- Filters --}}
        <div class="admin-feature-account-filters rounded-xl border border-gray-200 bg-white p-4">
            <form method="GET" action="{{ route('admin.farmers.index') }}" class="flex flex-wrap gap-3 items-end" data-auto-filter-form>
                @if(request()->boolean('no_ids'))
                    <input type="hidden" name="no_ids" value="1">
                @endif

                {{-- Search Input --}}
                <div class="flex-1 min-w-[200px]">
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                    <input type="text" 
                           id="search" 
                           name="search" 
                           value="{{ request('search') }}"
                           placeholder="Search by ID, name, municipality..."
                           class="min-h-11 w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-transparent focus:outline-none focus:ring-2 focus:ring-green-500">
                </div>

                {{-- Municipality Filter --}}
                <div class="min-w-[180px]">
                    <label for="municipality" class="block text-sm font-medium text-gray-700 mb-1">Municipality</label>
                    <select id="municipality" 
                            name="municipality"
                            class="min-h-11 w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-transparent focus:outline-none focus:ring-2 focus:ring-green-500">
                        <option value="">All Municipalities</option>
                        @foreach($municipalities as $municipality)
                            <option value="{{ $municipality }}" {{ request('municipality') == $municipality ? 'selected' : '' }}>
                                {{ $municipality }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- FCA Filter --}}
                <div class="min-w-[220px]">
                    <label for="cooperative" class="block text-sm font-medium text-gray-700 mb-1">FCA</label>
                    <select id="cooperative"
                            name="cooperative"
                            class="min-h-11 w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-transparent focus:outline-none focus:ring-2 focus:ring-green-500">
                        <option value="">All FCAs</option>
                        @foreach($cooperatives as $cooperative)
                            <option value="{{ $cooperative }}" {{ request('cooperative') == $cooperative ? 'selected' : '' }}>
                                {{ $cooperative }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="contents" data-filter-action-row>
                    {{-- Filter Button --}}
                    <div>
                        <button type="submit"
                                class="inline-flex min-h-11 items-center rounded-lg bg-gray-900 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-gray-800">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                            </svg>
                            Filter
                        </button>
                    </div>

                    {{-- No IDs Button --}}
                    <div>
                        <a href="{{ route('admin.farmers.index', ['no_ids' => 1]) }}"
                           class="inline-flex min-h-11 items-center rounded-lg px-5 py-2.5 text-sm font-semibold transition {{ request()->boolean('no_ids') ? 'bg-red-700 text-white hover:bg-red-800' : 'border border-red-200 bg-white text-red-700 hover:bg-red-50' }}">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636L5.636 18.364M12 9v4m0 4h.01M12 3a9 9 0 110 18 9 9 0 010-18z"/>
                            </svg>
                            Missing farmer IDs
                        </a>
                    </div>

                    {{-- Clear Button --}}
                    @if(request()->hasAny(['search', 'municipality', 'cooperative', 'no_ids']))
                        <div>
                            <a href="{{ route('admin.farmers.index') }}"
                               class="inline-flex min-h-11 items-center rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                                Clear
                            </a>
                        </div>
                    @endif
                </div>
            </form>
        </div>

        {{-- Farmers Table --}}
        <div class="admin-feature-account-table overflow-hidden rounded-xl border border-gray-200 bg-white" data-farmer-results aria-live="polite">
            <div class="overflow-x-auto">
                <table class="min-w-[980px] w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Farmer ID</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Municipality</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cooperative</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mobile Number</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($farmers as $farmer)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900">{{ $farmer->farmer_id }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600">{{ $farmer->full_name }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600">{{ $farmer->municipality_display }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600">{{ $farmer->cooperative_display ?: 'N/A' }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600">{{ $farmer->mobile_number }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600">{{ $farmer->created_at->format('M d, Y') }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <a href="{{ route('admin.farmers.edit', $farmer) }}" 
                                           class="text-blue-600 hover:text-blue-800 font-medium">
                                            Edit
                                        </a>
                                        <form action="{{ route('admin.farmers.destroy', $farmer) }}" 
                                              method="POST" 
                                              onsubmit="return confirm('Archive this farmer account? The account will be hidden and the farmer will no longer be able to sign in.');"
                                              class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-amber-600 hover:text-amber-800 font-medium">
                                                Archive
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-8 text-center text-gray-500">
                                    <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                                    </svg>
                                    <p class="mb-3">No farmer accounts found</p>
                                    <a href="{{ route('admin.farmers.create') }}" class="text-green-600 hover:underline font-medium">Create first farmer account →</a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($farmers->hasPages())
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $farmers->links() }}
                </div>
            @endif
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const form = document.querySelector('[data-auto-filter-form]');

                if (!form) {
                    return;
                }

                const searchInput = form.querySelector('input[name="search"]');
                const filterControls = form.querySelectorAll('select');
                let submitTimer;
                let abortController;

                const getFilterUrl = () => {
                    const formData = new FormData(form);
                    const params = new URLSearchParams();

                    formData.forEach((value, key) => {
                        const normalizedValue = String(value).trim();

                        if (normalizedValue !== '') {
                            params.set(key, normalizedValue);
                        }
                    });

                    const queryString = params.toString();
                    return queryString ? `${form.action}?${queryString}` : form.action;
                };

                const replaceSection = (doc, selector) => {
                    const currentSection = document.querySelector(selector);
                    const updatedSection = doc.querySelector(selector);

                    if (currentSection && updatedSection) {
                        currentSection.outerHTML = updatedSection.outerHTML;
                    }
                };

                const submitFilters = async () => {
                    window.clearTimeout(submitTimer);

                    const targetUrl = getFilterUrl();

                    if (targetUrl !== window.location.href) {
                        window.history.replaceState({}, '', targetUrl);
                    }

                    abortController?.abort();
                    const activeController = new AbortController();
                    abortController = activeController;

                    document.querySelector('[data-farmer-results]')?.setAttribute('aria-busy', 'true');

                    try {
                        const response = await fetch(targetUrl, {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                            signal: activeController.signal,
                        });

                        if (!response.ok) {
                            window.location.href = targetUrl;
                            return;
                        }

                        const html = await response.text();
                        const doc = new DOMParser().parseFromString(html, 'text/html');

                        replaceSection(doc, '[data-filter-action-row]');
                        replaceSection(doc, '[data-farmer-results]');
                    } catch (error) {
                        if (error.name !== 'AbortError') {
                            window.location.href = targetUrl;
                        }
                    } finally {
                        if (abortController === activeController) {
                            document.querySelector('[data-farmer-results]')?.removeAttribute('aria-busy');
                        }
                    }
                };

                form.addEventListener('submit', (event) => {
                    event.preventDefault();
                    submitFilters();
                });

                searchInput?.addEventListener('input', () => {
                    window.clearTimeout(submitTimer);
                    submitTimer = window.setTimeout(submitFilters, 450);
                });

                filterControls.forEach((control) => {
                    control.addEventListener('change', submitFilters);
                });
            });
        </script>
    </div>
</x-admin-layout>
