<x-admin-layout>
    <x-slot name="title">Crop Trends & Patterns</x-slot>

    @push('styles')
    <style>
        [x-cloak] { display: none !important; }
    </style>
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

        <!-- Crop Production Forecasting Chart -->
        <div class="bg-white rounded-xl shadow-md p-6">
            <div class="flex items-center justify-between mb-5">
                <div>
                    <h2 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                        Crop Production Forecasting
                    </h2>
                    <p class="text-sm text-gray-500 mt-1">
                        <span class="font-medium text-gray-700">{{ $selectedCrop }}</span>
                        <span class="text-gray-400 mx-1">&bull;</span>
                        <span>{{ $selectedMunicipality }}</span>
                        <span class="text-gray-400 mx-1">&bull;</span>
                        <span>{{ $selectedFarmType }}</span>
                        <span class="text-gray-400 mx-1">&bull;</span>
                        <span>Jan – Jun {{ $currentYear }}</span>
                    </p>
                </div>
                
                <!-- Legend -->
                <div class="flex items-center gap-4">
                    <div class="flex items-center gap-2 px-3 py-1.5 bg-blue-50 border border-blue-200 rounded-lg">
                        <svg width="24" height="10" class="flex-shrink-0">
                            <line x1="0" y1="5" x2="24" y2="5" stroke="#3b82f6" stroke-width="2.5" stroke-dasharray="5,3"/>
                            <circle cx="12" cy="5" r="3" fill="#3b82f6" stroke="#fff" stroke-width="1.5"/>
                        </svg>
                        <span class="text-xs font-medium text-blue-700">Historical</span>
                    </div>
                    <div class="flex items-center gap-2 px-3 py-1.5 bg-green-50 border border-green-200 rounded-lg">
                        <svg width="24" height="10" class="flex-shrink-0">
                            <line x1="0" y1="5" x2="24" y2="5" stroke="#16a34a" stroke-width="3"/>
                            <circle cx="12" cy="5" r="3.5" fill="#16a34a" stroke="#fff" stroke-width="1.5"/>
                        </svg>
                        <span class="text-xs font-medium text-green-700">Predicted</span>
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
                    <span>Historical = avg of yearly totals</span>
                    @if($mlApiHealthy)
                        <span class="ml-2 text-green-600 font-medium">&bull; ML-powered predictions</span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Summary Statistics -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Monthly Production Chart (spans 2 cols) -->
            <div class="lg:col-span-2 bg-white rounded-xl shadow-md p-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800">Average Monthly Production</h3>
                        <p class="text-sm text-gray-500">{{ $selectedCrop }} • {{ $selectedMunicipality }} • Production in Metric Tons (mt)</p>
                    </div>
                </div>
                <div class="h-[240px]">
                    <canvas id="demandChart"></canvas>
                </div>
            </div>

            <!-- Top Stats Column -->
            <div class="space-y-6">
                <!-- Top 3 Most Productive Years -->
                <div class="bg-white rounded-xl shadow-md p-5">
                    <h5 class="font-semibold text-gray-800 mb-4 flex items-center gap-2 text-sm uppercase tracking-wide">
                        <svg class="w-4 h-4 text-amber-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M12 7a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0V8.414l-4.293 4.293a1 1 0 01-1.414 0L8 10.414l-4.293 4.293a1 1 0 01-1.414-1.414l5-5a1 1 0 011.414 0L11 10.586 14.586 7H12z" clip-rule="evenodd"/>
                        </svg>
                        Top 3 Productive Years
                    </h5>
                    <ol class="space-y-3">
                        @forelse($topYearsWithProduction as $index => $item)
                            @php
                                $maxProd = $topYearsWithProduction[0]['production'] ?? 1;
                                $pct = $maxProd > 0 ? round(($item['production'] / $maxProd) * 100) : 0;
                                $colors = ['bg-amber-500', 'bg-amber-400', 'bg-amber-300'];
                            @endphp
                            <li>
                                <div class="flex items-center justify-between mb-1">
                                    <div class="flex items-center gap-2">
                                        <span class="w-5 h-5 flex items-center justify-center rounded-full {{ $colors[$index] ?? 'bg-gray-300' }} text-white text-xs font-bold">{{ $index + 1 }}</span>
                                        <span class="font-semibold text-gray-800">{{ $item['year'] }}</span>
                                    </div>
                                    <span class="text-sm font-medium text-gray-600">{{ number_format($item['production'], 0) }} mt</span>
                                </div>
                                <div class="w-full bg-gray-100 rounded-full h-1.5">
                                    <div class="{{ $colors[$index] ?? 'bg-gray-300' }} h-1.5 rounded-full" style="width: {{ $pct }}%"></div>
                                </div>
                            </li>
                        @empty
                            <li class="text-gray-500 text-sm">No data available</li>
                        @endforelse
                    </ol>
                </div>

                <!-- Top 3 Most Productive Crops -->
                <div class="bg-white rounded-xl shadow-md p-5">
                    <h5 class="font-semibold text-gray-800 mb-4 flex items-center gap-2 text-sm uppercase tracking-wide">
                        <svg class="w-4 h-4 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z"/>
                        </svg>
                        Top 3 Productive Crops
                    </h5>
                    <ol class="space-y-3">
                        @forelse($topCropsWithProduction as $index => $item)
                            @php
                                $maxCropProd = $topCropsWithProduction[0]['production'] ?? 1;
                                $cropPct = $maxCropProd > 0 ? round(($item['production'] / $maxCropProd) * 100) : 0;
                                $cropColors = ['bg-green-500', 'bg-green-400', 'bg-green-300'];
                            @endphp
                            <li>
                                <div class="flex items-center justify-between mb-1">
                                    <div class="flex items-center gap-2">
                                        <span class="w-5 h-5 flex items-center justify-center rounded-full {{ $cropColors[$index] ?? 'bg-gray-300' }} text-white text-xs font-bold">{{ $index + 1 }}</span>
                                        <span class="font-semibold text-gray-800">{{ $item['crop'] }}</span>
                                    </div>
                                    <span class="text-sm font-medium text-gray-600">{{ number_format($item['production'], 0) }} mt</span>
                                </div>
                                <div class="w-full bg-gray-100 rounded-full h-1.5">
                                    <div class="{{ $cropColors[$index] ?? 'bg-gray-300' }} h-1.5 rounded-full" style="width: {{ $cropPct }}%"></div>
                                </div>
                            </li>
                        @empty
                            <li class="text-gray-500 text-sm">No data available</li>
                        @endforelse
                    </ol>
                </div>
            </div>
        </div>

        <!-- Predict More Button -->
        <div class="flex justify-end">
            <button type="button" @click="$dispatch('open-modal', 'prediction-modal')" class="px-6 py-2.5 bg-yellow-400 hover:bg-yellow-500 text-gray-800 font-semibold rounded-lg transition-colors shadow-sm">
                Predict More
            </button>
        </div>

        <!-- Prediction Modal -->
        <div x-data="{ show: {{ $errors->any() ? 'true' : 'false' }} }" 
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

                    <!-- Validation Errors -->
                    @if ($errors->any())
                        <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                            <div class="flex items-start gap-2">
                                <svg class="w-5 h-5 text-red-600 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                </svg>
                                <div class="flex-1">
                                    <h4 class="text-sm font-semibold text-red-800">Please fix the following errors:</h4>
                                    <ul class="mt-2 text-sm text-red-700 list-disc list-inside">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Prediction Form -->
                    <form method="POST" action="{{ route('admin.crop-trends.predict') }}" class="space-y-4" x-data="{ submitting: false }" @submit="submitting = true">
                        @csrf
                        
                        <!-- Municipality -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Municipality<span class="text-red-500">*</span>
                            </label>
                            <select name="municipality" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                <option value="">Select municipality</option>
                                @foreach($municipalities as $municipality)
                                    <option value="{{ $municipality }}" {{ strtoupper($municipality) === strtoupper($selectedMunicipality) ? 'selected' : '' }}>
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
                                <option value="Rainfed" {{ strtoupper($selectedFarmType) === 'RAINFED' ? 'selected' : '' }}>Rainfed</option>
                                <option value="Irrigated" {{ strtoupper($selectedFarmType) === 'IRRIGATED' ? 'selected' : '' }}>Irrigated</option>
                            </select>
                        </div>

                        <!-- Month Range -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Month</label>
                            <div class="grid grid-cols-2 gap-2">
                                <select name="month_from" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                    <option value="">Month</option>
                                    <option value="JAN" selected>January</option>
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
                                <option value="JUN" selected>June</option>
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
                                <input type="number" name="year_from" required min="2000" max="2050" value="{{ date('Y') }}" placeholder="Year" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                <div class="flex items-center justify-center">
                                    <span class="text-gray-500 text-sm">to</span>
                                </div>
                            </div>
                            <input type="number" name="year_to" required min="2000" max="2050" value="{{ date('Y') }}" placeholder="Year" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent mt-2">
                        </div>

                        <!-- Crop -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Crop<span class="text-red-500">*</span>
                            </label>
                            <select name="crop" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                <option value="">Select a crop</option>
                                @foreach($crops as $crop)
                                    <option value="{{ $crop }}" {{ strtoupper($crop) === strtoupper($selectedCrop) ? 'selected' : '' }}>
                                        {{ ucwords(strtolower($crop)) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Submit Button -->
                        <div class="flex items-center justify-between gap-3 pt-2">
                            <button type="button" @click="show = false" class="px-4 py-2 text-gray-700 hover:text-gray-900 font-medium" :disabled="submitting">
                                Cancel
                            </button>
                            <button type="submit" class="px-6 py-2 bg-yellow-400 hover:bg-yellow-500 disabled:bg-gray-400 disabled:cursor-not-allowed text-gray-800 font-semibold rounded-md transition-colors flex items-center gap-2" :disabled="submitting">
                                <span x-show="submitting">
                                    <svg class="animate-spin h-5 w-5 text-gray-800" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </span>
                                <span x-text="submitting ? 'Processing...' : 'Submit'">Submit</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <!-- Load Chart.js first -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <script>
        function cropTrends() {
            return {
                init() {
                    console.log('=== CROP TRENDS PAGE LOADED ===');
                    console.log('Chart.js loaded:', typeof Chart !== 'undefined');
                    
                    // Wait for next tick to ensure DOM is ready
                    this.$nextTick(() => {
                        this.initForecastChart();
                        this.initDemandChart();
                    });
                },

                initForecastChart() {
                    const ctx = document.getElementById('forecastChart');
                    console.log('Forecast Chart Canvas:', ctx);
                    
                    if (!ctx) {
                        console.error('Forecast chart canvas not found!');
                        return;
                    }
                    
                    if (typeof Chart === 'undefined') {
                        console.error('Chart.js not loaded!');
                        return;
                    }

                    const months = @json($months);
                    const historical = @json($historicalYields);
                    const predicted = @json($predictedYields);

                    console.log('Forecast Chart Data:');
                    console.log('- Months:', months);
                    console.log('- Historical Production:', historical);
                    console.log('- Predicted Production:', predicted);

                    const monthLabels = months.map(m => {
                        const monthMap = { JAN: 'Jan', FEB: 'Feb', MAR: 'Mar', APR: 'Apr', MAY: 'May', JUN: 'Jun' };
                        return monthMap[m] || m;
                    });

                    console.log('- Month Labels:', monthLabels);

                    // Gradient for predicted line
                    const gradient = ctx.getContext('2d').createLinearGradient(0, 0, 0, ctx.parentElement.clientHeight || 350);
                    gradient.addColorStop(0, 'rgba(22, 163, 74, 0.25)');
                    gradient.addColorStop(1, 'rgba(22, 163, 74, 0.02)');

                    new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: monthLabels,
                            datasets: [
                                {
                                    label: 'Historical Production (mt)',
                                    data: historical,
                                    borderColor: 'rgb(59, 130, 246)',
                                    backgroundColor: 'rgba(59, 130, 246, 0.06)',
                                    borderWidth: 2.5,
                                    borderDash: [6, 4],
                                    tension: 0.4,
                                    fill: true,
                                    pointStyle: 'rectRot',
                                    pointRadius: 6,
                                    pointHoverRadius: 9,
                                    pointBackgroundColor: 'rgb(59, 130, 246)',
                                    pointBorderColor: '#fff',
                                    pointBorderWidth: 2
                                },
                                {
                                    label: 'Predicted Production (mt)',
                                    data: predicted,
                                    borderColor: 'rgb(22, 163, 74)',
                                    backgroundColor: gradient,
                                    borderWidth: 3,
                                    tension: 0.4,
                                    fill: true,
                                    pointStyle: 'circle',
                                    pointRadius: 5,
                                    pointHoverRadius: 9,
                                    pointBackgroundColor: 'rgb(22, 163, 74)',
                                    pointBorderColor: '#fff',
                                    pointBorderWidth: 2
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            animation: {
                                duration: 1200,
                                easing: 'easeInOutCubic',
                                delay: (context) => {
                                    let delay = 0;
                                    if (context.type === 'data' && context.mode === 'default') {
                                        delay = context.dataIndex * 50 + context.datasetIndex * 150;
                                    }
                                    return delay;
                                }
                            },
                            plugins: {
                                legend: {
                                    display: false
                                },
                                tooltip: {
                                    backgroundColor: 'rgba(15, 23, 42, 0.9)',
                                    padding: 14,
                                    titleFont: { size: 13, weight: 'bold' },
                                    bodyFont: { size: 12 },
                                    cornerRadius: 8,
                                    displayColors: false,
                                    callbacks: {
                                        title: function(items) {
                                            return items[0].label + ' Production';
                                        },
                                        label: function(context) {
                                            const icon = context.datasetIndex === 0 ? '◆' : '●';
                                            return icon + ' ' + context.dataset.label.replace(' (mt)', '') + ': ' + context.parsed.y.toFixed(2) + ' mt';
                                        },
                                        afterBody: function(items) {
                                            if (items.length >= 2) {
                                                const hist = items[0].parsed.y;
                                                const pred = items[1].parsed.y;
                                                if (hist && pred) {
                                                    const diff = pred - hist;
                                                    const pct = hist !== 0 ? ((diff / hist) * 100).toFixed(1) : 'N/A';
                                                    const arrow = diff >= 0 ? '▲' : '▼';
                                                    return ['', arrow + ' Difference: ' + diff.toFixed(2) + ' mt (' + pct + '%)'];
                                                }
                                            }
                                            return [];
                                        }
                                    }
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    title: {
                                        display: true,
                                        text: 'Production (mt)',
                                        font: { size: 11, weight: 'bold' },
                                        color: '#6b7280'
                                    },
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
                                        font: { size: 11, weight: '500' }
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
                    const productionData = @json($monthlyProductionData);

                    const monthLabels = months.map(m => {
                        const monthMap = { JAN: 'Jan', FEB: 'Feb', MAR: 'Mar', APR: 'Apr', MAY: 'May', JUN: 'Jun' };
                        return monthMap[m] || m;
                    });

                    // Gradient bars
                    const barColors = [
                        'rgba(34, 197, 94, 0.85)',
                        'rgba(16, 185, 129, 0.85)',
                        'rgba(20, 184, 166, 0.85)',
                        'rgba(6, 182, 212, 0.85)',
                        'rgba(59, 130, 246, 0.85)',
                        'rgba(99, 102, 241, 0.85)'
                    ];
                    const barBorders = [
                        'rgb(34, 197, 94)',
                        'rgb(16, 185, 129)',
                        'rgb(20, 184, 166)',
                        'rgb(6, 182, 212)',
                        'rgb(59, 130, 246)',
                        'rgb(99, 102, 241)'
                    ];

                    new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: monthLabels,
                            datasets: [
                                {
                                    label: 'Avg Production (mt)',
                                    data: productionData,
                                    backgroundColor: barColors.slice(0, productionData.length),
                                    borderColor: barBorders.slice(0, productionData.length),
                                    borderWidth: 1.5,
                                    borderRadius: 6,
                                    barThickness: 30
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            animation: {
                                duration: 1000,
                                easing: 'easeInOutQuart',
                                delay: (context) => {
                                    let delay = 0;
                                    if (context.type === 'data' && context.mode === 'default') {
                                        delay = context.dataIndex * 80;
                                    }
                                    return delay;
                                }
                            },
                            plugins: {
                                legend: {
                                    display: false
                                },
                                tooltip: {
                                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                    padding: 10,
                                    titleFont: { size: 12 },
                                    bodyFont: { size: 11 },
                                    callbacks: {
                                        label: function(context) {
                                            return context.parsed.y.toFixed(2) + ' mt';
                                        }
                                    }
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    title: {
                                        display: true,
                                        text: 'Production (mt)',
                                        font: {
                                            size: 11,
                                            weight: 'bold'
                                        }
                                    },
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
