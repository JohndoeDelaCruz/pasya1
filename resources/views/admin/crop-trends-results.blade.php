<x-admin-layout>
    <x-slot name="title">Prediction Results</x-slot>

    @push('styles')
    <style>
        [x-cloak] { display: none !important; }
    </style>
    @endpush

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    @endpush

    <div class="space-y-6" x-data="predictionResults()">
        <!-- Page Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Prediction Results</h1>
                <p class="text-sm text-gray-500 mt-1">
                    {{ ucwords(strtolower($filters['crop'])) }} • {{ ucwords(strtolower($filters['municipality'])) }} • {{ ucwords(strtolower($filters['farm_type'])) }}
                </p>
            </div>
            
            <!-- ML API Status Indicator -->
            <div class="flex items-center gap-2">
                @if($mlApiHealthy)
                    <div class="flex items-center gap-2 px-3 py-1.5 bg-green-50 border border-green-200 rounded-lg">
                        <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                        <span class="text-xs font-medium text-green-700">ML Predictions Active</span>
                    </div>
                @else
                    <div class="flex items-center gap-2 px-3 py-1.5 bg-yellow-50 border border-yellow-200 rounded-lg">
                        <div class="w-2 h-2 bg-yellow-500 rounded-full"></div>
                        <span class="text-xs font-medium text-yellow-700">Using Historical Data</span>
                    </div>
                @endif
            </div>
        </div>

        <!-- Back Button -->
        <div>
            <a href="{{ route('admin.crop-trends') }}" class="inline-flex items-center gap-2 text-gray-600 hover:text-gray-800">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Back to Dashboard
            </a>
        </div>

        <!-- Filter Summary Card -->
        <div class="bg-white rounded-xl shadow-md p-6">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-lg font-semibold text-gray-800">Applied Filters</h3>
                        <div class="text-sm text-gray-600">
                            <span class="font-medium">{{ count($predictions) }}</span> predictions generated
                        </div>
                    </div>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4 text-sm">
                        <div>
                            <span class="text-gray-500">Municipality:</span>
                            <span class="font-medium text-gray-800 ml-2">{{ ucwords(strtolower($filters['municipality'])) }}</span>
                        </div>
                        <div>
                            <span class="text-gray-500">Farm Type:</span>
                            <span class="font-medium text-gray-800 ml-2">{{ ucwords(strtolower($filters['farm_type'])) }}</span>
                        </div>
                        <div>
                            <span class="text-gray-500">Crop:</span>
                            <span class="font-medium text-green-600 ml-2">{{ ucwords(strtolower($filters['crop'])) }}</span>
                        </div>
                        <div>
                            <span class="text-gray-500">Month Range:</span>
                            <span class="font-medium text-gray-800 ml-2">{{ $filters['month_from'] }} - {{ $filters['month_to'] }}</span>
                        </div>
                        <div>
                            <span class="text-gray-500">Year Range:</span>
                            <span class="font-medium text-gray-800 ml-2">{{ $filters['year_from'] }} - {{ $filters['year_to'] }}</span>
                        </div>
                    </div>
                </div>
                <button @click="$dispatch('open-modal', 'prediction-modal')" class="ml-6 px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition-colors whitespace-nowrap">
                    Modify Filters
                </button>
            </div>
        </div>

        <!-- Prediction Chart -->
        <div class="bg-white rounded-xl shadow-md p-6">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                        Production Comparison: {{ ucwords(strtolower($filters['crop'])) }}
                    </h2>
                    <p class="text-sm text-gray-500 mt-1">
                        <span class="font-medium text-cyan-600">Historical Production</span> vs 
                        <span class="font-medium text-green-600">Predicted Production</span> (MT)
                    </p>
                </div>
                
                <!-- Legend -->
                <div class="flex items-center gap-6">
                    <div class="flex items-center gap-2">
                        <div class="w-3 h-3 rounded-full bg-cyan-400"></div>
                        <span class="text-sm text-gray-600">Historical Data</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-3 h-3 rounded-full bg-green-500"></div>
                        <span class="text-sm text-gray-600">ML Predictions</span>
                    </div>
                </div>
            </div>

            <!-- Chart -->
            <div class="h-[400px] relative">
                <canvas id="predictionChart"></canvas>
            </div>
            
            <!-- Chart Info -->
            <div class="mt-4 flex items-center justify-between text-sm">
                <div class="flex items-center gap-2 text-gray-600">
                </div>
                @if(collect($predictions)->whereNotNull('normalized_historical_production')->count() === 0)
                    <span class="text-yellow-600 font-medium">⚠ No historical data found for these filters</span>
                @endif
            </div>
        </div>

        <!-- Data Table -->
        <div class="bg-white rounded-xl shadow-md p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-800">Detailed Predictions</h3>
                <div class="text-sm text-gray-600">
                    Showing results for: <span class="font-semibold text-green-600">{{ ucwords(strtolower($filters['crop'])) }}</span>
                </div>
            </div>
            
            <!-- Simple Info Banner -->
            <div class="mb-4 p-4 bg-gradient-to-r from-blue-50 to-green-50 border border-blue-200 rounded-lg">
                <div class="flex items-center gap-3 mb-3">
                    <div class="p-2 bg-blue-100 rounded-full">
                        <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-800">How to Read This Chart</p>
                        <p class="text-sm text-gray-600">Production values use the <span class="font-bold text-blue-600">actual recorded farm sizes</span> for each period</p>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-cyan-500"></span>
                        <span><strong class="text-cyan-700">Historical</strong></span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-green-500"></span>
                        <span><strong class="text-green-700">Predicted</strong></span>
                    </div>
                </div>
                @if(!$mlApiHealthy)
                    <p class="mt-3 text-sm text-yellow-700 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                        Using historical averages as predictions
                    </p>
                @endif
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Period</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider" colspan="2">
                                Productivity (MT/ha)
                            </th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider" colspan="2">
                                Production @ {{ number_format($avgAreaHarvested ?? 0, 2) }} ha (MT)
                            </th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Accuracy</th>
                        </tr>
                        <tr class="bg-gray-100">
                            <th class="px-4 py-2"></th>
                            <th class="px-4 py-2 text-center text-xs font-medium text-cyan-600 bg-cyan-50">Historical</th>
                            <th class="px-4 py-2 text-center text-xs font-medium text-green-600 bg-green-50">Predicted</th>
                            <th class="px-4 py-2 text-center text-xs font-medium text-cyan-600 bg-cyan-50">Historical</th>
                            <th class="px-4 py-2 text-center text-xs font-medium text-green-600 bg-green-50">Predicted</th>
                            <th class="px-4 py-2 text-center text-xs font-medium text-gray-500">
                                <span class="text-xs">(Difference)</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($predictions as $prediction)
                            @php
                                // Calculate difference percentage between normalized historical and predicted
                                $diffPercent = null;
                                $diffClass = 'text-gray-400';
                                $diffIcon = '';
                                if (isset($prediction['normalized_historical_production']) && $prediction['normalized_historical_production'] > 0 && $prediction['predicted_production']) {
                                    $diffPercent = (($prediction['predicted_production'] - $prediction['normalized_historical_production']) / $prediction['normalized_historical_production']) * 100;
                                    if (abs($diffPercent) <= 10) {
                                        $diffClass = 'text-green-600 bg-green-50';
                                        $diffIcon = '✓';
                                    } elseif (abs($diffPercent) <= 25) {
                                        $diffClass = 'text-yellow-600 bg-yellow-50';
                                        $diffIcon = '~';
                                    } else {
                                        $diffClass = 'text-red-600 bg-red-50';
                                        $diffIcon = '!';
                                    }
                                }
                            @endphp
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ $prediction['month'] }} {{ $prediction['year'] }}
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-sm text-center bg-cyan-50/50">
                                    @if($prediction['historical_productivity'])
                                        <span class="text-cyan-700 font-medium">{{ number_format($prediction['historical_productivity'], 2) }}</span>
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-sm text-center bg-green-50/50">
                                    @if($prediction['predicted_productivity'])
                                        <span class="text-green-700 font-medium">{{ number_format($prediction['predicted_productivity'], 2) }}</span>
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-sm text-center bg-cyan-50/50">
                                    @if(isset($prediction['normalized_historical_production']) && $prediction['normalized_historical_production'])
                                        <span class="text-cyan-700 font-semibold">{{ number_format($prediction['normalized_historical_production'], 2) }}</span>
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-sm text-center bg-green-50/50">
                                    @if($prediction['predicted_production'])
                                        <span class="text-green-700 font-semibold">{{ number_format($prediction['predicted_production'], 2) }}</span>
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-sm text-center {{ $diffClass }} rounded">
                                    @if($diffPercent !== null)
                                        <span class="font-medium">
                                            {{ $diffIcon }} {{ $diffPercent > 0 ? '+' : '' }}{{ number_format($diffPercent, 1) }}%
                                        </span>
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- Legend for accuracy indicators -->
            <div class="mt-4 flex flex-wrap items-center gap-4 text-xs text-gray-600">
                <span class="font-medium">Accuracy Legend:</span>
                <span class="flex items-center gap-1"><span class="px-2 py-0.5 bg-green-50 text-green-600 rounded">✓ ±10%</span> High accuracy</span>
                <span class="flex items-center gap-1"><span class="px-2 py-0.5 bg-yellow-50 text-yellow-600 rounded">~ ±25%</span> Moderate</span>
                <span class="flex items-center gap-1"><span class="px-2 py-0.5 bg-red-50 text-red-600 rounded">! >25%</span> Large difference</span>
            </div>
        </div>

        <!-- Prediction Modal (same as main page) -->
        <div x-data="{ show: false }" 
             @open-modal.window="if ($event.detail === 'prediction-modal') show = true"
             @close-modal.window="show = false"
             @keydown.escape.window="show = false"
             x-show="show"
             x-cloak
             class="fixed inset-0 z-50 overflow-y-auto"
             style="display: none;">
            
            <!-- Backdrop -->
            <div x-show="show" 
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
                 @click="show = false"></div>

            <!-- Modal Content -->
            <div class="flex min-h-full items-center justify-center p-4">
                <div x-show="show"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     class="relative bg-white rounded-lg shadow-xl max-w-md w-full p-6">
                    
                    <!-- Modal Header -->
                    <div class="mb-6">
                        <h3 class="text-xl font-bold text-gray-800">Prediction</h3>
                    </div>

                    <!-- Prediction Form -->
                    <form method="POST" action="{{ route('admin.crop-trends.predict') }}" class="space-y-4">
                        @csrf
                        
                        <!-- Municipality -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Municipality<span class="text-red-500">*</span>
                            </label>
                            <select name="municipality" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                <option value="">Select municipality</option>
                                @foreach($municipalities as $municipality)
                                    <option value="{{ $municipality }}" {{ $filters['municipality'] == $municipality ? 'selected' : '' }}>
                                        {{ ucwords(strtolower($municipality)) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Farm Type -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Farm Type<span class="text-red-500">*</span>
                            </label>
                            <select name="farm_type" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                <option value="">Select farm type</option>
                                <option value="Rainfed" {{ $filters['farm_type'] == 'Rainfed' ? 'selected' : '' }}>Rainfed</option>
                                <option value="Irrigated" {{ $filters['farm_type'] == 'Irrigated' ? 'selected' : '' }}>Irrigated</option>
                            </select>
                        </div>

                        <!-- Month Range -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Month</label>
                            <div class="grid grid-cols-2 gap-2">
                                <select name="month_from" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                    <option value="">Month</option>
                                    @foreach(['JAN' => 'January', 'FEB' => 'February', 'MAR' => 'March', 'APR' => 'April', 'MAY' => 'May', 'JUN' => 'June', 'JUL' => 'July', 'AUG' => 'August', 'SEP' => 'September', 'OCT' => 'October', 'NOV' => 'November', 'DEC' => 'December'] as $key => $label)
                                        <option value="{{ $key }}" {{ $filters['month_from'] == $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                                <div class="flex items-center justify-center">
                                    <span class="text-gray-500 text-sm">to</span>
                                </div>
                            </div>
                            <select name="month_to" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent mt-2">
                                <option value="">Month</option>
                                @foreach(['JAN' => 'January', 'FEB' => 'February', 'MAR' => 'March', 'APR' => 'April', 'MAY' => 'May', 'JUN' => 'June', 'JUL' => 'July', 'AUG' => 'August', 'SEP' => 'September', 'OCT' => 'October', 'NOV' => 'November', 'DEC' => 'December'] as $key => $label)
                                    <option value="{{ $key }}" {{ $filters['month_to'] == $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Year Range -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Year</label>
                            <div class="grid grid-cols-2 gap-2">
                                <input type="number" name="year_from" required min="2000" max="2050" placeholder="Year" value="{{ $filters['year_from'] }}" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                <div class="flex items-center justify-center">
                                    <span class="text-gray-500 text-sm">to</span>
                                </div>
                            </div>
                            <input type="number" name="year_to" required min="2000" max="2050" placeholder="Year" value="{{ $filters['year_to'] }}" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent mt-2">
                        </div>

                        <!-- Crop -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Crop<span class="text-red-500">*</span>
                            </label>
                            <select name="crop" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                <option value="">Select a crop</option>
                                @foreach($crops as $crop)
                                    <option value="{{ $crop }}" {{ $filters['crop'] == $crop ? 'selected' : '' }}>
                                        {{ ucwords(strtolower($crop)) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Submit Button -->
                        <div class="flex items-center justify-between gap-3 pt-2">
                            <button type="button" @click="show = false" class="px-4 py-2 text-gray-700 hover:text-gray-900 font-medium">
                                Cancel
                            </button>
                            <button type="submit" class="px-6 py-2 bg-yellow-400 hover:bg-yellow-500 text-gray-800 font-semibold rounded-md transition-colors">
                                Submit
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Debug: Verify this page is loading
        console.log('=== PREDICTION RESULTS PAGE LOADED ===');
        console.log('Total Predictions:', {{ count($predictions) }});
        
        function predictionResults() {
            return {
                init() {
                    console.log('Initializing prediction results...');
                    this.initChart();
                },

                initChart() {
                    const ctx = document.getElementById('predictionChart');
                    if (!ctx) {
                        console.error('Chart canvas not found!');
                        return;
                    }

                    const labels = @json($chartLabels);
                    const historical = @json($historicalData);
                    const predicted = @json($predictedData);

                    // Debug: Log the data
                    console.log('Chart Labels:', labels);
                    console.log('Historical Data:', historical);
                    console.log('Predicted Data:', predicted);

                    new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: labels,
                            datasets: [
                                {
                                    label: 'Historical Production (MT)',
                                    data: historical,
                                    borderColor: 'rgb(34, 211, 238)',
                                    backgroundColor: 'rgba(34, 211, 238, 0.2)',
                                    borderWidth: 3,
                                    tension: 0.4,
                                    fill: true,
                                    pointRadius: 5,
                                    pointHoverRadius: 8,
                                    spanGaps: false, // Don't connect across gaps to show missing data
                                    pointBackgroundColor: 'rgb(34, 211, 238)',
                                    pointBorderColor: '#fff',
                                    pointBorderWidth: 2
                                },
                                {
                                    label: 'Predicted Production (MT)',
                                    data: predicted,
                                    borderColor: 'rgb(34, 197, 94)',
                                    backgroundColor: 'rgba(34, 197, 94, 0.2)',
                                    borderWidth: 3,
                                    tension: 0.4,
                                    fill: true,
                                    pointRadius: 5,
                                    pointHoverRadius: 8,
                                    spanGaps: false, // Don't connect across gaps to show missing data
                                    pointBackgroundColor: 'rgb(34, 197, 94)',
                                    pointBorderColor: '#fff',
                                    pointBorderWidth: 2
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            interaction: {
                                mode: 'index',
                                intersect: false,
                            },
                            plugins: {
                                legend: {
                                    display: true,
                                    position: 'top',
                                    labels: {
                                        usePointStyle: true,
                                        padding: 15
                                    }
                                },
                                tooltip: {
                                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                    padding: 12,
                                    titleFont: {
                                        size: 14
                                    },
                                    bodyFont: {
                                        size: 13
                                    },
                                    callbacks: {
                                        label: function(context) {
                                            let label = context.dataset.label || '';
                                            if (label) {
                                                label += ': ';
                                            }
                                            if (context.parsed.y !== null) {
                                                label += context.parsed.y.toFixed(2) + ' MT';
                                            } else {
                                                label += 'N/A';
                                            }
                                            return label;
                                        }
                                    }
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    title: {
                                        display: true,
                                        text: 'Production (MT)',
                                        font: {
                                            size: 14,
                                            weight: 'bold'
                                        }
                                    },
                                    grid: {
                                        color: 'rgba(0, 0, 0, 0.05)'
                                    }
                                },
                                x: {
                                    title: {
                                        display: true,
                                        text: 'Period',
                                        font: {
                                            size: 14,
                                            weight: 'bold'
                                        }
                                    },
                                    grid: {
                                        display: false
                                    }
                                }
                            }
                        }
                    });
                }
            }
        }
    </script>
    @endpush
</x-admin-layout>
