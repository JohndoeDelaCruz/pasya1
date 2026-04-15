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

    @php
        $totalPredictions = count($predictions);
        $mlBackedCount = $mlBackedPredictions ?? collect($predictions)->where('prediction_source', 'ml')->count();
        $fallbackCount = max(0, $totalPredictions - $mlBackedCount);
        $mlCoveragePercent = $totalPredictions > 0 ? round(($mlBackedCount / $totalPredictions) * 100, 1) : 0;
        $historicalCount = collect($predictions)->whereNotNull('historical_production')->count();
        $historicalCoveragePercent = $totalPredictions > 0 ? round(($historicalCount / $totalPredictions) * 100, 1) : 0;
        $sourceBreakdown = $sourceCounts ?? [];
        $mlUnavailableCount = (int) ($sourceBreakdown['ml_unavailable'] ?? 0);
        $fallbackDerivedCount = max(0, $fallbackCount - $mlUnavailableCount);
        $strictMlModeEnabled = isset($strictMlMode)
            ? (bool) $strictMlMode
            : filter_var((string) env('ML_STRICT_MODE', 'true'), FILTER_VALIDATE_BOOLEAN);
    @endphp

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
                @elseif($strictMlModeEnabled)
                    <div class="flex items-center gap-2 px-3 py-1.5 bg-red-50 border border-red-200 rounded-lg">
                        <div class="w-2 h-2 bg-red-500 rounded-full"></div>
                        <span class="text-xs font-medium text-red-700">Strict ML Mode • API Unavailable</span>
                    </div>
                @else
                    <div class="flex items-center gap-2 px-3 py-1.5 bg-yellow-50 border border-yellow-200 rounded-lg">
                        <div class="w-2 h-2 bg-yellow-500 rounded-full"></div>
                        <span class="text-xs font-medium text-yellow-700">Fallback Prediction Mode</span>
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
                    <div class="mt-4 flex flex-wrap items-center gap-2 text-xs">
                        <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200">
                            ML-backed: {{ $mlBackedCount }}/{{ $totalPredictions }} ({{ $mlCoveragePercent }}%)
                        </span>
                        <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full bg-slate-50 text-slate-700 border border-slate-200">
                            Historical data: {{ $historicalCount }}/{{ $totalPredictions }} ({{ $historicalCoveragePercent }}%)
                        </span>
                        @if($mlUnavailableCount > 0)
                            <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full bg-red-50 text-red-700 border border-red-200">
                                ML unavailable periods: {{ $mlUnavailableCount }}
                            </span>
                        @endif
                        @if($fallbackDerivedCount > 0)
                            <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full bg-yellow-50 text-yellow-700 border border-yellow-200">
                                Fallback periods: {{ $fallbackDerivedCount }}
                            </span>
                        @endif
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
                        Predicted Production: {{ ucwords(strtolower($filters['crop'])) }}
                    </h2>
                    <p class="text-sm text-gray-500 mt-1">
                        <span class="font-medium text-green-600">Predicted Production</span> (mt) across selected periods
                    </p>
                </div>
                
                <!-- Legend -->
                <div class="flex items-center gap-5">
                    <div class="flex items-center gap-2.5 px-3 py-1.5 bg-blue-50 border border-blue-200 rounded-lg">
                        <svg width="28" height="12" class="flex-shrink-0">
                            <line x1="0" y1="6" x2="28" y2="6" stroke="#3b82f6" stroke-width="2.5" stroke-dasharray="5,3"/>
                            <circle cx="14" cy="6" r="3.5" fill="#3b82f6" stroke="#fff" stroke-width="1.5"/>
                        </svg>
                        <span class="text-sm font-medium text-blue-700">Historical Data</span>
                    </div>
                    <div class="flex items-center gap-2.5 px-3 py-1.5 bg-green-50 border border-green-200 rounded-lg">
                        <svg width="28" height="12" class="flex-shrink-0">
                            <line x1="0" y1="6" x2="28" y2="6" stroke="#16a34a" stroke-width="3"/>
                            <circle cx="14" cy="6" r="4" fill="#16a34a" stroke="#fff" stroke-width="1.5"/>
                        </svg>
                        <span class="text-sm font-medium text-green-700">Predicted Data</span>
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
                    @if($mlUnavailableCount > 0)
                        <span class="text-red-700">{{ $mlUnavailableCount }} period(s) have no ML output due API connectivity or response issues.</span>
                    @endif
                    @if($fallbackDerivedCount > 0)
                        <span class="text-yellow-700">{{ $fallbackDerivedCount }} period(s) are using fallback predictions.</span>
                    @endif
                </div>
                @if(collect($predictions)->whereNotNull('predicted_production')->count() === 0)
                    <span class="text-yellow-600 font-medium">⚠ No predicted data found for these filters</span>
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
                        <p class="font-semibold text-gray-800">How to Read This Data</p>
                        <p class="text-sm text-gray-600">Values show <span class="font-bold text-green-600">predicted productivity and production</span> for each period</p>
                    </div>
                </div>
                <div class="grid grid-cols-1 gap-3 text-sm">
                    <div class="flex items-center gap-2">
                        <svg width="20" height="10" class="flex-shrink-0"><line x1="0" y1="5" x2="20" y2="5" stroke="#3b82f6" stroke-width="2" stroke-dasharray="4,2"/><circle cx="10" cy="5" r="2.5" fill="#3b82f6"/></svg>
                        <span><strong class="text-blue-700">Historical</strong> from crop records (same filter and period)</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <svg width="20" height="10" class="flex-shrink-0"><line x1="0" y1="5" x2="20" y2="5" stroke="#16a34a" stroke-width="2.5"/><circle cx="10" cy="5" r="3" fill="#16a34a"/></svg>
                        <span><strong class="text-green-700">Predicted</strong> from ML API or fallback (depends on source)</span>
                    </div>
                </div>
                @if(!$mlApiHealthy)
                    <p class="mt-3 text-sm text-yellow-700 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                        @if($strictMlModeEnabled)
                            ML API is unavailable. Strict mode keeps affected periods empty (no fallback replacement).
                        @else
                            ML API is unavailable. Fallback predictions may be used.
                        @endif
                    </p>
                @endif
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Period</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Historical Productivity (mt/ha)
                            </th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Predicted Productivity (mt/ha)
                            </th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Historical Production (mt)
                            </th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Production @ {{ number_format($avgAreaHarvested ?? 0, 2) }} ha (mt)
                            </th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Confidence</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Source</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($predictions as $prediction)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ $prediction['month'] }} {{ $prediction['year'] }}
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-sm text-center bg-slate-50/60">
                                    @if($prediction['historical_productivity'])
                                        <span class="text-slate-700 font-medium">{{ number_format($prediction['historical_productivity'], 2) }}</span>
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
                                <td class="px-4 py-4 whitespace-nowrap text-sm text-center bg-slate-50/60">
                                    @if($prediction['historical_production'])
                                        <span class="text-slate-700 font-semibold">{{ number_format($prediction['historical_production'], 2) }}</span>
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
                                <td class="px-4 py-4 whitespace-nowrap text-sm text-center">
                                    @if(isset($prediction['confidence_score']) && $prediction['confidence_score'] !== null)
                                        <span class="inline-flex px-2 py-0.5 text-xs font-semibold rounded-full bg-emerald-50 text-emerald-700">
                                            {{ number_format($prediction['confidence_score'], 1) }}%
                                        </span>
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-sm text-center">
                                    @php
                                        $source = $prediction['prediction_source'] ?? 'unknown';
                                        $sourceLabel = match ($source) {
                                            'ml' => 'ML API',
                                            'ml_unavailable' => 'ML unavailable',
                                            'fallback_historical' => 'Fallback: historical',
                                            'fallback_trend' => 'Fallback: trend',
                                            'fallback_average' => 'Fallback: average',
                                            default => 'Fallback: unavailable',
                                        };
                                        $sourceClass = match ($source) {
                                            'ml' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                            'ml_unavailable' => 'bg-red-50 text-red-700 border-red-200',
                                            default => 'bg-yellow-50 text-yellow-700 border-yellow-200',
                                        };
                                    @endphp
                                    <span class="inline-flex px-2 py-0.5 text-xs font-semibold rounded-full border {{ $sourceClass }}">
                                        {{ $sourceLabel }}
                                    </span>
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
                                    label: 'Historical Production (mt)',
                                    data: historical,
                                    borderColor: 'rgb(59, 130, 246)',
                                    backgroundColor: 'rgba(59, 130, 246, 0.06)',
                                    borderWidth: 2.5,
                                    borderDash: [6, 4],
                                    tension: 0.35,
                                    fill: false,
                                    pointRadius: 4,
                                    pointHoverRadius: 7,
                                    spanGaps: false,
                                    pointStyle: 'rectRot',
                                    pointBackgroundColor: 'rgb(59, 130, 246)',
                                    pointBorderColor: '#fff',
                                    pointBorderWidth: 2,
                                    order: 1
                                },
                                {
                                    label: 'Predicted Production (mt)',
                                    data: predicted,
                                    borderColor: 'rgb(22, 163, 106)',
                                    backgroundColor: function(context) {
                                        const chart = context.chart;
                                        const {ctx: c, chartArea} = chart;
                                        if (!chartArea) return 'rgba(22, 163, 106, 0.15)';
                                        const gradient = c.createLinearGradient(0, chartArea.top, 0, chartArea.bottom);
                                        gradient.addColorStop(0, 'rgba(22, 163, 106, 0.25)');
                                        gradient.addColorStop(1, 'rgba(22, 163, 106, 0.02)');
                                        return gradient;
                                    },
                                    borderWidth: 3,
                                    tension: 0.4,
                                    fill: true,
                                    pointRadius: 5,
                                    pointHoverRadius: 9,
                                    spanGaps: false,
                                    pointStyle: 'circle',
                                    pointBackgroundColor: 'rgb(22, 163, 106)',
                                    pointBorderColor: '#fff',
                                    pointBorderWidth: 2.5,
                                    order: 0
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
                                    display: false
                                },
                                tooltip: {
                                    backgroundColor: 'rgba(15, 23, 42, 0.92)',
                                    padding: 14,
                                    cornerRadius: 8,
                                    titleFont: {
                                        size: 13,
                                        weight: 'bold'
                                    },
                                    bodyFont: {
                                        size: 13
                                    },
                                    bodySpacing: 6,
                                    usePointStyle: true,
                                    callbacks: {
                                        title: function(items) {
                                            return items[0].label;
                                        },
                                        label: function(context) {
                                            const val = context.parsed.y;
                                            if (val === null || val === undefined) return null;
                                            const prefix = context.datasetIndex === 0 ? '◆ Historical' : '● Predicted';
                                            return prefix + ': ' + val.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' mt';
                                        },
                                        afterBody: function(items) {
                                            const hist = items.find(i => i.datasetIndex === 0);
                                            const pred = items.find(i => i.datasetIndex === 1);
                                            if (hist && pred && hist.parsed.y != null && pred.parsed.y != null) {
                                                const diff = pred.parsed.y - hist.parsed.y;
                                                const pct = hist.parsed.y !== 0 ? ((diff / hist.parsed.y) * 100).toFixed(1) : 'N/A';
                                                const arrow = diff >= 0 ? '▲' : '▼';
                                                return ['', arrow + ' Difference: ' + diff.toFixed(2) + ' mt (' + pct + '%)'];
                                            }
                                            return [];
                                        },
                                        labelColor: function(context) {
                                            return {
                                                borderColor: context.datasetIndex === 0 ? 'rgb(59, 130, 246)' : 'rgb(22, 163, 106)',
                                                backgroundColor: context.datasetIndex === 0 ? 'rgb(59, 130, 246)' : 'rgb(22, 163, 106)',
                                                borderWidth: 2,
                                                borderRadius: context.datasetIndex === 0 ? 0 : 4
                                            };
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
