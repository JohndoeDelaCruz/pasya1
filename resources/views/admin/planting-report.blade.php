<x-admin-layout>
    <x-slot name="title">Planting Report</x-slot>

    @php
        $hasRecords = $plantingRecords->total() > 0;
        $exportFilters = collect($filters)->filter(fn ($value) => filled($value))->all();
        $totalRecords = (int) ($summary['total_records'] ?? 0);
        $plantedRecords = (int) ($summary['planted_records'] ?? $summary['planned_records'] ?? 0);
        $damagedRecords = (int) ($summary['damaged_records'] ?? 0);
        $totalArea = (float) ($summary['total_area'] ?? 0);
        $damagedArea = min((float) ($summary['total_damaged_area'] ?? 0), $totalArea);
        $productiveArea = max(0, $totalArea - $damagedArea);
        $totalOriginalProduction = (float) ($summary['total_original_production'] ?? 0);
        $totalAdjustedProduction = (float) ($summary['total_predicted_production'] ?? 0);
        $totalActualHarvest = (float) ($summary['total_actual_harvest'] ?? 0);
        $totalHarvestVariance = (float) ($summary['total_harvest_variance'] ?? 0);
        $actualHarvestRecords = (int) ($summary['actual_harvest_records'] ?? 0);
        $totalProductionLoss = min((float) ($summary['total_production_loss'] ?? 0), max($totalOriginalProduction, 0));
        $percentOf = fn ($value, $total) => $total > 0 ? min(100, round(((float) $value / (float) $total) * 100, 1)) : 0;
        $otherRecords = max(0, $totalRecords - $plantedRecords - $damagedRecords);
        $statusLabels = ['Planted', 'Damaged'];
        $statusValues = [$plantedRecords, $damagedRecords];

        if ($otherRecords > 0) {
            $statusLabels[] = 'Other';
            $statusValues[] = $otherRecords;
        }

        $statusChartData = [
            'labels' => $statusLabels,
            'values' => $statusValues,
        ];
        $cropDistribution = collect($summary['crop_distribution'] ?? []);
        $topCropDistribution = $cropDistribution->take(4)->values();
        $remainingCropRecords = (int) $cropDistribution->skip(4)->sum('records');
        $visibleCropDistribution = $remainingCropRecords > 0
            ? $topCropDistribution->push([
                'label' => 'Other crops',
                'records' => $remainingCropRecords,
                'area' => (float) $cropDistribution->skip(4)->sum('area'),
                'production' => (float) $cropDistribution->skip(4)->sum('production'),
            ])
            : $topCropDistribution;
        $topCropRecordCount = max(1, (int) $visibleCropDistribution->max('records'));
        $cropChartData = [
            'labels' => $visibleCropDistribution->pluck('label')->values()->all(),
            'values' => $visibleCropDistribution->pluck('records')->values()->all(),
        ];
    @endphp

    <div class="min-h-full bg-gray-50">
        <div class="p-3 sm:p-6">
            @if(session('error'))
                <div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-800">
                    {{ session('error') }}
                </div>
            @endif

            <div class="mb-5 grid grid-cols-1 gap-4 xl:grid-cols-12" data-summary-cards>
                <section class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm sm:p-5 xl:col-span-4">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-[11px] font-semibold uppercase tracking-wide text-gray-500">Record Status</p>
                            <h2 class="mt-1 text-base font-semibold text-gray-900">Planting report</h2>
                        </div>
                        <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">
                            {{ number_format($totalRecords) }} total
                        </span>
                    </div>

                    <div class="mt-4 grid gap-4 sm:grid-cols-[minmax(0,12rem)_1fr] sm:items-center">
                        <div class="relative mx-auto h-44 w-44">
                            <canvas
                                id="planting-status-chart"
                                data-planting-status-chart
                                data-status-summary='@json($statusChartData)'
                                aria-label="Planting status distribution"
                                role="img"
                            ></canvas>
                            <div class="pointer-events-none absolute inset-0 flex flex-col items-center justify-center">
                                <span class="text-2xl font-bold leading-none text-gray-900">{{ number_format($totalRecords) }}</span>
                                <span class="mt-1 text-[11px] font-semibold uppercase tracking-wide text-gray-500">records</span>
                            </div>
                        </div>

                        <div class="space-y-3">
                            <div class="flex items-center justify-between gap-3 rounded-xl bg-emerald-50 px-3 py-2">
                                <div class="flex items-center gap-2">
                                    <span class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span>
                                    <span class="text-sm font-medium text-gray-700">Planted</span>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-bold text-gray-900">{{ number_format($plantedRecords) }}</p>
                                    <p class="text-[11px] text-gray-500">{{ $percentOf($plantedRecords, $totalRecords) }}%</p>
                                </div>
                            </div>
                            <div class="flex items-center justify-between gap-3 rounded-xl bg-orange-50 px-3 py-2">
                                <div class="flex items-center gap-2">
                                    <span class="h-2.5 w-2.5 rounded-full bg-orange-400"></span>
                                    <span class="text-sm font-medium text-gray-700">Damaged</span>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-bold text-gray-900">{{ number_format($damagedRecords) }}</p>
                                    <p class="text-[11px] text-gray-500">{{ $percentOf($damagedRecords, $totalRecords) }}%</p>
                                </div>
                            </div>
                            @if($otherRecords > 0)
                            <div class="flex items-center justify-between gap-3 rounded-xl bg-gray-50 px-3 py-2">
                                <div class="flex items-center gap-2">
                                    <span class="h-2.5 w-2.5 rounded-full bg-gray-400"></span>
                                    <span class="text-sm font-medium text-gray-700">Other</span>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-bold text-gray-900">{{ number_format($otherRecords) }}</p>
                                    <p class="text-[11px] text-gray-500">{{ $percentOf($otherRecords, $totalRecords) }}%</p>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </section>

                <section class="grid gap-4 md:grid-cols-2 xl:col-span-4 xl:grid-cols-1">
                    <div class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm sm:p-5">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-[11px] font-semibold uppercase tracking-wide text-gray-500">Total Area Planted</p>
                                <div class="mt-1 flex items-end gap-2">
                                    <p class="text-2xl font-bold leading-none text-gray-900">{{ number_format($totalArea, 2) }}</p>
                                    <p class="text-[11px] font-medium uppercase tracking-wide text-gray-500">ha</p>
                                </div>
                            </div>
                            <span class="rounded-full bg-green-50 px-3 py-1 text-xs font-semibold text-green-700">
                                {{ $percentOf($productiveArea, $totalArea) }}% area planted
                            </span>
                        </div>

                        <div class="mt-5">
                            <div class="flex h-3 overflow-hidden rounded-full bg-gray-100" aria-hidden="true">
                                <span class="bg-emerald-500" style="width: {{ $percentOf($productiveArea, $totalArea) }}%"></span>
                                <span class="bg-orange-400" style="width: {{ $percentOf($damagedArea, $totalArea) }}%"></span>
                            </div>
                            <div class="mt-4 grid grid-cols-2 gap-3">
                                <div class="rounded-xl bg-emerald-50 px-3 py-2">
                                    <p class="text-[11px] font-semibold uppercase tracking-wide text-emerald-700">Area Planted</p>
                                    <p class="mt-1 text-sm font-bold text-gray-900">{{ number_format($productiveArea, 2) }} ha</p>
                                </div>
                                <div class="rounded-xl bg-orange-50 px-3 py-2">
                                    <p class="text-[11px] font-semibold uppercase tracking-wide text-orange-700">Damaged</p>
                                    <p class="mt-1 text-sm font-bold text-gray-900">{{ number_format($damagedArea, 2) }} ha</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm sm:p-5">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-[11px] font-semibold uppercase tracking-wide text-gray-500">Predicted Harvest</p>
                                <div class="mt-1 flex items-end gap-2">
                                    <p class="text-2xl font-bold leading-none text-gray-900">{{ number_format($totalAdjustedProduction, 2) }}</p>
                                    <p class="text-[11px] font-medium uppercase tracking-wide text-gray-500">MT</p>
                                </div>
                            </div>
                            <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700">
                                {{ number_format($actualHarvestRecords) }} actual
                            </span>
                        </div>

                        <div class="mt-5">
                            <div class="grid grid-cols-2 gap-3">
                                <div class="rounded-xl bg-emerald-50 px-3 py-2">
                                    <p class="text-[11px] font-semibold uppercase tracking-wide text-emerald-700">Predicted</p>
                                    <p class="mt-1 text-sm font-bold text-gray-900">{{ number_format($totalAdjustedProduction, 2) }} MT</p>
                                </div>
                                <div class="rounded-xl bg-blue-50 px-3 py-2">
                                    <p class="text-[11px] font-semibold uppercase tracking-wide text-blue-700">Actual</p>
                                    <p class="mt-1 text-sm font-bold text-gray-900">{{ number_format($totalActualHarvest, 2) }} MT</p>
                                </div>
                            </div>
                            <p class="mt-3 text-xs text-gray-500">
                                Original estimate: {{ number_format($totalOriginalProduction, 2) }} MT.
                                Damage loss: {{ number_format($totalProductionLoss, 2) }} MT.
                                Actual variance: {{ number_format($totalHarvestVariance, 2) }} MT.
                            </p>
                        </div>
                    </div>
                </section>

                <section class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm sm:p-5 xl:col-span-4">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <p class="text-[11px] font-semibold uppercase tracking-wide text-gray-500">Crops Planted</p>
                            <h2 class="mt-1 text-base font-semibold text-gray-900">Crop records</h2>
                        </div>
                        <span class="inline-flex w-fit rounded-full bg-green-50 px-3 py-1 text-xs font-semibold text-green-700">
                            {{ number_format($cropDistribution->count()) }} crop type{{ $cropDistribution->count() === 1 ? '' : 's' }}
                        </span>
                    </div>

                    <div class="mt-4 grid gap-4 sm:grid-cols-[minmax(0,10rem)_1fr] sm:items-center">
                        <div class="relative mx-auto h-40 w-40">
                            <canvas
                                id="planting-crop-chart"
                                data-planting-crop-chart
                                data-crop-summary='@json($cropChartData)'
                                aria-label="Crop distribution by planting records"
                                role="img"
                            ></canvas>
                            <div class="pointer-events-none absolute inset-0 flex flex-col items-center justify-center text-center">
                                <span class="text-xl font-bold leading-none text-gray-900">{{ number_format($totalRecords) }}</span>
                                <span class="mt-1 text-[10px] font-semibold uppercase tracking-wide text-gray-500">crop records</span>
                            </div>
                        </div>

                        <div class="space-y-2">
                            @forelse ($visibleCropDistribution as $crop)
                                @php
                                    $cropRecords = (int) ($crop['records'] ?? 0);
                                    $cropArea = (float) ($crop['area'] ?? 0);
                                    $cropProduction = (float) ($crop['production'] ?? 0);
                                @endphp
                                <div class="rounded-xl bg-gray-50 px-3 py-2">
                                    <div class="flex items-center justify-between gap-3">
                                        <p class="min-w-0 truncate text-sm font-semibold text-gray-800">{{ $crop['label'] }}</p>
                                        <p class="shrink-0 text-sm font-bold text-gray-900">{{ number_format($cropRecords) }}</p>
                                    </div>
                                    <div class="mt-1 h-2 overflow-hidden rounded-full bg-gray-100" aria-hidden="true">
                                        <div class="h-full rounded-full bg-emerald-500" style="width: {{ $percentOf($cropRecords, $topCropRecordCount) }}%"></div>
                                    </div>
                                    <p class="mt-1 truncate text-xs text-gray-500">{{ number_format($cropArea, 2) }} ha, {{ number_format($cropProduction, 2) }} MT</p>
                                </div>
                            @empty
                                <div class="rounded-xl border border-dashed border-gray-200 bg-gray-50 px-4 py-6 text-center">
                                    <p class="text-sm font-semibold text-gray-700">No crops to visualize</p>
                                    <p class="mt-1 text-xs text-gray-500">Matching planting records will appear here.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </section>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 mb-6">
                <form
                    method="GET"
                    action="{{ route('admin.planting-report') }}"
                    class="p-4 sm:p-5 grid grid-cols-1 md:grid-cols-2 xl:grid-cols-6 gap-4"
                    data-auto-filter-form
                >
                    <div class="xl:col-span-2">
                        <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                        <input
                            type="text"
                            id="search"
                            name="search"
                            value="{{ $filters['search'] ?? '' }}"
                            placeholder="Farmer name, ID, crop, municipality"
                            class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm text-gray-900 focus:border-green-500 focus:ring-green-500"
                        >
                    </div>

                    <div>
                        <label for="crop_type" class="block text-sm font-medium text-gray-700 mb-1">Crop Type</label>
                        <select
                            id="crop_type"
                            name="crop_type"
                            class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm text-gray-900 focus:border-green-500 focus:ring-green-500"
                        >
                            <option value="">All crops</option>
                            @foreach ($cropTypes as $cropType)
                                <option value="{{ $cropType }}" @selected(($filters['crop_type'] ?? '') === $cropType)>
                                    {{ ucwords(strtolower($cropType)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="municipality" class="block text-sm font-medium text-gray-700 mb-1">Municipality</label>
                        <select
                            id="municipality"
                            name="municipality"
                            class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm text-gray-900 focus:border-green-500 focus:ring-green-500"
                        >
                            <option value="">All municipalities</option>
                            @foreach ($municipalities as $municipality)
                                <option value="{{ $municipality }}" @selected(($filters['municipality'] ?? '') === $municipality)>
                                    {{ ucwords(strtolower($municipality)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="farm_type" class="block text-sm font-medium text-gray-700 mb-1">Farm Type</label>
                        <select
                            id="farm_type"
                            name="farm_type"
                            class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm text-gray-900 focus:border-green-500 focus:ring-green-500"
                        >
                            <option value="">All farm types</option>
                            @foreach ($farmTypes as $farmType)
                                <option value="{{ $farmType }}" @selected(($filters['farm_type'] ?? '') === $farmType)>
                                    {{ ucfirst(strtolower($farmType)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select
                            id="status"
                            name="status"
                            class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm text-gray-900 focus:border-green-500 focus:ring-green-500"
                        >
                            <option value="">All statuses</option>
                            @foreach ($statuses as $status)
                                <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>
                                    {{ ucfirst($status) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="validation_status" class="block text-sm font-medium text-gray-700 mb-1">LGU Validation</label>
                        <select
                            id="validation_status"
                            name="validation_status"
                            class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm text-gray-900 focus:border-green-500 focus:ring-green-500"
                        >
                            @foreach ($validationStatuses as $value => $label)
                                <option value="{{ $value }}" @selected(($filters['validation_status'] ?? 'approved') === $value)>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="planting_month" class="block text-sm font-medium text-gray-700 mb-1">Planting Month</label>
                        <select
                            id="planting_month"
                            name="planting_month"
                            class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm text-gray-900 focus:border-green-500 focus:ring-green-500"
                        >
                            <option value="">Any month</option>
                            @foreach ($months as $month)
                                <option value="{{ $month['value'] }}" @selected((string) ($filters['planting_month'] ?? '') === (string) $month['value'])>
                                    {{ $month['label'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="planting_year" class="block text-sm font-medium text-gray-700 mb-1">Planting Year</label>
                        <select
                            id="planting_year"
                            name="planting_year"
                            class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm text-gray-900 focus:border-green-500 focus:ring-green-500"
                        >
                            <option value="">Any year</option>
                            @foreach ($plantingYears as $year)
                                <option value="{{ $year }}" @selected((string) ($filters['planting_year'] ?? '') === (string) $year)>
                                    {{ $year }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="harvest_month" class="block text-sm font-medium text-gray-700 mb-1">Harvest Month</label>
                        <select
                            id="harvest_month"
                            name="harvest_month"
                            class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm text-gray-900 focus:border-green-500 focus:ring-green-500"
                        >
                            <option value="">Any month</option>
                            @foreach ($months as $month)
                                <option value="{{ $month['value'] }}" @selected((string) ($filters['harvest_month'] ?? '') === (string) $month['value'])>
                                    {{ $month['label'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="harvest_year" class="block text-sm font-medium text-gray-700 mb-1">Harvest Year</label>
                        <select
                            id="harvest_year"
                            name="harvest_year"
                            class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm text-gray-900 focus:border-green-500 focus:ring-green-500"
                        >
                            <option value="">Any year</option>
                            @foreach ($harvestYears as $year)
                                <option value="{{ $year }}" @selected((string) ($filters['harvest_year'] ?? '') === (string) $year)>
                                    {{ $year }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="md:col-span-2 xl:col-span-6 flex flex-wrap items-center gap-3">
                        <button type="submit" class="inline-flex flex-1 items-center justify-center rounded-xl bg-green-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-green-700 sm:flex-none">
                            Apply Filters
                        </button>
                        <a href="{{ route('admin.planting-report') }}" class="inline-flex flex-1 items-center justify-center rounded-xl border border-gray-200 px-5 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50 sm:flex-none">
                            Reset
                        </a>

                        <div class="flex w-full flex-wrap items-center gap-3 sm:ml-auto sm:w-auto" data-export-actions>
                            @if ($hasRecords)
                                <a href="{{ route('admin.planting-report.export.csv', $exportFilters) }}" data-no-page-loader class="inline-flex flex-1 items-center justify-center rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-2.5 text-sm font-semibold text-emerald-700 hover:bg-emerald-100 sm:flex-none">
                                    Export CSV
                                </a>
                                <a href="{{ route('admin.planting-report.export.pdf', $exportFilters) }}" data-no-page-loader class="inline-flex flex-1 items-center justify-center rounded-xl border border-gray-300 bg-white px-5 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50 sm:flex-none">
                                    Export PDF
                                </a>
                            @else
                                <span class="inline-flex flex-1 cursor-not-allowed items-center justify-center rounded-xl border border-gray-200 bg-gray-100 px-5 py-2.5 text-sm font-semibold text-gray-400 sm:flex-none">
                                    Export CSV
                                </span>
                                <span class="inline-flex flex-1 cursor-not-allowed items-center justify-center rounded-xl border border-gray-200 bg-gray-100 px-5 py-2.5 text-sm font-semibold text-gray-400 sm:flex-none">
                                    Export PDF
                                </span>
                            @endif
                        </div>
                    </div>
                </form>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden" data-report-results>
                <div class="px-4 sm:px-6 py-4 border-b border-gray-100 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div class="pasya-text-safe">
                        <h2 class="text-lg font-semibold text-gray-900">Planting Records</h2>
                        <p class="text-sm text-gray-500">Defaults to LGU-approved crop plans submitted from the farmer calendar.</p>
                    </div>
                    <p class="text-sm text-gray-500">{{ $plantingRecords->total() }} record{{ $plantingRecords->total() === 1 ? '' : 's' }}</p>
                </div>

                @if ($plantingRecords->isEmpty())
                    <div class="p-10 text-center">
                        <div class="w-14 h-14 mx-auto mb-4 rounded-full bg-green-100 flex items-center justify-center">
                            <svg class="w-7 h-7 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V9.414A2 2 0 0013.414 8L9 3.586A2 2 0 007.586 3H4zm5 1.414L12.586 8H10a1 1 0 01-1-1V4.414zM6 10a1 1 0 011-1h4a1 1 0 110 2H7a1 1 0 01-1-1zm0 3a1 1 0 011-1h4a1 1 0 110 2H7a1 1 0 01-1-1z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900">No planting records found</h3>
                        <p class="mt-1 text-sm text-gray-500">Farmer calendar submissions will appear here once crop plans are added.</p>
                    </div>
                @else
                    <div class="pasya-scroll-table overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 sm:px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Farmer Details</th>
                                    <th class="px-4 sm:px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Crop Plan</th>
                                    <th class="px-4 sm:px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Planting</th>
                                    <th class="px-4 sm:px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Harvest</th>
                                    <th class="px-4 sm:px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Area &amp; Yield</th>
                                    <th class="px-4 sm:px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Farm Type</th>
                                    <th class="px-4 sm:px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Status</th>
                                    <th class="px-4 sm:px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Recorded</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                @foreach ($plantingRecords as $record)
                                    @php
                                        $farmer = $record->farmer;
                                        $displayStatus = $record->planting_report_status;
                                        $statusClasses = match ($displayStatus) {
                                            'planted' => 'bg-blue-100 text-blue-800',
                                            'growing' => 'bg-emerald-100 text-emerald-800',
                                            'damaged' => 'bg-orange-100 text-orange-800',
                                            'harvested' => 'bg-green-100 text-green-800',
                                            'cancelled' => 'bg-red-100 text-red-800',
                                            default => 'bg-gray-100 text-gray-700',
                                        };
                                        $validationClasses = match ($record->lgu_validation_status) {
                                            'approved' => 'bg-green-100 text-green-800',
                                            'pending' => 'bg-amber-100 text-amber-800',
                                            'rejected' => 'bg-red-100 text-red-800',
                                            default => 'bg-gray-100 text-gray-700',
                                        };
                                    @endphp
                                    <tr class="align-top hover:bg-gray-50/70">
                                        <td class="px-4 sm:px-6 py-4 text-sm text-gray-700">
                                            <p class="font-semibold text-gray-900">{{ $farmer?->full_name ?? 'Farmer record unavailable' }}</p>
                                            <p class="mt-1 text-xs text-gray-500">Farmer ID: {{ $farmer?->farmer_id ?? 'N/A' }}</p>
                                            <p class="mt-1 text-xs text-gray-500">Municipality: {{ ucwords(strtolower($record->municipality ?? $farmer?->municipality ?? 'N/A')) }}</p>
                                            <p class="mt-1 text-xs text-gray-500">Cooperative: {{ $farmer?->cooperative_display ?? 'N/A' }}</p>
                                            @if ($farmer?->trashed())
                                                <span class="mt-2 inline-flex rounded-full bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-600">Archived farmer</span>
                                            @endif
                                        </td>
                                        <td class="px-4 sm:px-6 py-4 text-sm text-gray-700">
                                            <p class="font-semibold text-gray-900">{{ $record->crop_name }}</p>
                                            <p class="mt-1 text-xs text-gray-500">Notes: {{ $record->notes ? \Illuminate\Support\Str::limit($record->notes, 70) : 'None' }}</p>
                                        </td>
                                        <td class="px-4 sm:px-6 py-4 text-sm font-medium text-gray-900 whitespace-nowrap">
                                            {{ $record->planting_date->format('M d, Y') }}
                                        </td>
                                        <td class="px-4 sm:px-6 py-4 text-sm font-medium text-gray-900 whitespace-nowrap">
                                            {{ $record->expected_harvest_date->format('M d, Y') }}
                                        </td>
                                        <td class="px-4 sm:px-6 py-4 text-sm text-gray-700">
                                            <p class="font-medium text-gray-900">{{ number_format((float) $record->area_hectares, 2) }} ha</p>
                                            <p class="mt-1 text-xs text-gray-500">Original estimate: {{ number_format((float) $record->predicted_production, 2) }} MT</p>
                                            <p class="mt-1 text-xs text-gray-500">Predicted harvest: {{ number_format((float) $record->adjusted_predicted_production, 2) }} MT</p>
                                            <p class="mt-1 text-xs text-gray-500">
                                                Actual harvest:
                                                @if($record->actual_harvest_production_mt !== null)
                                                    {{ number_format((float) $record->actual_harvest_production_mt, 4) }} MT
                                                @else
                                                    Not reported
                                                @endif
                                            </p>
                                            @if($record->actual_harvest_variance_mt !== null)
                                                <p class="mt-1 text-xs {{ $record->actual_harvest_variance_mt < 0 ? 'text-red-700' : 'text-green-700' }}">
                                                    Variance: {{ number_format((float) $record->actual_harvest_variance_mt, 4) }} MT
                                                </p>
                                            @endif
                                            @if ($record->has_damage_report)
                                                <p class="mt-1 text-xs text-orange-700">Damage: {{ number_format((float) $record->damaged_area_hectares, 2) }} ha affected, {{ number_format((float) $record->production_loss_mt, 2) }} MT lost</p>
                                            @endif
                                        </td>
                                        <td class="px-4 sm:px-6 py-4 text-sm text-gray-700">
                                            <p>{{ ucfirst(strtolower($record->farm_type)) }}</p>
                                            <p class="mt-1 text-xs text-gray-500">{{ $record->planting_material_label ?? 'Planting material not set' }}</p>
                                        </td>
                                        <td class="px-4 sm:px-6 py-4 text-sm text-gray-700">
                                            <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold uppercase {{ $statusClasses }}">
                                                {{ $displayStatus }}
                                            </span>
                                            <span class="mt-2 inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $validationClasses }}">
                                                {{ $record->lgu_validation_status_label }}
                                            </span>
                                            @if ($record->lgu_validated_at || $record->lguValidator)
                                                <p class="mt-2 text-xs text-gray-500">
                                                    Validator: {{ $record->lguValidator?->name ?? 'LGU account unavailable' }}
                                                </p>
                                                <p class="mt-1 text-xs text-gray-500">
                                                    Reviewed: {{ $record->lgu_validated_at?->format('M d, Y h:i A') ?? 'Pending review' }}
                                                </p>
                                            @endif
                                            @if ($record->lgu_validation_notes)
                                                <p class="mt-1 text-xs text-gray-500">LGU note: {{ \Illuminate\Support\Str::limit($record->lgu_validation_notes, 80) }}</p>
                                            @endif
                                            @if ($record->actual_harvest_production_mt !== null)
                                                <p class="mt-2 text-xs font-medium text-blue-700">Actual harvest approved</p>
                                                <p class="mt-1 text-xs text-gray-500">Actual date: {{ $record->actual_harvest_date?->format('M d, Y') ?? 'Date unavailable' }}</p>
                                                <p class="mt-1 text-xs text-gray-500">Harvest validator: {{ $record->actualHarvestReport?->lguValidator?->name ?? 'LGU account unavailable' }}</p>
                                            @elseif ($record->actualHarvestReport)
                                                <p class="mt-2 text-xs font-medium text-amber-700">Actual harvest: {{ $record->actualHarvestReport->lgu_validation_status_label }}</p>
                                            @else
                                                <p class="mt-2 text-xs text-gray-500">Actual harvest: Not reported</p>
                                            @endif
                                            @if ($record->has_damage_report)
                                                <p class="mt-2 text-xs font-medium text-orange-700">{{ $record->damage_cause_label ?? 'Damage reported' }}</p>
                                                <p class="mt-1 text-xs text-gray-500">Date damaged: {{ $record->damage_occurred_on?->format('M d, Y') ?? 'Date unavailable' }}</p>
                                                <p class="mt-1 text-xs text-gray-500">Reported: {{ $record->damage_reported_at?->format('M d, Y h:i A') ?? 'Report time unavailable' }}</p>
                                                <p class="mt-1 text-xs text-gray-500">{{ $record->damage_notes ? \Illuminate\Support\Str::limit($record->damage_notes, 80) : 'No additional notes' }}</p>
                                            @endif
                                        </td>
                                        <td class="px-4 sm:px-6 py-4 text-sm text-gray-700">
                                            <p>{{ $record->created_at->format('M d, Y') }}</p>
                                            <p class="mt-1 text-xs text-gray-500">{{ $record->created_at->format('h:i A') }}</p>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="px-4 sm:px-6 py-4 border-t border-gray-100">
                        {{ $plantingRecords->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            let plantingStatusChart = null;
            let plantingCropChart = null;

            const destroyPlantingStatusChart = () => {
                if (plantingStatusChart) {
                    plantingStatusChart.destroy();
                    plantingStatusChart = null;
                }
            };

            const destroyPlantingCropChart = () => {
                if (plantingCropChart) {
                    plantingCropChart.destroy();
                    plantingCropChart = null;
                }
            };

            const destroySummaryCharts = () => {
                destroyPlantingStatusChart();
                destroyPlantingCropChart();
            };

            const initPlantingStatusChart = () => {
                const canvas = document.querySelector('[data-planting-status-chart]');

                destroyPlantingStatusChart();

                if (!canvas || typeof Chart === 'undefined') {
                    return;
                }

                let statusSummary = { labels: ['Planted', 'Damaged'], values: [0, 0] };

                try {
                    statusSummary = JSON.parse(canvas.dataset.statusSummary || '{}');
                } catch (error) {
                    statusSummary = { labels: ['Planted', 'Damaged'], values: [0, 0] };
                }

                const labels = Array.isArray(statusSummary.labels) ? statusSummary.labels : ['Planted', 'Damaged'];
                const values = Array.isArray(statusSummary.values)
                    ? statusSummary.values.map(value => Number(value) || 0)
                    : [0, 0];
                const hasData = values.some(value => value > 0);
                const chartValues = hasData ? values : [1];
                const chartLabels = hasData ? labels : ['No records'];
                const baseColors = ['#10b981', '#fb923c', '#6b7280'];
                const chartColors = hasData ? baseColors.slice(0, chartValues.length) : ['#e5e7eb'];

                plantingStatusChart = new Chart(canvas, {
                    type: 'doughnut',
                    data: {
                        labels: chartLabels,
                        datasets: [{
                            data: chartValues,
                            backgroundColor: chartColors,
                            borderColor: '#ffffff',
                            borderWidth: 3,
                            hoverOffset: hasData ? 5 : 0,
                        }],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '70%',
                        plugins: {
                            legend: {
                                display: false,
                            },
                            tooltip: {
                                enabled: hasData,
                                callbacks: {
                                    label(context) {
                                        const total = values.reduce((sum, value) => sum + value, 0);
                                        const value = values[context.dataIndex] || 0;
                                        const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : '0.0';

                                        return `${labels[context.dataIndex]}: ${value.toLocaleString()} (${percentage}%)`;
                                    },
                                },
                            },
                        },
                    },
                });
            };

            const initPlantingCropChart = () => {
                const canvas = document.querySelector('[data-planting-crop-chart]');

                destroyPlantingCropChart();

                if (!canvas || typeof Chart === 'undefined') {
                    return;
                }

                let cropSummary = { labels: [], values: [] };

                try {
                    cropSummary = JSON.parse(canvas.dataset.cropSummary || '{}');
                } catch (error) {
                    cropSummary = { labels: [], values: [] };
                }

                const labels = Array.isArray(cropSummary.labels) ? cropSummary.labels : [];
                const values = Array.isArray(cropSummary.values)
                    ? cropSummary.values.map(value => Number(value) || 0)
                    : [];
                const hasData = values.some(value => value > 0);
                const chartLabels = hasData ? labels : ['No crops'];
                const chartValues = hasData ? values : [1];
                const chartColors = hasData
                    ? ['#10b981', '#0ea5e9', '#f59e0b', '#8b5cf6', '#06b6d4', '#fb923c', '#9ca3af']
                    : ['#e5e7eb'];

                plantingCropChart = new Chart(canvas, {
                    type: 'doughnut',
                    data: {
                        labels: chartLabels,
                        datasets: [{
                            label: 'Planting records',
                            data: chartValues,
                            backgroundColor: chartColors.slice(0, chartValues.length),
                            borderColor: '#ffffff',
                            borderWidth: 3,
                            hoverOffset: hasData ? 5 : 0,
                        }],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '64%',
                        plugins: {
                            legend: {
                                display: false,
                            },
                            tooltip: {
                                enabled: hasData,
                                callbacks: {
                                    label(context) {
                                        const total = chartValues.reduce((sum, value) => sum + value, 0);
                                        const value = chartValues[context.dataIndex] || 0;
                                        const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : '0.0';

                                        return `${chartLabels[context.dataIndex]}: ${value.toLocaleString()} record${value === 1 ? '' : 's'} (${percentage}%)`;
                                    },
                                },
                            },
                        },
                    },
                });
            };

            const initSummaryCharts = () => {
                initPlantingStatusChart();
                initPlantingCropChart();
            };

            initSummaryCharts();

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

                document.querySelector('[data-report-results]')?.setAttribute('aria-busy', 'true');

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

                    destroySummaryCharts();
                    replaceSection(doc, '[data-summary-cards]');
                    initSummaryCharts();
                    replaceSection(doc, '[data-export-actions]');
                    replaceSection(doc, '[data-report-results]');
                } catch (error) {
                    if (error.name !== 'AbortError') {
                        window.location.href = targetUrl;
                    }
                } finally {
                    if (abortController === activeController) {
                        document.querySelector('[data-report-results]')?.removeAttribute('aria-busy');
                    }
                }
            };

            searchInput?.addEventListener('input', () => {
                window.clearTimeout(submitTimer);
                submitTimer = window.setTimeout(submitFilters, 450);
            });

            filterControls.forEach((control) => {
                control.addEventListener('change', submitFilters);
            });
        });
    </script>
</x-admin-layout>
