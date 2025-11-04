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
                    {{ $filters['crop'] }} • {{ $filters['municipality'] }} • {{ $filters['farm_type'] }}
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
                            <span class="font-medium text-gray-800 ml-2">{{ $filters['municipality'] }}</span>
                        </div>
                        <div>
                            <span class="text-gray-500">Farm Type:</span>
                            <span class="font-medium text-gray-800 ml-2">{{ $filters['farm_type'] }}</span>
                        </div>
                        <div>
                            <span class="text-gray-500">Crop:</span>
                            <span class="font-medium text-green-600 ml-2">{{ $filters['crop'] }}</span>
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
                        Yield Comparison: {{ $filters['crop'] }}
                    </h2>
                    <p class="text-sm text-gray-500 mt-1">
                        <span class="font-medium text-cyan-600">Historical Yields</span> vs 
                        <span class="font-medium text-green-600">Predicted Yields</span> (kg/ha)
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
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                    <span>
                        <span class="font-medium text-cyan-600">Cyan line</span> shows actual recorded data. 
                        <span class="font-medium text-green-600">Green line</span> shows {{ $mlApiHealthy ? 'ML-generated predictions' : 'historical average predictions' }}.
                    </span>
                </div>
                @if(collect($predictions)->whereNotNull('historical_productivity')->count() === 0)
                    <span class="text-yellow-600 font-medium">⚠ No historical data found for these filters</span>
                @endif
            </div>
        </div>

        <!-- Data Table -->
        <div class="bg-white rounded-xl shadow-md p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-800">Detailed Predictions</h3>
                <div class="text-sm text-gray-600">
                    Showing results for: <span class="font-semibold text-green-600">{{ $filters['crop'] }}</span>
                </div>
            </div>
            
            <!-- Data Info Banner -->
            <div class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded-lg text-sm text-blue-800">
                <div class="flex items-start gap-2">
                    <svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                    <div>
                        <strong>Data Sources:</strong> Historical data shows actual recorded values from your database for the specified crop, municipality, and farm type. 
                        Predicted values are generated using {{ $mlApiHealthy ? 'ML predictions' : 'historical averages' }} based on your filters.
                        @if(!$mlApiHealthy)
                            <span class="text-yellow-700">(ML API is currently unavailable, using fallback data)</span>
                        @endif
                    </div>
                </div>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Period</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Historical Productivity (kg/ha)</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Predicted Productivity (kg/ha)</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Historical Production (MT)</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Predicted Production (MT)</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($predictions as $prediction)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ $prediction['month'] }} {{ $prediction['year'] }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    @if($prediction['historical_productivity'])
                                        {{ number_format($prediction['historical_productivity'], 2) }}
                                    @else
                                        <span class="text-gray-400">N/A</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    @if($prediction['predicted_productivity'])
                                        <span class="text-green-600 font-medium">{{ number_format($prediction['predicted_productivity'], 2) }}</span>
                                    @else
                                        <span class="text-gray-400">N/A</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    @if($prediction['historical_production'])
                                        {{ number_format($prediction['historical_production'], 2) }}
                                    @else
                                        <span class="text-gray-400">N/A</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    @if($prediction['predicted_production'])
                                        <span class="text-green-600 font-medium">{{ number_format($prediction['predicted_production'], 2) }}</span>
                                    @else
                                        <span class="text-gray-400">N/A</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
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
                                        {{ $municipality }}
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
                                        {{ $crop }}
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
        function predictionResults() {
            return {
                init() {
                    this.initChart();
                },

                initChart() {
                    const ctx = document.getElementById('predictionChart');
                    if (!ctx) return;

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
                                    label: 'Historical Yields (kg/ha)',
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
                                    label: 'Predicted Yields (kg/ha)',
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
                                                label += context.parsed.y.toFixed(2) + ' kg/ha';
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
                                        text: 'Productivity (kg/ha)',
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
