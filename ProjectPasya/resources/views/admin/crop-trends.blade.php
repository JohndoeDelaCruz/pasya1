<x-admin-layout>
    <x-slot name="title">Crop Trends & Patterns</x-slot>

    @push('styles')
    <style>
        [x-cloak] { display: none !important; }
    </style>
    @endpush

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    @endpush

    <div class="space-y-6" x-data="cropTrends()">
        <!-- Page Header -->
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold text-gray-800">Dashboard</h1>
            
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

        <!-- Crop Yield Forecasting Chart -->
        <div class="bg-white rounded-xl shadow-md p-6">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                        Crop Yield Forecasting
                    </h2>
                    <p class="text-sm text-gray-500 mt-1">Prediction<span class="text-gray-400 ml-2">Jan - June 2025</span></p>
                </div>
                
                <!-- Legend -->
                <div class="flex items-center gap-6">
                    <div class="flex items-center gap-2">
                        <div class="w-3 h-3 rounded-full bg-cyan-400"></div>
                        <span class="text-sm text-gray-600">Historical Yields</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-3 h-3 rounded-full bg-green-500"></div>
                        <span class="text-sm text-gray-600">Predicted</span>
                    </div>
                </div>
            </div>

            <!-- Forecast Chart -->
            <div class="h-[350px] relative">
                <canvas id="forecastChart"></canvas>
            </div>

            <div class="mt-4 flex items-center justify-between text-sm text-gray-500">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                    <span>Showing comparison for the six months</span>
                    @if($mlApiHealthy)
                        <span class="ml-2 text-green-600">• ML-powered predictions</span>
                    @endif
                </div>
                <svg class="w-4 h-4 cursor-pointer hover:text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/>
                </svg>
            </div>
        </div>

        <!-- Summary Card Statistics -->
        <div class="bg-white rounded-xl shadow-md p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-6">Summary Card Statistics</h3>
            
            <!-- Summary of Demand Chart -->
            <div class="bg-gray-50 rounded-lg p-6 mb-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h4 class="font-semibold text-gray-800">Summary of Demand</h4>
                        <p class="text-sm text-gray-500">January - June 2025</p>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="flex items-center gap-2">
                            <div class="w-3 h-3 bg-green-500 rounded"></div>
                            <span class="text-sm text-gray-600">Predicted</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="w-3 h-3 bg-gray-300 rounded"></div>
                            <span class="text-sm text-gray-600">Recorded</span>
                        </div>
                    </div>
                </div>
                
                <!-- Demand Bar Chart -->
                <div class="h-[200px]">
                    <canvas id="demandChart"></canvas>
                </div>
            </div>

            <!-- Top Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Top 3 Most Productive Years -->
                <div class="border border-gray-200 rounded-lg p-4">
                    <h5 class="font-semibold text-gray-800 mb-3 flex items-center gap-2">
                        <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M12 7a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0V8.414l-4.293 4.293a1 1 0 01-1.414 0L8 10.414l-4.293 4.293a1 1 0 01-1.414-1.414l5-5a1 1 0 011.414 0L11 10.586 14.586 7H12z" clip-rule="evenodd"/>
                        </svg>
                        Top 3 Most Productive Years
                    </h5>
                    <ol class="space-y-2">
                        @foreach($topYears as $index => $year)
                            <li class="flex items-center gap-2 text-gray-700">
                                <span class="font-semibold text-green-600">{{ $index + 1 }}.</span>
                                <span>{{ $year }}</span>
                            </li>
                        @endforeach
                    </ol>
                </div>

                <!-- Top 3 of Most Productive Crops -->
                <div class="border border-gray-200 rounded-lg p-4">
                    <h5 class="font-semibold text-gray-800 mb-3 flex items-center gap-2">
                        <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z"/>
                        </svg>
                        Top 3 of Most Productive Crops
                    </h5>
                    <ol class="space-y-2">
                        @foreach($topCrops as $index => $crop)
                            <li class="flex items-center gap-2 text-gray-700">
                                <span class="font-semibold text-green-600">{{ $index + 1 }}.</span>
                                <span>{{ $crop }}</span>
                            </li>
                        @endforeach
                    </ol>
                </div>
            </div>
        </div>

        <!-- Predict More Button -->
        <div class="flex justify-end">
            <button @click="$dispatch('open-modal', 'prediction-modal')" class="px-6 py-2.5 bg-yellow-400 hover:bg-yellow-500 text-gray-800 font-semibold rounded-lg transition-colors shadow-sm">
                Predict More
            </button>
        </div>

        <!-- Prediction Modal -->
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
                                    <option value="{{ $municipality }}">{{ $municipality }}</option>
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
                                <option value="Rainfed">Rainfed</option>
                                <option value="Irrigated">Irrigated</option>
                            </select>
                        </div>

                        <!-- Month Range -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Month</label>
                            <div class="grid grid-cols-2 gap-2">
                                <select name="month_from" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                    <option value="">Month</option>
                                    <option value="JAN">January</option>
                                    <option value="FEB">February</option>
                                    <option value="MAR">March</option>
                                    <option value="APR">April</option>
                                    <option value="MAY">May</option>
                                    <option value="JUN">June</option>
                                    <option value="JUL">July</option>
                                    <option value="AUG">August</option>
                                    <option value="SEP">September</option>
                                    <option value="OCT">October</option>
                                    <option value="NOV">November</option>
                                    <option value="DEC">December</option>
                                </select>
                                <div class="flex items-center justify-center">
                                    <span class="text-gray-500 text-sm">to</span>
                                </div>
                            </div>
                            <select name="month_to" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent mt-2">
                                <option value="">Month</option>
                                <option value="JAN">January</option>
                                <option value="FEB">February</option>
                                <option value="MAR">March</option>
                                <option value="APR">April</option>
                                <option value="MAY">May</option>
                                <option value="JUN">June</option>
                                <option value="JUL">July</option>
                                <option value="AUG">August</option>
                                <option value="SEP">September</option>
                                <option value="OCT">October</option>
                                <option value="NOV">November</option>
                                <option value="DEC">December</option>
                            </select>
                        </div>

                        <!-- Year Range -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Year</label>
                            <div class="grid grid-cols-2 gap-2">
                                <input type="number" name="year_from" required min="2000" max="2050" placeholder="Year" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                <div class="flex items-center justify-center">
                                    <span class="text-gray-500 text-sm">to</span>
                                </div>
                            </div>
                            <input type="number" name="year_to" required min="2000" max="2050" placeholder="Year" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent mt-2">
                        </div>

                        <!-- Crop -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Crop<span class="text-red-500">*</span>
                            </label>
                            <select name="crop" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                <option value="">Select a crop</option>
                                @foreach($crops as $crop)
                                    <option value="{{ $crop }}">{{ $crop }}</option>
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
        function cropTrends() {
            return {
                init() {
                    this.initForecastChart();
                    this.initDemandChart();
                },

                initForecastChart() {
                    const ctx = document.getElementById('forecastChart');
                    if (!ctx) return;

                    const months = @json($months);
                    const historical = @json($historicalYields);
                    const predicted = @json($predictedYields);

                    const monthLabels = months.map(m => {
                        const monthMap = { JAN: 'Jan', FEB: 'Feb', MAR: 'Mar', APR: 'Apr', MAY: 'May', JUN: 'Jun' };
                        return monthMap[m] || m;
                    });

                    new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: monthLabels,
                            datasets: [
                                {
                                    label: 'Historical Yields',
                                    data: historical,
                                    borderColor: 'rgb(34, 211, 238)',
                                    backgroundColor: 'rgba(34, 211, 238, 0.1)',
                                    borderWidth: 3,
                                    tension: 0.4,
                                    fill: true,
                                    pointRadius: 5,
                                    pointHoverRadius: 8
                                },
                                {
                                    label: 'Predicted',
                                    data: predicted,
                                    borderColor: 'rgb(34, 197, 94)',
                                    backgroundColor: 'rgba(34, 197, 94, 0.1)',
                                    borderWidth: 3,
                                    tension: 0.4,
                                    fill: true,
                                    pointRadius: 5,
                                    pointHoverRadius: 8
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: false
                                },
                                tooltip: {
                                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                    padding: 12,
                                    titleFont: { size: 13 },
                                    bodyFont: { size: 12 }
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    grid: {
                                        color: 'rgba(0, 0, 0, 0.05)'
                                    },
                                    ticks: {
                                        font: { size: 11 }
                                    }
                                },
                                x: {
                                    grid: {
                                        display: false
                                    },
                                    ticks: {
                                        font: { size: 11 }
                                    }
                                }
                            }
                        }
                    });
                },

                initDemandChart() {
                    const ctx = document.getElementById('demandChart');
                    if (!ctx) return;

                    const months = @json($months);
                    const predicted = @json($demandData);
                    const recorded = @json($recordedData);

                    const monthLabels = months.map(m => {
                        const monthMap = { JAN: 'Jan', FEB: 'Feb', MAR: 'Mar', APR: 'Apr', MAY: 'May', JUN: 'Jun' };
                        return monthMap[m] || m;
                    });

                    new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: monthLabels,
                            datasets: [
                                {
                                    label: 'Predicted',
                                    data: predicted,
                                    backgroundColor: 'rgb(34, 197, 94)',
                                    borderRadius: 4,
                                    barThickness: 20
                                },
                                {
                                    label: 'Recorded',
                                    data: recorded,
                                    backgroundColor: 'rgb(209, 213, 219)',
                                    borderRadius: 4,
                                    barThickness: 20
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: false
                                },
                                tooltip: {
                                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                    padding: 10,
                                    titleFont: { size: 12 },
                                    bodyFont: { size: 11 }
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    grid: {
                                        color: 'rgba(0, 0, 0, 0.05)'
                                    },
                                    ticks: {
                                        font: { size: 10 }
                                    }
                                },
                                x: {
                                    grid: {
                                        display: false
                                    },
                                    ticks: {
                                        font: { size: 10 }
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
