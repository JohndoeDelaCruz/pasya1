<x-admin-layout>
    <x-slot name="title">Data & Analytics</x-slot>

    <div class="space-y-6" x-data="dataAnalytics()">
        <!-- Page Header with Filters -->
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Dashboard</h1>
            </div>
            
            <!-- Filter Controls and Action Buttons -->
            <div class="flex flex-wrap items-center gap-3">
                <!-- Allocate Resource Button -->
                <button class="px-4 py-2 bg-yellow-400 hover:bg-yellow-500 text-gray-800 font-semibold rounded-lg transition-colors shadow-sm">
                    Allocate Resource
                </button>
                
                <!-- Recommend Button -->
                <button onclick="document.getElementById('predictions-section')?.scrollIntoView({ behavior: 'smooth' })" class="px-4 py-2 bg-yellow-500 hover:bg-yellow-600 text-white font-semibold rounded-lg transition-colors shadow-sm">
                    Recommend
                </button>
            </div>
        </div>

        <!-- Filter Bar -->
        <form method="GET" action="{{ route('admin.dashboard') }}" id="filterForm" class="bg-white rounded-lg shadow-sm p-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <label class="text-sm text-gray-700 font-medium whitespace-nowrap">Filter by:</label>
            </div>
            
            <div class="flex items-center gap-3 flex-wrap">
                <!-- Crop Filter -->
                <div class="relative">
                    <select name="crop" onchange="document.getElementById('filterForm').submit()" class="appearance-none px-4 py-2.5 pr-10 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-500 focus:border-transparent bg-white text-gray-700 cursor-pointer min-w-[150px]">
                        <option value="">Crop</option>
                        @foreach(App\Models\Crop::select('crop')->distinct()->orderBy('crop')->pluck('crop') as $crop)
                            <option value="{{ $crop }}" {{ request('crop') == $crop ? 'selected' : '' }}>
                                {{ ucwords(strtolower($crop)) }}
                            </option>
                        @endforeach
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-500">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                </div>
                
                <!-- Municipality Filter -->
                <div class="relative">
                    <select name="municipality" onchange="document.getElementById('filterForm').submit()" class="appearance-none px-4 py-2.5 pr-10 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-500 focus:border-transparent bg-white text-gray-700 cursor-pointer min-w-[150px]">
                        <option value="">Municipality</option>
                        @foreach($allMunicipalities as $municipality)
                            <option value="{{ $municipality }}" {{ $filterMunicipality == $municipality ? 'selected' : '' }}>
                                {{ ucwords(strtolower($municipality)) }}
                            </option>
                        @endforeach
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-500">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                </div>
                
                <!-- Month Filter -->
                <div class="relative">
                    <select name="month" onchange="document.getElementById('filterForm').submit()" class="appearance-none px-4 py-2.5 pr-10 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-500 focus:border-transparent bg-white text-gray-700 cursor-pointer min-w-[150px]">
                        <option value="">Month</option>
                        <option value="January" {{ $filterMonth == 'January' ? 'selected' : '' }}>January</option>
                        <option value="February" {{ $filterMonth == 'February' ? 'selected' : '' }}>February</option>
                        <option value="March" {{ $filterMonth == 'March' ? 'selected' : '' }}>March</option>
                        <option value="April" {{ $filterMonth == 'April' ? 'selected' : '' }}>April</option>
                        <option value="May" {{ $filterMonth == 'May' ? 'selected' : '' }}>May</option>
                        <option value="June" {{ $filterMonth == 'June' ? 'selected' : '' }}>June</option>
                        <option value="July" {{ $filterMonth == 'July' ? 'selected' : '' }}>July</option>
                        <option value="August" {{ $filterMonth == 'August' ? 'selected' : '' }}>August</option>
                        <option value="September" {{ $filterMonth == 'September' ? 'selected' : '' }}>September</option>
                        <option value="October" {{ $filterMonth == 'October' ? 'selected' : '' }}>October</option>
                        <option value="November" {{ $filterMonth == 'November' ? 'selected' : '' }}>November</option>
                        <option value="December" {{ $filterMonth == 'December' ? 'selected' : '' }}>December</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-500">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                </div>
                
                <!-- Year Filter -->
                <div class="relative">
                    <select name="year" onchange="document.getElementById('filterForm').submit()" class="appearance-none px-4 py-2.5 pr-10 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-500 focus:border-transparent bg-white text-gray-700 cursor-pointer min-w-[150px]">
                        <option value="">Year</option>
                        @foreach($allYears as $year)
                            <option value="{{ $year }}" {{ $filterYear == $year ? 'selected' : '' }}>{{ $year }}</option>
                        @endforeach
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-500">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                </div>
                
                <!-- Reset Button -->
                <a href="{{ route('admin.dashboard') }}" class="px-6 py-2.5 bg-yellow-400 hover:bg-yellow-500 text-gray-800 font-semibold rounded-lg transition-colors shadow-sm whitespace-nowrap">
                    Reset
                </a>
            </div>
        </form>

        <!-- Trend Analysis Line Chart -->
        <div class="bg-white rounded-xl shadow-md p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-gray-800">
                    @if($chartMode === 'monthly')
                        Monthly Production Trend ({{ $filterYear }})
                    @else
                        Trend Analysis Line Chart
                    @endif
                </h2>
                <button class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </button>
            </div>
            
            <div class="mb-4">
                <h3 class="text-base font-medium text-gray-700">Production</h3>
                <p class="text-sm text-gray-500">
                    @if($chartMode === 'monthly')
                        Monthly production breakdown for {{ $filterYear }}
                    @else
                        Each municipality's seasonal productivity per year
                    @endif
                </p>
                <div class="flex items-center gap-2 mt-2">
                    @if($productionTrend != 0)
                        <span class="text-sm text-gray-600">
                            Production {{ $productionTrend > 0 ? 'up' : 'down' }} {{ number_format(abs($productionTrend), 1) }}% compared to last year
                        </span>
                        @if($productionTrend > 0)
                            <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
                            </svg>
                        @else
                            <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                            </svg>
                        @endif
                    @else
                        <span class="text-sm text-gray-600">Showing production trends based on uploaded data</span>
                    @endif
                    <span class="text-xs text-gray-400">Showing year-over-year comparison</span>
                </div>
            </div>
            
            <!-- Chart Container -->
            @if(count($years) > 0 && count($municipalities) > 0)
                <div class="h-80 relative">
                    <canvas id="trendChart"></canvas>
                </div>
            @else
                <div class="h-80 flex items-center justify-center bg-gray-50 rounded-lg border-2 border-dashed border-gray-200">
                    <div class="text-center px-4">
                        <svg class="w-16 h-16 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                        <p class="text-gray-600 font-semibold mb-1">No data matches your filters</p>
                        <p class="text-sm text-gray-500 mb-4">
                            @if($filterMunicipality || $filterMonth || $filterYear)
                                Try adjusting your filters or clear them to see all data
                            @else
                                Upload crop production data to see trends and analytics
                            @endif
                        </p>
                        @if($filterMunicipality || $filterMonth || $filterYear)
                            <a href="{{ route('admin.dashboard') }}" class="inline-block px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg text-sm transition-colors">
                                Clear Filters
                            </a>
                        @else
                            <a href="{{ route('admin.crop-data.upload') }}" class="inline-block px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg text-sm transition-colors">
                                Upload Data
                            </a>
                        @endif
                    </div>
                </div>
            @endif
            
            <!-- Chart Legend - Dynamically generated from database -->
            <div class="flex flex-wrap items-center gap-4 mt-4 justify-center">
                @php
                    $legendColors = [
                        'bg-blue-500', 'bg-green-500', 'bg-yellow-500', 
                        'bg-purple-500', 'bg-pink-500', 'bg-orange-500',
                        'bg-sky-500', 'bg-violet-500', 'bg-rose-500', 'bg-amber-500'
                    ];
                @endphp
                @foreach($municipalities as $index => $municipality)
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-1 {{ $legendColors[$index % count($legendColors)] }}"></div>
                        <span class="text-xs text-gray-600">{{ ucwords(strtolower($municipality)) }}</span>
                    </div>
                @endforeach
            </div>

            <!-- Production Trend Indicator -->
            @if(count($years) > 0 && $productionTrend !== null)
                <div class="mt-4 flex items-center justify-center">
                    <div class="flex items-center gap-2 text-sm">
                        @if($productionTrend > 0)
                            <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M12 7a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0V8.414l-4.293 4.293a1 1 0 01-1.414 0L8 10.414l-4.293 4.293a1 1 0 01-1.414-1.414l5-5a1 1 0 011.414 0L11 10.586 14.586 7H12z" clip-rule="evenodd"/>
                            </svg>
                            <span class="font-semibold text-gray-800">Production up by {{ number_format(abs($productionTrend), 1) }}% this {{ $chartMode === 'monthly' ? 'month' : 'year' }}</span>
                        @elseif($productionTrend < 0)
                            <svg class="w-5 h-5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M12 13a1 1 0 100 2h5a1 1 0 001-1V9a1 1 0 10-2 0v2.586l-4.293-4.293a1 1 0 00-1.414 0L8 9.586 3.707 5.293a1 1 0 00-1.414 1.414l5 5a1 1 0 001.414 0L11 9.414 14.586 13H12z" clip-rule="evenodd"/>
                            </svg>
                            <span class="font-semibold text-gray-800">Production down by {{ number_format(abs($productionTrend), 1) }}% this {{ $chartMode === 'monthly' ? 'month' : 'year' }}</span>
                        @else
                            <svg class="w-5 h-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M3 10a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"/>
                            </svg>
                            <span class="font-semibold text-gray-800">Production unchanged this {{ $chartMode === 'monthly' ? 'month' : 'year' }}</span>
                        @endif
                    </div>
                </div>
                <div class="text-center mt-1">
                    <span class="text-xs text-gray-500">
                        Showing summarized comparison for {{ $filterCrop ? strtolower($filterCrop) : 'all crops' }}
                    </span>
                </div>
            @endif
        </div>

        <!-- Key Metrics -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Number of Farmers -->
            <div class="bg-white rounded-xl shadow-md p-6">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-sm font-medium text-gray-600">Number of Farmers</h3>
                    <div class="w-10 h-10 bg-yellow-100 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/>
                        </svg>
                    </div>
                </div>
                <div class="text-3xl font-bold text-gray-800">{{ $totalFarmers ?? 0 }}</div>
                <div class="flex items-center gap-1 mt-2">
                    <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                    </svg>
                    <span class="text-xs text-gray-400">Updated {{ $lastUpdate->format('F Y') ?? 'July 2025' }}</span>
                </div>
            </div>

            <!-- Total Area Planted/Harvested -->
            <div class="bg-white rounded-xl shadow-md p-6">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-sm font-medium text-gray-600">Total Area planted/Harvested</h3>
                    <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h8a2 2 0 012 2v12a1 1 0 110 2h-3a1 1 0 01-1-1v-2a1 1 0 00-1-1H9a1 1 0 00-1 1v2a1 1 0 01-1 1H4a1 1 0 110-2V4zm3 1h2v2H7V5zm2 4H7v2h2V9zm2-4h2v2h-2V5zm2 4h-2v2h2V9z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                </div>
                <div class="text-3xl font-bold text-gray-800">{{ number_format($totalAreaHarvested ?? 0, 0) }} ha</div>
                <div class="flex items-center gap-1 mt-2">
                    <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                    </svg>
                    <span class="text-xs text-gray-400">Updated {{ $lastUpdate->format('F Y') ?? 'July 2025' }}</span>
                </div>
            </div>

            <!-- Average Yield -->
            <div class="bg-white rounded-xl shadow-md p-6">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-sm font-medium text-gray-600">Average Yield</h3>
                    <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z"/>
                        </svg>
                    </div>
                </div>
                <div class="text-3xl font-bold text-gray-800">{{ number_format($averageYield ?? 0, 1) }}</div>
                <div class="flex items-center gap-1 mt-2">
                    <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                    </svg>
                    <span class="text-xs text-gray-400">Updated {{ $lastUpdate->format('F Y') ?? 'July 2025' }}</span>
                </div>
                <div class="mt-4 flex gap-2">
                    <a href="{{ route('admin.crop-data.index') }}" class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm text-gray-700 hover:bg-gray-50 transition-colors text-center">
                        View All Data
                    </a>
                    <a href="{{ route('admin.export-summary') }}" class="px-4 py-2 bg-yellow-400 hover:bg-yellow-500 text-gray-800 font-medium rounded-lg text-sm transition-colors whitespace-nowrap">
                        Export Summary
                    </a>
                </div>
            </div>
        </div>

        <!-- Summary Cards and Demand Chart -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Summary Cards -->
            <div class="bg-white rounded-xl shadow-md p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-gray-800">Summary Cards</h2>
                    <button class="text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </button>
                </div>

                <div class="space-y-4">
                    <!-- Top 3 Crops -->
                    <div class="flex items-start gap-3">
                        <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center flex-shrink-0">
                            <svg class="w-6 h-6 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-semibold text-gray-800 mb-1">Top 3 Crops</h3>
                            <ul class="text-sm text-gray-600 space-y-1">
                                @if(isset($topCrops) && $topCrops->count() > 0)
                                    @foreach($topCrops as $index => $crop)
                                        <li class="flex items-center gap-2">
                                            <span class="font-medium text-green-600">{{ $index + 1 }}.</span>
                                            <span>{{ ucwords(strtolower($crop->crop)) }} - <span class="font-medium">{{ number_format($crop->total_production, 2) }} kg</span> produced</span>
                                        </li>
                                    @endforeach
                                @else
                                    <li class="text-gray-400 italic">No crop data available for selected filters</li>
                                @endif
                            </ul>
                            <div class="flex items-center gap-1 mt-2">
                                <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                                </svg>
                                <span class="text-xs text-gray-400">Updated {{ $lastUpdate->format('M d, Y') }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="border-t border-gray-200"></div>

                    <!-- Most Productive Municipality -->
                    <div class="flex items-start gap-3">
                        <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center flex-shrink-0">
                            <svg class="w-6 h-6 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-semibold text-gray-800 mb-1">Most Productive Municipality</h3>
                            <p class="text-sm text-gray-600">
                                @if(isset($topMunicipality))
                                    <span class="font-semibold text-green-700">{{ ucwords(strtolower($topMunicipality->municipality)) }}</span>
                                    <span class="text-gray-500"> - {{ number_format($topMunicipality->total_production, 2) }} kg total</span>
                                @else
                                    <span class="text-gray-400 italic">No data available for selected filters</span>
                                @endif
                            </p>
                            <div class="flex items-center gap-1 mt-2">
                                <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                                </svg>
                                <span class="text-xs text-gray-400">Updated {{ $lastUpdate->format('M d, Y') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Demand Chart -->
            <div class="bg-white rounded-xl shadow-md p-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-800">Monthly Production</h2>
                        <p class="text-sm text-gray-500">{{ $selectedYear ?? date('Y') }} Production Data</p>
                    </div>
                    <button class="text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </button>
                </div>

                <!-- Bar Chart -->
                @if(array_sum($monthlyDemand) > 0)
                    <div class="h-64">
                        <canvas id="demandChart"></canvas>
                    </div>

                    <div class="mt-4">
                        @php
                            $monthValues = array_values($monthlyDemand);
                            $nonZeroMonths = array_filter($monthValues);
                            $avgProduction = count($nonZeroMonths) > 0 ? array_sum($nonZeroMonths) / count($nonZeroMonths) : 0;
                        @endphp
                        <p class="text-sm text-gray-600">
                            Total production: {{ number_format(array_sum($monthlyDemand), 2) }} kg
                        </p>
                        <p class="text-xs text-gray-400 mt-1">Showing monthly production for {{ $selectedYear ?? date('Y') }}</p>
                    </div>
                @else
                    <div class="h-64 flex items-center justify-center bg-gray-50 rounded-lg">
                        <div class="text-center">
                            <svg class="w-16 h-16 mx-auto text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                            <p class="text-gray-500 font-medium">No monthly data for {{ $selectedYear ?? date('Y') }}</p>
                            <p class="text-sm text-gray-400 mt-1">Upload crop data or try a different filter</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- ML Predictions Section -->
        @if(isset($predictions) && $predictions['available'])
            <div id="predictions-section" class="bg-gradient-to-br from-green-50 to-blue-50 rounded-xl shadow-md p-6 border border-green-200">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 bg-green-600 rounded-lg flex items-center justify-center">
                            <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-gray-800">AI-Powered Production Predictions</h2>
                            <p class="text-sm text-gray-600">Machine Learning predictions based on historical data</p>
                        </div>
                    </div>
                    <span class="px-3 py-1 bg-green-600 text-white text-xs font-semibold rounded-full">
                        {{ $predictions['count'] }} Predictions
                    </span>
                </div>

                @if(!empty($predictions['predictions']))
                    @php
                        // Group predictions by year and municipality
                        $groupedPredictions = collect($predictions['predictions'])
                            ->groupBy(function($pred) {
                                return $pred['year'] ?? date('Y');
                            });
                    @endphp

                    @foreach($groupedPredictions as $year => $yearPredictions)
                        <div class="mb-6">
                            <!-- Year Header -->
                            <div class="bg-gray-800 text-white px-4 py-2 rounded-t-lg">
                                <h3 class="font-bold text-lg">{{ $year }}</h3>
                            </div>

                            <!-- Municipality Predictions -->
                            <div class="bg-white border border-gray-200 rounded-b-lg p-4">
                                @php
                                    $municipalityTotals = collect($yearPredictions)->groupBy('municipality');
                                @endphp

                                <div class="space-y-2">
                                    @foreach($municipalityTotals as $municipality => $predictions)
                                        @php
                                            $totalProduction = collect($predictions)->sum('predicted_production');
                                            $totalProductionMt = $totalProduction / 1000;
                                            
                                            // Assign colors to municipalities
                                            $colors = [
                                                'bg-blue-500', 'bg-green-500', 'bg-yellow-500', 
                                                'bg-purple-500', 'bg-pink-500', 'bg-orange-500',
                                                'bg-red-500', 'bg-indigo-500', 'bg-teal-500', 
                                                'bg-cyan-500', 'bg-amber-500', 'bg-lime-500'
                                            ];
                                            $colorIndex = crc32($municipality) % count($colors);
                                            $color = $colors[$colorIndex];
                                        @endphp
                                        
                                        <div class="flex items-center gap-3 py-2 hover:bg-gray-50 rounded px-2">
                                            <div class="w-4 h-4 {{ $color }} rounded-sm flex-shrink-0"></div>
                                            <span class="font-medium text-gray-800 flex-1">{{ $municipality }}:</span>
                                            <span class="font-bold text-gray-900">{{ number_format($totalProductionMt, 2) }} mt</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endforeach

                    <div class="mt-4 p-4 bg-blue-50 border border-blue-200 rounded-lg flex items-start gap-3">
                        <svg class="w-5 h-5 text-blue-600 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                        <div class="text-sm">
                            <p class="font-semibold text-blue-900 mb-1">About these predictions</p>
                            <p class="text-blue-800">These predictions are generated by our machine learning model trained on historical crop production data. Predictions are based on municipality, crop type, season, and historical farming patterns.</p>
                        </div>
                    </div>
                @else
                    <div class="text-center py-8">
                        <p class="text-gray-600">No predictions available. Try adjusting your filters.</p>
                    </div>
                @endif
            </div>
        @elseif(isset($predictions) && !$predictions['available'])
            <div class="bg-yellow-50 rounded-xl shadow-sm p-6 border border-yellow-200">
                <div class="flex items-start gap-3">
                    <svg class="w-6 h-6 text-yellow-600 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                    <div>
                        <h3 class="font-semibold text-yellow-900 mb-1">Predictions Unavailable</h3>
                        <p class="text-sm text-yellow-800">{{ $predictions['message'] ?? 'Unable to generate predictions at this time.' }}</p>
                    </div>
                </div>
            </div>
        @endif
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        function dataAnalytics() {
            return {
                init() {
                    this.initTrendChart();
                    this.initDemandChart();
                },

                initTrendChart() {
                    const ctx = document.getElementById('trendChart');
                    if (!ctx) return;

                    // Get actual data from database
                    const years = @json($years);
                    const municipalities = @json($municipalities);
                    const trendData = @json($trendChartData);
                    
                    // Color palette for municipalities
                    const colors = [
                        'rgb(59, 130, 246)',   // Blue
                        'rgb(34, 197, 94)',    // Green
                        'rgb(234, 179, 8)',    // Yellow
                        'rgb(168, 85, 247)',   // Purple
                        'rgb(236, 72, 153)',   // Pink
                        'rgb(249, 115, 22)',   // Orange
                        'rgb(14, 165, 233)',   // Sky
                        'rgb(139, 92, 246)',   // Violet
                        'rgb(244, 63, 94)',    // Rose
                        'rgb(251, 146, 60)',   // Amber
                    ];
                    
                    // Build datasets from actual database data
                    const datasets = municipalities.map((municipality, index) => {
                        const color = colors[index % colors.length];
                        return {
                            label: municipality,
                            data: trendData[municipality] || [],
                            borderColor: color,
                            backgroundColor: color.replace('rgb', 'rgba').replace(')', ', 0.1)'),
                            tension: 0.4
                        };
                    });
                    
                    new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: years,
                            datasets: datasets
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: false
                                },
                                tooltip: {
                                    mode: 'index',
                                    intersect: false,
                                    callbacks: {
                                        label: function(context) {
                                            let label = context.dataset.label || '';
                                            if (label) {
                                                label += ': ';
                                            }
                                            label += context.parsed.y.toLocaleString() + ' mt';
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
                                        text: 'Production (mt)',
                                        font: {
                                            size: 12,
                                            weight: 'bold'
                                        }
                                    },
                                    ticks: {
                                        callback: function(value) {
                                            return value.toLocaleString() + ' mt';
                                        }
                                    },
                                    grid: {
                                        color: 'rgba(0, 0, 0, 0.05)'
                                    }
                                },
                                x: {
                                    grid: {
                                        display: false
                                    }
                                }
                            },
                            interaction: {
                                mode: 'nearest',
                                axis: 'x',
                                intersect: false
                            }
                        }
                    });
                },

                initDemandChart() {
                    const ctx = document.getElementById('demandChart');
                    if (!ctx) return;

                    // Get actual monthly production data from database
                    const monthlyData = @json($monthlyDemand);
                    const monthLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                    const monthKeys = ['JAN', 'FEB', 'MAR', 'APR', 'MAY', 'JUN', 'JUL', 'AUG', 'SEP', 'OCT', 'NOV', 'DEC'];
                    
                    // Extract values from database data
                    const demandValues = monthKeys.map(key => monthlyData[key] || 0);
                    
                    // Color bars based on value (high = green, medium = yellow, low = gray)
                    const maxValue = Math.max(...demandValues);
                    const colors = demandValues.map(value => {
                        if (value === 0) return 'rgba(156, 163, 175, 0.3)';
                        const ratio = value / maxValue;
                        if (ratio > 0.7) return 'rgb(34, 197, 94)';
                        if (ratio > 0.4) return 'rgba(234, 179, 8, 0.7)';
                        return 'rgba(156, 163, 175, 0.5)';
                    });

                    new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: monthLabels,
                            datasets: [
                                {
                                    label: 'Production (mt)',
                                    data: demandValues,
                                    backgroundColor: colors,
                                    borderRadius: 4
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
                                    callbacks: {
                                        label: function(context) {
                                            return 'Production: ' + context.parsed.y.toLocaleString() + ' mt';
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
                                            size: 12,
                                            weight: 'bold'
                                        }
                                    },
                                    ticks: {
                                        callback: function(value) {
                                            return value.toLocaleString() + ' mt';
                                        }
                                    },
                                    grid: {
                                        color: 'rgba(0, 0, 0, 0.05)'
                                    }
                                },
                                x: {
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
