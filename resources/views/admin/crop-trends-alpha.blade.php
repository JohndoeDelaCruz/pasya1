<x-admin-layout>
    <x-slot name="title">Crop Trends & Patterns Alpha Test</x-slot>

    @php
        $actualTotal = collect($actualData)->filter(fn ($value) => $value !== null)->sum();
        $predictedTotal = collect($predictedData)->filter(fn ($value) => $value !== null)->sum();
        $varianceTotal = $actualTotal > 0 && $predictedTotal > 0 ? $actualTotal - $predictedTotal : null;
    @endphp

    <div class="space-y-6" x-data="cropTrendsAlpha()">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-2">
                    <h1 class="text-2xl font-bold text-gray-900">Crop Trends & Patterns</h1>
                    <span class="rounded-full border border-amber-200 bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700">Alpha Test</span>
                </div>
                <p class="mt-1 text-sm text-gray-500">
                    Actual farmer harvests compared with ML forecasts from {{ $start->format('M Y') }} to {{ $end->format('M Y') }}.
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                @if($mlApiHealthy === true)
                    <span class="rounded-full border border-green-200 bg-green-50 px-3 py-1.5 text-xs font-semibold text-green-700">ML active</span>
                @elseif($mlApiHealthy === false)
                    <span class="rounded-full border border-red-200 bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-700">ML unavailable</span>
                @else
                    <span class="rounded-full border border-amber-200 bg-amber-50 px-3 py-1.5 text-xs font-semibold text-amber-700">ML gated</span>
                @endif
                <span class="rounded-full border border-blue-200 bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-700">
                    {{ number_format($coverage['percentage'], 2) }}% participation
                </span>
            </div>
        </div>

        <form method="GET" action="{{ route('admin.crop-trends-alpha') }}" class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm sm:p-5" x-data>
            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <div>
                    <label for="municipality" class="block text-sm font-medium text-gray-700">Municipality</label>
                    <select id="municipality" name="municipality" class="mt-1 w-full rounded-xl border-gray-200 text-sm focus:border-green-500 focus:ring-green-500" @change="$el.form.requestSubmit()">
                        @foreach($municipalities as $municipality)
                            <option value="{{ $municipality }}" @selected(\App\Models\Municipality::normalizeLocationName($municipality) === $selectedMunicipality)>
                                {{ ucwords(strtolower($municipality)) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="crop" class="block text-sm font-medium text-gray-700">Crop</label>
                    <select id="crop" name="crop" class="mt-1 w-full rounded-xl border-gray-200 text-sm focus:border-green-500 focus:ring-green-500" @change="$el.form.requestSubmit()">
                        @foreach($crops as $crop)
                            <option value="{{ $crop }}" @selected(strtoupper($crop) === strtoupper($selectedCrop))>
                                {{ ucwords(strtolower($crop)) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="farm_type" class="block text-sm font-medium text-gray-700">Farm Type</label>
                    <select id="farm_type" name="farm_type" class="mt-1 w-full rounded-xl border-gray-200 text-sm focus:border-green-500 focus:ring-green-500" @change="$el.form.requestSubmit()">
                        @foreach($farmTypes as $farmType)
                            <option value="{{ $farmType }}" @selected(strtoupper(str_replace(' ', '', $farmType)) === $selectedFarmType)>
                                {{ ucfirst(strtolower($farmType)) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-end gap-2">
                    <a href="{{ route('admin.crop-trends-alpha') }}" class="inline-flex w-full items-center justify-center rounded-xl border border-gray-200 px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50">Reset</a>
                </div>
            </div>
        </form>

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <section class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Registered Farmers</p>
                <p class="mt-2 text-2xl font-bold text-gray-900">{{ number_format($coverage['registered_farmers']) }}</p>
                <p class="mt-1 text-xs text-gray-500">Selected municipality scope</p>
            </section>
            <section class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Participating Farmers</p>
                <p class="mt-2 text-2xl font-bold text-gray-900">{{ number_format($coverage['participating_farmers']) }}</p>
                <p class="mt-1 text-xs text-gray-500">With approved relevant crop records</p>
            </section>
            <section class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Actual Harvest</p>
                <p class="mt-2 text-2xl font-bold text-blue-700">{{ number_format($actualTotal, 2) }} MT</p>
                <p class="mt-1 text-xs text-gray-500">LGU-approved farmer actuals</p>
            </section>
            <section class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Predicted Harvest</p>
                <p class="mt-2 text-2xl font-bold text-green-700">{{ number_format($predictedTotal, 2) }} MT</p>
                <p class="mt-1 text-xs text-gray-500">{{ $coverage['can_predict'] ? 'ML forecasted periods' : 'Blocked below 10% coverage' }}</p>
            </section>
        </div>

        @unless($coverage['can_predict'])
            <section class="rounded-2xl border border-amber-200 bg-amber-50 p-5 shadow-sm">
                <h2 class="text-lg font-semibold text-amber-900">Insufficient farmer participation</h2>
                <p class="mt-1 text-sm text-amber-800">
                    Predictions are disabled until at least {{ $coverage['threshold'] }}% of active registered farmers in this scope have relevant LGU-approved crop or harvest records.
                </p>
            </section>
        @endunless

        <section class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm sm:p-5">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">Actual vs Predicted Production</h2>
                    <p class="mt-1 text-sm text-gray-500">
                        {{ ucwords(strtolower($selectedCrop)) }} in {{ ucwords(strtolower($selectedMunicipality)) }} - {{ ucfirst(strtolower($selectedFarmType)) }}
                    </p>
                </div>
                @if($varianceTotal !== null)
                    <span class="w-fit rounded-full border border-gray-200 bg-gray-50 px-3 py-1 text-xs font-semibold text-gray-700">
                        Variance total: {{ number_format($varianceTotal, 2) }} MT
                    </span>
                @endif
            </div>

            <div class="mt-5 grid gap-6 xl:grid-cols-2">
                <div class="min-w-0">
                    <h3 class="mb-3 text-sm font-semibold uppercase text-gray-700">Actual Harvest</h3>
                    <div class="h-[300px] sm:h-[360px]">
                        <canvas id="alphaActualChart"></canvas>
                    </div>
                </div>
                <div class="min-w-0 border-t border-gray-100 pt-5 xl:border-l xl:border-t-0 xl:pl-6 xl:pt-0">
                    <h3 class="mb-3 text-sm font-semibold uppercase text-gray-700">Predicted Harvest</h3>
                    <div class="h-[300px] sm:h-[360px]">
                        <canvas id="alphaPredictedChart"></canvas>
                    </div>
                </div>
            </div>
        </section>

        <section class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">
            <div class="border-b border-gray-100 px-4 py-4 sm:px-5">
                <h2 class="text-lg font-semibold text-gray-900">Monthly Details</h2>
                <p class="mt-1 text-sm text-gray-500">Actual values are official only after LGU approval.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Month</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-gray-500">Actual Harvest</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-gray-500">Predicted Harvest</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-gray-500">Farmer-Reported Area</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-gray-500">Confidence</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Source</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($rows as $row)
                            <tr>
                                <td class="whitespace-nowrap px-4 py-3 text-sm font-semibold text-gray-900">{{ $row['label'] }}</td>
                                <td class="whitespace-nowrap px-4 py-3 text-right text-sm text-blue-700">
                                    {{ $row['actual_harvest_mt'] !== null ? number_format($row['actual_harvest_mt'], 4) . ' MT' : 'No actual' }}
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-right text-sm text-green-700">
                                    {{ $row['predicted_harvest_mt'] !== null ? number_format($row['predicted_harvest_mt'], 4) . ' MT' : 'Not calculated' }}
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-right text-sm text-gray-600">
                                    {{ $row['prediction_area_ha'] > 0 ? number_format($row['prediction_area_ha'], 4) . ' ha' : 'No approved plan' }}
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-right text-sm text-gray-600">
                                    {{ $row['confidence_score'] !== null ? number_format($row['confidence_score'], 1) . '%' : 'N/A' }}
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-600">
                                    {{ $row['source'] === 'ml' ? 'ML forecast' : ($row['source'] === 'no_input' ? 'No farmer input' : ($coverage['can_predict'] ? 'Unavailable' : 'Coverage blocked')) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        function cropTrendsAlpha() {
            return {
                init() {
                    this.$nextTick(() => this.initCharts());
                },
                initCharts() {
                    if (typeof Chart === 'undefined') {
                        return;
                    }

                    this.renderBarChart(
                        'alphaActualChart',
                        'Actual Harvest (MT)',
                        @json($actualData),
                        'rgba(37, 99, 235, 0.7)',
                        'rgb(37, 99, 235)'
                    );
                    this.renderBarChart(
                        'alphaPredictedChart',
                        'Predicted Harvest (MT)',
                        @json($predictedData),
                        'rgba(34, 197, 94, 0.7)',
                        'rgb(34, 197, 94)'
                    );
                },
                renderBarChart(canvasId, label, values, backgroundColor, borderColor) {
                    const canvas = document.getElementById(canvasId);
                    if (!canvas) {
                        return;
                    }

                    new Chart(canvas, {
                        type: 'bar',
                        data: {
                            labels: @json($labels),
                            datasets: [{
                                label,
                                data: values,
                                backgroundColor,
                                borderColor,
                                borderWidth: 1,
                                maxBarThickness: 28,
                            }],
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            interaction: { mode: 'index', intersect: false },
                            plugins: {
                                legend: {
                                    display: false,
                                },
                                tooltip: {
                                    callbacks: {
                                        label(context) {
                                            const value = context.parsed.y;
                                            if (value === null || value === undefined) {
                                                return `${context.dataset.label}: N/A`;
                                            }

                                            return `${context.dataset.label}: ${value.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 4 })} MT`;
                                        },
                                    },
                                },
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        callback(value) {
                                            return Number(value).toLocaleString();
                                        },
                                    },
                                    title: {
                                        display: true,
                                        text: 'Production (MT)',
                                    },
                                    grid: {
                                        color: 'rgba(15, 23, 42, 0.08)',
                                    },
                                },
                                x: {
                                    ticks: {
                                        maxRotation: 45,
                                        minRotation: 45,
                                        autoSkip: true,
                                        maxTicksLimit: 12,
                                    },
                                    grid: {
                                        color: 'rgba(15, 23, 42, 0.06)',
                                    },
                                },
                            },
                        },
                    });
                },
            };
        }
    </script>
    @endpush
</x-admin-layout>
