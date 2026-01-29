<x-admin-layout>
    <x-slot name="title">Recommendations</x-slot>

    @push('styles')
    <style>
        [x-cloak] { display: none !important; }
        
        /* Custom scrollbar for weather cards */
        #weatherCardsContainer::-webkit-scrollbar {
            height: 8px;
        }
        #weatherCardsContainer::-webkit-scrollbar-track {
            background: #F1F5F9;
            border-radius: 4px;
        }
        #weatherCardsContainer::-webkit-scrollbar-thumb {
            background: #CBD5E1;
            border-radius: 4px;
        }
        #weatherCardsContainer::-webkit-scrollbar-thumb:hover {
            background: #94A3B8;
        }
        
        /* Hide scrollbar for cleaner look but keep functionality */
        .hide-scrollbar::-webkit-scrollbar {
            display: none;
        }
        .hide-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
    </style>
    @endpush

    <div class="space-y-6" 
         x-data="{ showSubsidyModal: {{ $errors->any() ? 'true' : 'false' }} }"
         x-init="console.log('Alpine initialized.')">
        <!-- Success Message -->
        @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg" role="alert">
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <span>{{ session('success') }}</span>
                </div>
            </div>
        @endif

        <!-- Validation Errors -->
        @if($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg" role="alert">
                <div class="flex items-start">
                    <svg class="w-5 h-5 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                    <div>
                        <h4 class="font-semibold">Please fix the following errors:</h4>
                        <ul class="mt-2 list-disc list-inside text-sm">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        <!-- Page Header -->
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold text-gray-800">Recommendations</h1>
        </div>

        <!-- Climate Resilience Section -->
        <div>
            <h2 class="text-xl font-bold text-gray-800 mb-6 flex items-center gap-2">
                <span class="text-2xl">🌤️</span> Climate Resilience
                <span class="text-xs bg-green-100 text-green-700 px-2 py-1 rounded-full font-medium ml-2">Live Weather Data</span>
            </h2>
            
            <!-- Municipality Weather Cards - Horizontal Scroll -->
            <div class="bg-white rounded-xl border border-gray-200 p-6 mb-6 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-semibold text-gray-600 uppercase tracking-wider">Benguet Municipality Weather</h3>
                    <div class="flex items-center gap-3">
                        <span class="text-xs text-gray-500">Updated: {{ now()->format('g:i A') }}</span>
                        <div class="flex items-center gap-1">
                            <button onclick="scrollWeatherLeft()" class="p-1.5 rounded-lg bg-gray-100 hover:bg-gray-200 transition text-gray-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                                </svg>
                            </button>
                            <button onclick="scrollWeatherRight()" class="p-1.5 rounded-lg bg-gray-100 hover:bg-gray-200 transition text-gray-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Scrollable Weather Cards Container -->
                <div id="weatherCardsContainer" class="flex gap-4 overflow-x-auto pb-4 scroll-smooth snap-x snap-mandatory" style="scrollbar-width: thin; scrollbar-color: #CBD5E1 #F1F5F9;">
                    @foreach($municipalityWeather as $weather)
                    <!-- {{ $weather['municipality'] }} Weather Card -->
                    <div class="flex-shrink-0 w-72 snap-start bg-gradient-to-br from-sky-50 to-blue-50 border border-sky-200 rounded-xl p-4 hover:shadow-md transition-shadow">
                        <div class="flex items-center justify-between mb-3">
                            <div>
                                <div class="text-xs font-medium text-sky-600 uppercase tracking-wider">BENGUET</div>
                                <div class="text-lg font-bold text-gray-800">{{ $weather['municipality'] }}</div>
                            </div>
                            <div class="w-10 h-10 bg-white/80 rounded-lg flex items-center justify-center shadow-sm">
                                <span class="text-2xl">{{ $weather['forecast'][0]['icon'] ?? '⛅' }}</span>
                            </div>
                        </div>
                        
                        <!-- Current Temp Display -->
                        @if(isset($weather['forecast'][0]))
                        <div class="mb-3 p-3 bg-white/60 rounded-lg">
                            <div class="flex items-center justify-between">
                                <div>
                                    <span class="text-2xl font-bold text-gray-800">{{ $weather['forecast'][0]['temp'] }}</span>
                                    <p class="text-xs text-gray-500 mt-1">{{ $weather['forecast'][0]['condition'] }}</p>
                                </div>
                                <div class="text-right">
                                    <div class="text-xs text-gray-500">AQI</div>
                                    <div class="text-lg font-semibold {{ $weather['forecast'][0]['aqi'] < 50 ? 'text-green-600' : ($weather['forecast'][0]['aqi'] < 100 ? 'text-yellow-600' : 'text-red-600') }}">
                                        {{ $weather['forecast'][0]['aqi'] }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                        
                        <!-- 4-Day Forecast -->
                        <div class="grid grid-cols-4 gap-1">
                            @foreach($weather['forecast'] as $index => $day)
                            <div class="text-center p-1.5 rounded-lg {{ $index === 0 ? 'bg-sky-100' : 'bg-white/50' }} hover:bg-sky-100 transition">
                                <div class="text-[10px] font-medium text-gray-500 mb-0.5">{{ Str::limit($day['day'], 3, '') }}</div>
                                <div class="text-base mb-0.5">{{ $day['icon'] }}</div>
                                <div class="text-[10px] font-medium text-gray-700">{{ $day['temp'] }}</div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                </div>
                
                <!-- Scroll Indicator Dots -->
                <div class="flex justify-center gap-1.5 mt-3">
                    @foreach($municipalityWeather as $index => $weather)
                    <div class="w-2 h-2 rounded-full {{ $index < 3 ? 'bg-sky-400' : 'bg-gray-300' }} transition-colors"></div>
                    @endforeach
                </div>
            </div>

            <!-- Hourly Forecast & Recommendations - Enhanced Design -->
            <div class="bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500 rounded-xl p-6 mb-6 shadow-lg">
                <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
                    
                    <!-- Hourly Weather -->
                    <div class="lg:col-span-2">
                        <h4 class="text-white font-semibold mb-3 flex items-center gap-2">
                            <span>🕐</span> Hourly Forecast
                        </h4>
                        <div class="grid grid-cols-6 gap-2 mb-4">
                            @foreach($hourlyForecast as $index => $hour)
                            <div class="text-center p-3 rounded-xl {{ $index === 0 ? 'bg-white/30' : 'bg-white/10' }} backdrop-blur-sm text-white hover:bg-white/25 transition">
                                <div class="text-xs font-medium mb-1.5 {{ $index === 0 ? 'text-yellow-200' : '' }}">{{ $hour['time'] }}</div>
                                <div class="text-2xl mb-1.5">{{ $hour['icon'] }}</div>
                                <div class="text-sm font-bold">{{ $hour['temp'] }}</div>
                            </div>
                            @endforeach
                        </div>
                        
                        <!-- Municipality Search -->
                        <div class="relative">
                            <input type="text" placeholder="Search municipality weather..." 
                                   class="w-full px-4 py-3 pl-10 rounded-xl border-0 bg-white/90 backdrop-blur-sm focus:outline-none focus:ring-2 focus:ring-yellow-400 text-gray-700 placeholder-gray-500 shadow-sm">
                            <svg class="w-5 h-5 text-gray-400 absolute left-3 top-1/2 transform -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                    </div>

                    <!-- Recommendations Panel - Enhanced -->
                    <div class="lg:col-span-2 bg-white rounded-xl p-5 shadow-lg">
                        <h4 class="font-bold text-gray-800 mb-4 flex items-center gap-2">
                            <span class="text-xl">🌱</span> Planting Recommendations
                        </h4>
                        <div class="space-y-4">
                            <div class="flex items-start gap-3 p-3 bg-green-50 rounded-lg">
                                <span class="text-2xl">⏰</span>
                                <div>
                                    <div class="text-sm font-semibold text-gray-700">Optimal Planting Window</div>
                                    <div class="text-base font-bold text-green-600">{{ $optimalWindow }}</div>
                                </div>
                            </div>
                            <div class="flex items-start gap-3 p-3 bg-amber-50 rounded-lg">
                                <span class="text-2xl">🥬</span>
                                <div>
                                    <div class="text-sm font-semibold text-gray-700">Best Crops for Season</div>
                                    <div class="text-sm text-gray-600">{{ $bestCrops }}</div>
                                </div>
                            </div>
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <div class="flex items-center gap-3">
                                    <span class="text-2xl">⚠️</span>
                                    <div>
                                        <div class="text-sm font-semibold text-gray-700">Climate Risk Level</div>
                                        <div class="text-xs text-gray-500">Based on weather forecast</div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-2xl font-bold {{ $climateRisk < 30 ? 'text-green-600' : ($climateRisk < 60 ? 'text-yellow-600' : 'text-red-600') }}">{{ $climateRisk }}%</div>
                                    <div class="text-xs {{ $climateRisk < 30 ? 'text-green-500' : ($climateRisk < 60 ? 'text-yellow-500' : 'text-red-500') }}">
                                        {{ $climateRisk < 30 ? 'Low Risk' : ($climateRisk < 60 ? 'Moderate' : 'High Risk') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button class="w-full mt-4 px-4 py-3 bg-gradient-to-r from-yellow-400 to-orange-400 hover:from-yellow-500 hover:to-orange-500 text-gray-800 font-bold rounded-xl transition-all shadow-md hover:shadow-lg flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                            Generate Recommendations
                        </button>
                    </div>

                </div>
            </div>
        </div>

        <!-- Resource Allocation Section -->
        <div class="bg-white rounded-xl shadow-md p-6">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-lg font-semibold text-gray-800">📊 Bar Chart Of Resource Planning</h2>
                    <div class="mt-2">
                        <h3 class="text-base font-semibold text-gray-700 mb-1">Allocation vs. Need</h3>
                        <p class="text-sm text-gray-600">Per Crop</p>
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    <div class="flex items-center gap-2">
                        <div class="w-4 h-4 bg-yellow-500 rounded"></div>
                        <span class="text-sm text-gray-600">Needed Seeds (kg)</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-4 h-4 bg-orange-500 rounded"></div>
                        <span class="text-sm text-gray-600">Allocated Seeds (kg)</span>
                    </div>
                </div>
            </div>

            <!-- Bar Chart -->
            <div class="h-80 relative mb-4">
                <canvas id="allocationChart"></canvas>
            </div>

            <!-- Allocation Info -->
            <div class="flex items-center justify-between text-sm text-gray-600">
                <p>Allocation down by 5.2% this month</p>
                <p>Showing allocations per crop for this month</p>
            </div>

            <!-- Export Button -->
            <div class="mt-4 flex justify-end gap-2">
                <select class="px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
                    <option>Type of file</option>
                    <option>PDF</option>
                    <option>Excel</option>
                    <option>CSV</option>
                </select>
                <button class="px-6 py-2 bg-yellow-400 hover:bg-yellow-500 text-gray-800 font-semibold rounded-lg transition-colors">
                    Export data
                </button>
            </div>
        </div>

        <!-- Policy Dashboard Section -->
        <div class="bg-white rounded-xl shadow-md p-6">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-lg font-semibold text-gray-800">Policy Dashboard</h2>
                <button type="button" 
                        @click="showSubsidyModal = true" 
                        class="px-4 py-2 bg-yellow-400 hover:bg-yellow-500 text-gray-800 font-semibold rounded-lg transition-colors flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Allocate Subsidy
                </button>
            </div>

            <!-- Filters and Actions Row -->
            <form method="GET" action="{{ route('admin.recommendations') }}" class="mb-6">
                <div class="flex items-center gap-3">
                    <!-- Name Filter -->
                    <div class="flex-1">
                        <input type="text" name="name" placeholder="Name" value="{{ request('name') }}" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-gray-300">
                    </div>

                    <!-- ID Filter -->
                    <div class="flex-1">
                        <input type="text" name="id" placeholder="ID" value="{{ request('id') }}" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-gray-300">
                    </div>

                    <!-- Crop Filter -->
                    <div class="flex-1">
                        <select name="crop" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-gray-300">
                            <option value="">Crop</option>
                            @foreach($crops as $crop)
                                <option value="{{ $crop }}" {{ $filterCrop == $crop ? 'selected' : '' }}>{{ ucwords(strtolower($crop)) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Status Filter -->
                    <div class="flex-1">
                        <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-gray-300">
                            <option value="">Status</option>
                            <option value="Approved" {{ $filterStatus == 'Approved' ? 'selected' : '' }}>Approved</option>
                            <option value="Pending" {{ $filterStatus == 'Pending' ? 'selected' : '' }}>Pending</option>
                            <option value="Rejected" {{ $filterStatus == 'Rejected' ? 'selected' : '' }}>Rejected</option>
                        </select>
                    </div>

                    <!-- Reset and Filter Buttons -->
                    <div class="flex items-center gap-2">
                        <a href="{{ route('admin.recommendations') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md hover:bg-gray-50 text-gray-700 text-sm transition-colors">
                            Reset
                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </a>
                        <button type="submit" class="px-4 py-2 text-gray-600 text-sm hover:text-gray-800">
                            View
                        </button>
                    </div>
                </div>
            </form>

            <!-- Data Table -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-white border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-3 text-left w-10">
                                <input type="checkbox" class="rounded border-gray-300">
                            </th>
                            <th class="px-6 py-3 text-left">
                                <button class="flex items-center gap-1 text-xs font-medium text-gray-600 uppercase tracking-wider hover:text-gray-900">
                                    Full Name
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"/>
                                    </svg>
                                </button>
                            </th>
                            <th class="px-6 py-3 text-left">
                                <button class="flex items-center gap-1 text-xs font-medium text-gray-600 uppercase tracking-wider hover:text-gray-900">
                                    Crop
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"/>
                                    </svg>
                                </button>
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Subsidy Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Subsidy Amt</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Last Updated</th>
                            <th class="px-6 py-3 text-left w-10"></th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($subsidies as $subsidy)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <input type="checkbox" class="rounded border-gray-300">
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $subsidy->full_name }}</div>
                                    <div class="text-xs text-gray-500">{{ $subsidy->farmer_id }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $subsidy->crop_display }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($subsidy->subsidy_status == 'Approved')
                                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">
                                            Approved
                                        </span>
                                    @elseif($subsidy->subsidy_status == 'Pending')
                                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-800">
                                            Pending
                                        </span>
                                    @else
                                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-800">
                                            Rejected
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        @if($subsidy->subsidy_amount)
                                            ₱{{ number_format($subsidy->subsidy_amount, 0) }}
                                        @else
                                            <span class="text-gray-400">—</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $subsidy->updated_at->format('Y-m-d') }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    <button class="text-gray-400 hover:text-gray-600">
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"/>
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    <p class="mt-2 text-sm">No subsidy records found.</p>
                                    <p class="text-xs text-gray-400 mt-1">Try adjusting your filters or click "Allocate Subsidy" to create a new record.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination Footer -->
            <div class="mt-4 flex items-center justify-between border-t border-gray-200 pt-4">
                <div class="text-sm text-gray-700">
                    @if($subsidies->total() > 0)
                        0 of {{ $subsidies->total() }} row(s) selected.
                    @else
                        0 of 0 row(s) selected.
                    @endif
                </div>
                
                <div class="flex items-center gap-6">
                    <div class="flex items-center gap-2">
                        <span class="text-sm text-gray-700">Rows per page</span>
                        <select class="px-2 py-1 border border-gray-300 rounded text-sm focus:outline-none focus:ring-1 focus:ring-gray-300">
                            <option>10</option>
                            <option>25</option>
                            <option>50</option>
                            <option>100</option>
                        </select>
                    </div>

                    <div class="text-sm text-gray-700">
                        Page {{ $subsidies->currentPage() }} of {{ $subsidies->lastPage() }}
                    </div>

                    <div class="flex items-center gap-1">
                        <a href="{{ $subsidies->url(1) }}" class="p-1 rounded hover:bg-gray-100 {{ $subsidies->onFirstPage() ? 'text-gray-300 cursor-not-allowed' : 'text-gray-700' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/>
                            </svg>
                        </a>
                        <a href="{{ $subsidies->previousPageUrl() }}" class="p-1 rounded hover:bg-gray-100 {{ $subsidies->onFirstPage() ? 'text-gray-300 cursor-not-allowed' : 'text-gray-700' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                            </svg>
                        </a>
                        <a href="{{ $subsidies->nextPageUrl() }}" class="p-1 rounded hover:bg-gray-100 {{ !$subsidies->hasMorePages() ? 'text-gray-300 cursor-not-allowed' : 'text-gray-700' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                        <a href="{{ $subsidies->url($subsidies->lastPage()) }}" class="p-1 rounded hover:bg-gray-100 {{ !$subsidies->hasMorePages() ? 'text-gray-300 cursor-not-allowed' : 'text-gray-700' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"/>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Allocate Subsidy Modal -->
        <div x-show="showSubsidyModal" 
             x-cloak
             @keydown.escape.window="showSubsidyModal = false"
             class="fixed inset-0 z-50 overflow-y-auto" 
             aria-labelledby="modal-title" 
             role="dialog" 
             aria-modal="true">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <!-- Background overlay -->
                <div x-show="showSubsidyModal"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     @click="showSubsidyModal = false"
                     class="fixed inset-0 bg-black bg-opacity-60 transition-opacity" 
                     aria-hidden="true"></div>

                <!-- Center modal -->
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <!-- Modal panel -->
                <div x-show="showSubsidyModal"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     class="inline-block align-bottom bg-white rounded-lg text-left overflow-visible shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-3xl sm:w-full"
                     @click.away="showSubsidyModal = false">
                    
                    <div class="bg-white px-10 py-8 rounded-lg">
                        <form method="POST" action="{{ route('admin.subsidies.store') }}" class="space-y-6">
                            @csrf

                            <!-- Header -->
                            <div class="pb-3 border-b border-gray-200">
                                <h3 class="text-base font-semibold text-gray-900">Allocate Subsidy</h3>
                                <p class="text-sm text-gray-600 mt-1">All required fields are marked with *</p>
                            </div>

                            @if(session('success'))
                                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                                    {{ session('success') }}
                                </div>
                            @endif

                            @if($errors->any())
                                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                                    <ul class="list-disc list-inside text-sm">
                                        @foreach($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <!-- Row 1: Full Name, Farmer ID -->
                            <div class="grid grid-cols-2 gap-6">
                                <!-- Full Name -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-900 mb-2">
                                        Full Name*
                                    </label>
                                    <input type="text" 
                                           name="full_name" 
                                           value="{{ old('full_name') }}" 
                                           required 
                                           placeholder="Enter full name"
                                           class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-md text-sm text-gray-700 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-300 focus:border-transparent focus:bg-white transition-colors">
                                </div>

                                <!-- Farmer ID -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-900 mb-2">
                                        Farmer ID*
                                    </label>
                                    <input type="text" 
                                           name="farmer_id" 
                                           value="{{ old('farmer_id') }}" 
                                           required 
                                           placeholder="ID-COOP-XXXX"
                                           class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-md text-sm text-gray-700 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-300 focus:border-transparent focus:bg-white transition-colors">
                                </div>
                            </div>

                            <!-- Row 2: Municipality, Farm Type, Year, Crop -->
                            <div class="grid grid-cols-4 gap-6">
                                <!-- Municipality -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-900 mb-2">
                                        Municipality*
                                    </label>
                                    <select name="municipality" 
                                            required 
                                            class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-md text-sm text-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-300 focus:border-transparent focus:bg-white transition-colors">
                                        <option value="" class="text-gray-400">Municipality</option>
                                        @foreach($municipalities as $municipality)
                                            <option value="{{ $municipality }}" class="text-gray-700" {{ old('municipality') == $municipality ? 'selected' : '' }}>
                                                {{ ucwords(strtolower($municipality)) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Farm Type -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-900 mb-2">
                                        Farm Type*
                                    </label>
                                    <select name="farm_type" 
                                            required 
                                            class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-md text-sm text-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-300 focus:border-transparent focus:bg-white transition-colors">
                                        <option value="" class="text-gray-400">Select farm type</option>
                                        <option value="Rainfed" class="text-gray-700" {{ old('farm_type') == 'Rainfed' ? 'selected' : '' }}>Rainfed</option>
                                        <option value="Irrigated" class="text-gray-700" {{ old('farm_type') == 'Irrigated' ? 'selected' : '' }}>Irrigated</option>
                                    </select>
                                </div>

                                <!-- Year -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-900 mb-2">
                                        Year*
                                    </label>
                                    <input type="number" 
                                           name="year" 
                                           value="{{ old('year', date('Y')) }}" 
                                           required 
                                           min="2000" 
                                           max="2050"
                                           placeholder="Year"
                                           class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-md text-sm text-gray-700 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-300 focus:border-transparent focus:bg-white transition-colors">
                                </div>

                                <!-- Crop -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-900 mb-2">
                                        Crop*
                                    </label>
                                    <select name="crop" 
                                            required 
                                            class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-md text-sm text-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-300 focus:border-transparent focus:bg-white transition-colors">
                                        <option value="" class="text-gray-400">Crop</option>
                                        @foreach($crops as $crop)
                                            <option value="{{ $crop }}" class="text-gray-700" {{ old('crop') == $crop ? 'selected' : '' }}>
                                                {{ ucwords(strtolower($crop)) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <!-- Row 3: Area Planted, Area Harvested -->
                            <div class="grid grid-cols-2 gap-6">
                                <!-- Area Planted (ha) -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-900 mb-2">
                                        Area Planted (ha)*
                                    </label>
                                    <input type="number" 
                                           name="area_planted" 
                                           value="{{ old('area_planted') }}" 
                                           required 
                                           step="0.01"
                                           min="0"
                                           placeholder="Enter a number"
                                           class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-md text-sm text-gray-700 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-300 focus:border-transparent focus:bg-white transition-colors">
                                </div>

                                <!-- Area Harvested (ha) -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-900 mb-2">
                                        Area Harvested (ha)*
                                    </label>
                                    <input type="number" 
                                           name="area_harvested" 
                                           value="{{ old('area_harvested') }}" 
                                           required 
                                           step="0.01"
                                           min="0"
                                           placeholder="Enter a number"
                                           class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-md text-sm text-gray-700 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-300 focus:border-transparent focus:bg-white transition-colors">
                                </div>
                            </div>

                            <!-- Row 4: Production, Productivity -->
                            <div class="grid grid-cols-2 gap-6">
                                <!-- Production (mt) -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-900 mb-2">
                                        Production (mt)*
                                    </label>
                                    <input type="number" 
                                           name="production" 
                                           value="{{ old('production') }}" 
                                           required 
                                           step="0.01"
                                           min="0"
                                           placeholder="Enter a number"
                                           class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-md text-sm text-gray-700 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-300 focus:border-transparent focus:bg-white transition-colors">
                                </div>

                                <!-- Productivity (mt/ha) -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-900 mb-2">
                                        Productivity (mt/ha)
                                    </label>
                                    <input type="number" 
                                           name="productivity" 
                                           value="{{ old('productivity') }}" 
                                           step="0.01"
                                           min="0"
                                           placeholder="Enter a number"
                                           class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-md text-sm text-gray-700 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-300 focus:border-transparent focus:bg-white transition-colors">
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <div class="pt-2">
                                <button type="submit" 
                                        class="px-8 py-2.5 bg-yellow-400 hover:bg-yellow-500 text-gray-900 font-semibold rounded-md transition-colors shadow-sm">
                                    Submit
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Weather Cards Scroll Functions
        function scrollWeatherLeft() {
            const container = document.getElementById('weatherCardsContainer');
            if (container) {
                container.scrollBy({ left: -300, behavior: 'smooth' });
            }
        }
        
        function scrollWeatherRight() {
            const container = document.getElementById('weatherCardsContainer');
            if (container) {
                container.scrollBy({ left: 300, behavior: 'smooth' });
            }
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            console.log('=== RECOMMENDATIONS PAGE LOADED ===');
            
            // Allocation vs Need Bar Chart
            const ctx = document.getElementById('allocationChart');
            if (ctx) {
                console.log('Loading allocation chart...');
                
                const labels = @json($allocationData['labels']);
                const needed = @json($allocationData['needed']);
                const allocated = @json($allocationData['allocated']);
                
                console.log('Chart Data:', { labels, needed, allocated });
                
                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [
                            {
                                label: 'Needed Seeds (kg)',
                                data: needed,
                                backgroundColor: 'rgba(234, 179, 8, 0.8)', // Yellow
                                borderColor: 'rgb(234, 179, 8)',
                                borderWidth: 1,
                                borderRadius: 4
                            },
                            {
                                label: 'Allocated Seeds (kg)',
                                data: allocated,
                                backgroundColor: 'rgba(249, 115, 22, 0.8)', // Orange
                                borderColor: 'rgb(249, 115, 22)',
                                borderWidth: 1,
                                borderRadius: 4
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        animation: {
                            duration: 1200,
                            easing: 'easeInOutQuart',
                            delay: (context) => {
                                let delay = 0;
                                if (context.type === 'data' && context.mode === 'default') {
                                    delay = context.dataIndex * 100 + context.datasetIndex * 150;
                                }
                                return delay;
                            }
                        },
                        plugins: {
                            legend: {
                                display: false // Using custom legend
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
                                        return context.dataset.label + ': ' + context.parsed.y + ' kg';
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                type: 'logarithmic',
                                beginAtZero: false,
                                min: 0.1,
                                title: {
                                    display: true,
                                    text: 'Seeds (kg) - Log Scale',
                                    font: {
                                        size: 12,
                                        weight: 'bold'
                                    }
                                },
                                grid: {
                                    color: 'rgba(0, 0, 0, 0.05)'
                                },
                                ticks: {
                                    font: {
                                        size: 11
                                    },
                                    callback: function(value, index, ticks) {
                                        if (value === 0.1 || value === 1 || value === 10 || value === 100 || value === 1000 || value === 10000 || value === 100000) {
                                            return value.toLocaleString() + ' kg';
                                        }
                                        return '';
                                    }
                                }
                            },
                            x: {
                                title: {
                                    display: true,
                                    text: 'Crops',
                                    font: {
                                        size: 12,
                                        weight: 'bold'
                                    }
                                },
                                grid: {
                                    display: false
                                },
                                ticks: {
                                    font: {
                                        size: 11
                                    }
                                }
                            }
                        }
                    }
                });
            }
        });
    </script>
    @endpush
</x-admin-layout>
