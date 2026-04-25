<x-admin-layout>
    <x-slot name="title">Archived Crop Production Data</x-slot>

    <div class="p-6 space-y-6">

        {{-- Header --}}
        <div class="bg-gradient-to-r from-amber-600 to-amber-700 rounded-xl shadow-lg p-5 text-white">
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-bold mb-1">Archived Crop Production Data</h1>
                    <p class="text-amber-100 text-sm sm:text-base">Records that have been archived. Restore or permanently delete them.</p>
                </div>
                <a href="{{ route('admin.crop-data.index') }}"
                   class="self-start sm:self-auto bg-white text-amber-700 hover:bg-amber-50 font-semibold py-2.5 px-5 rounded-lg transition-all duration-200 shadow-md flex items-center text-sm whitespace-nowrap">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Back to Active
                </a>
            </div>
        </div>

        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded-r-lg shadow-sm">
                <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
            </div>
        @endif
        @if(session('error'))
            <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-r-lg shadow-sm">
                <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
            </div>
        @endif

        {{-- Stats --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-white rounded-xl shadow p-5 border-l-4 border-amber-400">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Archived Records</p>
                <p class="text-3xl font-bold text-gray-900 mt-1">{{ number_format($stats['archived_records']) }}</p>
            </div>
            <div class="bg-white rounded-xl shadow p-5 border-l-4 border-green-400">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Active Records</p>
                <p class="text-3xl font-bold text-gray-900 mt-1">{{ number_format($stats['active_records']) }}</p>
            </div>
            <div class="bg-white rounded-xl shadow p-5 border-l-4 border-blue-400">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Archived Municipalities</p>
                <p class="text-3xl font-bold text-gray-900 mt-1">{{ $stats['archived_municipalities'] }}</p>
            </div>
            <div class="bg-white rounded-xl shadow p-5 border-l-4 border-purple-400">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Archived Crop Types</p>
                <p class="text-3xl font-bold text-gray-900 mt-1">{{ $stats['archived_crops'] }}</p>
            </div>
        </div>

        {{-- Filters --}}
        <div class="bg-white rounded-xl shadow p-4">
            <form method="GET" action="{{ route('admin.crop-data.archived') }}" class="flex flex-wrap gap-3 items-end">
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                    <input type="text"
                           name="search"
                           value="{{ request('search') }}"
                           placeholder="Crop, municipality, year, month..."
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-500">
                </div>

                <div class="min-w-[180px]">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Municipality</label>
                    <select name="municipality" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-500">
                        <option value="">All Municipalities</option>
                        @foreach($filters['municipalities'] as $m)
                            <option value="{{ $m }}" {{ request('municipality') == $m ? 'selected' : '' }}>
                                {{ ucwords(strtolower($m)) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="min-w-[180px]">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Crop</label>
                    <select name="crop" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-500">
                        <option value="">All Crops</option>
                        @foreach($filters['crops'] as $c)
                            <option value="{{ $c }}" {{ request('crop') == $c ? 'selected' : '' }}>
                                {{ ucwords(strtolower($c)) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <button type="submit"
                        class="bg-amber-600 hover:bg-amber-700 text-white font-semibold py-2 px-5 rounded-lg transition flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                    </svg>
                    Filter
                </button>

                @if(request()->hasAny(['search', 'municipality', 'crop']))
                    <a href="{{ route('admin.crop-data.archived') }}"
                       class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold py-2 px-5 rounded-lg transition flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        Clear
                    </a>
                @endif
            </form>
        </div>

        {{-- Table --}}
        <div class="bg-white rounded-xl shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-amber-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Municipality</th>
                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Crop</th>
                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Farm Type</th>
                            <th class="px-4 py-3 text-center text-xs font-bold text-gray-600 uppercase tracking-wider">Year</th>
                            <th class="px-4 py-3 text-center text-xs font-bold text-gray-600 uppercase tracking-wider">Month</th>
                            <th class="px-4 py-3 text-right text-xs font-bold text-gray-600 uppercase tracking-wider">Area Harvested (ha)</th>
                            <th class="px-4 py-3 text-right text-xs font-bold text-gray-600 uppercase tracking-wider">Production (mt)</th>
                            <th class="px-4 py-3 text-right text-xs font-bold text-gray-600 uppercase tracking-wider">Productivity (mt/ha)</th>
                            <th class="px-4 py-3 text-center text-xs font-bold text-gray-600 uppercase tracking-wider">Archived</th>
                            <th class="px-4 py-3 text-center text-xs font-bold text-gray-600 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($crops as $crop)
                            <tr class="bg-amber-50/20 hover:bg-amber-50 transition-colors duration-150">
                                <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-700">{{ $crop->municipality_display }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600">{{ $crop->crop_display }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600">{{ $crop->farm_type_display }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600 text-center">{{ $crop->year }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600 text-center">{{ $crop->month }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600 text-right">{{ number_format($crop->area_harvested, 2) }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600 text-right">{{ number_format($crop->production, 2) }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600 text-right">{{ number_format($crop->productivity, 2) }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-xs text-gray-400 text-center">
                                    {{ $crop->deleted_at ? $crop->deleted_at->format('M d, Y') : '—' }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        {{-- Restore --}}
                                        <form action="{{ route('admin.crop-data.restore', $crop->id) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit"
                                                    title="Restore"
                                                    class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-semibold text-green-700 bg-green-100 hover:bg-green-200 rounded-lg transition-colors duration-150">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                                </svg>
                                                Restore
                                            </button>
                                        </form>
                                        {{-- Permanent Delete --}}
                                        <form action="{{ route('admin.crop-data.force-delete', $crop->id) }}" method="POST" class="inline"
                                              onsubmit="return confirm('Permanently delete this record for {{ addslashes($crop->crop_display) }} ({{ $crop->municipality_display }}, {{ $crop->year }})? This cannot be undone.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    title="Delete permanently"
                                                    class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-semibold text-red-700 bg-red-100 hover:bg-red-200 rounded-lg transition-colors duration-150">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="px-4 py-12 text-center">
                                    <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                                    </svg>
                                    <p class="text-gray-500">No archived crop records found.</p>
                                    @if(request()->hasAny(['search', 'municipality', 'crop']))
                                        <a href="{{ route('admin.crop-data.archived') }}" class="mt-2 inline-block text-amber-600 hover:underline text-sm">Clear filters</a>
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($crops->hasPages())
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $crops->links() }}
                </div>
            @endif
        </div>

    </div>
</x-admin-layout>
