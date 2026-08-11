<x-admin-layout>
    <x-slot name="title">Crop Statistics</x-slot>

    <div class="admin-feature-dataset-summary space-y-5 p-3 sm:p-6">
        <div class="admin-feature-page-header flex flex-col gap-4 border-b border-gray-200 pb-5 sm:flex-row sm:items-end sm:justify-between">
            <div class="pasya-text-safe max-w-2xl">
                <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-gray-500">Dataset coverage</p>
                <h1 class="mt-1 text-2xl font-semibold tracking-tight text-gray-950 sm:text-3xl">Production data summary</h1>
                <p class="mt-2 text-sm leading-6 text-gray-600">Check the distribution and coverage of imported source records before interpreting totals or forecasts.</p>
            </div>
            <a href="{{ route('admin.crop-data.index') }}"
               class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-800 transition hover:bg-gray-50">
                Back to records
            </a>
        </div>

        <div class="admin-feature-source-note rounded-xl border border-gray-200 bg-gray-50 p-4">
            <p class="text-sm font-semibold text-gray-900">Source: imported production dataset</p>
            <p class="mt-1 text-xs leading-5 text-gray-600">Record counts describe dataset coverage, not farmer participation. Verified farmer harvest reports are reviewed separately in official reporting.</p>
        </div>

        <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
            {{-- By Municipality --}}
            <div class="pasya-card-safe rounded-xl border border-gray-200 bg-white p-4 sm:p-6">
                <h2 class="mb-1 text-base font-semibold text-gray-900">Municipality coverage</h2>
                <p class="mb-4 text-xs text-gray-500">Number of imported rows per municipality</p>
                <div class="space-y-2 max-h-96 overflow-y-auto">
                    @forelse($stats['by_municipality'] as $item)
                        <div class="flex items-center justify-between gap-3 p-2 hover:bg-gray-50 rounded">
                            <span class="min-w-0 break-words text-sm font-medium text-gray-700">{{ ucwords(strtolower($item->municipality)) }}</span>
                            <span class="text-sm text-gray-600 bg-gray-100 px-3 py-1 rounded-full">{{ number_format($item->count) }}</span>
                        </div>
                    @empty
                        <p class="rounded-lg bg-gray-50 px-3 py-6 text-center text-sm text-gray-500">No municipality records imported.</p>
                    @endforelse
                </div>
            </div>

            {{-- By Crop --}}
            <div class="pasya-card-safe rounded-xl border border-gray-200 bg-white p-4 sm:p-6">
                <h2 class="mb-1 text-base font-semibold text-gray-900">Crop coverage</h2>
                <p class="mb-4 text-xs text-gray-500">Number of imported rows per crop type</p>
                <div class="space-y-2 max-h-96 overflow-y-auto">
                    @forelse($stats['by_crop'] as $item)
                        <div class="flex items-center justify-between gap-3 p-2 hover:bg-gray-50 rounded">
                            <span class="min-w-0 break-words text-sm font-medium text-gray-700">{{ ucwords(strtolower($item->crop)) }}</span>
                            <span class="text-sm text-gray-600 bg-green-100 px-3 py-1 rounded-full">{{ number_format($item->count) }}</span>
                        </div>
                    @empty
                        <p class="rounded-lg bg-gray-50 px-3 py-6 text-center text-sm text-gray-500">No crop records imported.</p>
                    @endforelse
                </div>
            </div>

            {{-- By Year --}}
            <div class="pasya-card-safe rounded-xl border border-gray-200 bg-white p-4 sm:p-6">
                <h2 class="mb-1 text-base font-semibold text-gray-900">Year coverage</h2>
                <p class="mb-4 text-xs text-gray-500">Imported record volume and production totals by year</p>
                <div class="space-y-2">
                    @forelse($stats['by_year'] as $item)
                        <div class="p-3 hover:bg-gray-50 rounded">
                            <div class="flex flex-wrap items-center justify-between gap-2 mb-1">
                                <span class="text-sm font-medium text-gray-700">{{ $item->year }}</span>
                                <span class="text-sm text-gray-600 bg-blue-100 px-3 py-1 rounded-full">{{ number_format($item->count) }} records</span>
                            </div>
                            <div class="text-xs text-gray-500">
                                Total production: {{ number_format($item->total_production, 2) }} MT
                            </div>
                        </div>
                    @empty
                        <p class="rounded-lg bg-gray-50 px-3 py-6 text-center text-sm text-gray-500">No yearly production records imported.</p>
                    @endforelse
                </div>
            </div>

            {{-- By Farm Type --}}
            <div class="pasya-card-safe rounded-xl border border-gray-200 bg-white p-4 sm:p-6">
                <h2 class="mb-1 text-base font-semibold text-gray-900">Farm-type coverage</h2>
                <p class="mb-4 text-xs text-gray-500">Record volume and mean productivity by farm type</p>
                <div class="space-y-2">
                    @forelse($stats['by_farm_type'] as $item)
                        <div class="p-3 hover:bg-gray-50 rounded">
                            <div class="flex flex-wrap items-center justify-between gap-2 mb-1">
                                <span class="text-sm font-medium text-gray-700">{{ $item->farm_type }}</span>
                                <span class="text-sm text-gray-600 bg-yellow-100 px-3 py-1 rounded-full">{{ number_format($item->count) }} records</span>
                            </div>
                            <div class="text-xs text-gray-500">
                                Average productivity: {{ number_format($item->avg_productivity, 2) }} MT/ha
                            </div>
                        </div>
                    @empty
                        <p class="rounded-lg bg-gray-50 px-3 py-6 text-center text-sm text-gray-500">No farm-type records imported.</p>
                    @endforelse
                </div>
            </div>
        </div>

    </div>
</x-admin-layout>
