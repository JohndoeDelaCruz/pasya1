<x-admin-layout>
    <x-slot name="title">Data & Analytics</x-slot>

    <div class="space-y-6" x-data="{ 
        showMunicipalityModal: false,
        showResourceModal: false,
        selectedMunicipality: '{{ $filterMunicipality ?? '' }}',
        openMunicipalityModal(municipality) {
            this.selectedMunicipality = municipality;
            this.showMunicipalityModal = true;
            console.log('Opening modal for:', municipality);
        },
        closeMunicipalityModal() {
            this.showMunicipalityModal = false;
        },
        toggleMunicipalityModal() {
            if (this.selectedMunicipality) {
                this.showMunicipalityModal = !this.showMunicipalityModal;
                console.log('Toggling modal. Selected:', this.selectedMunicipality, 'Show:', this.showMunicipalityModal);
            }
        }
    }" x-init="setTimeout(() => { 
        if (typeof dataAnalytics === 'function') {
            const analytics = dataAnalytics();
            if (analytics && typeof analytics.init === 'function') {
                analytics.init();
            }
        }
    }, 100)">
        <!-- Page Header with Filters -->
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 animate-fade-in-down">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Dashboard</h1>
            </div>
            
            <!-- Filter Controls and Action Buttons -->
            <div class="flex flex-wrap items-center gap-3">
                <!-- Allocate Resource Button -->
                <button type="button" 
                        @click="showResourceModal = true"
                        class="px-4 py-2 bg-yellow-400 hover:bg-yellow-500 text-gray-800 font-semibold rounded-lg transition-colors shadow-sm hover-lift animate-fade-in animate-delay-100">
                    Allocate Resource
                </button>
                
                <!-- Recommend Button -->
                <button onclick="document.getElementById('predictions-section')?.scrollIntoView({ behavior: 'smooth' })" class="px-4 py-2 bg-yellow-500 hover:bg-yellow-600 text-white font-semibold rounded-lg transition-colors shadow-sm hover-lift animate-fade-in animate-delay-200">
                    Recommend
                </button>
            </div>
        </div>

        <!-- Filter Bar -->
        <form method="GET" action="{{ route('admin.dashboard') }}" id="filterForm" class="bg-white rounded-lg shadow-sm p-4 flex items-center justify-between animate-fade-in-up animate-delay-200">
            <div class="flex items-center gap-3">
                <label class="text-sm text-gray-700 font-medium whitespace-nowrap">Filter by:</label>
            </div>
            
            <div class="flex items-center gap-3 flex-wrap">
                <!-- Crop Filter -->
                <div class="relative">
                    <select name="crop" 
                            id="cropFilter"
                            onchange="handleCropFilterChange()" 
                            class="appearance-none px-4 py-2.5 pr-10 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-500 focus:border-transparent bg-white text-gray-700 cursor-pointer min-w-[150px]">
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
                    <select name="municipality" 
                            id="municipalityFilter"
                            onchange="handleMunicipalityFilterChange()"
                            class="appearance-none px-4 py-2.5 pr-10 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-500 focus:border-transparent bg-white text-gray-700 cursor-pointer min-w-[150px]">
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

                <!-- Toggle Municipality Details Button -->
                @if($filterMunicipality)
                    <button type="button" 
                            @click="toggleMunicipalityModal()"
                            class="px-4 py-2.5 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition-colors shadow-sm flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                        <span x-text="showMunicipalityModal ? 'Hide Details' : 'View Details'"></span>
                    </button>
                @endif
                
                <!-- Month Filter -->
                <div class="relative">
                    <select name="month" 
                            id="monthFilter"
                            onchange="document.getElementById('filterForm').submit()" 
                            class="appearance-none px-4 py-2.5 pr-10 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-500 focus:border-transparent bg-white text-gray-700 cursor-pointer min-w-[150px]">
                        <option value="">Month</option>
                        <option value="JAN" {{ $filterMonth == 'JAN' ? 'selected' : '' }}>January</option>
                        <option value="FEB" {{ $filterMonth == 'FEB' ? 'selected' : '' }}>February</option>
                        <option value="MAR" {{ $filterMonth == 'MAR' ? 'selected' : '' }}>March</option>
                        <option value="APR" {{ $filterMonth == 'APR' ? 'selected' : '' }}>April</option>
                        <option value="MAY" {{ $filterMonth == 'MAY' ? 'selected' : '' }}>May</option>
                        <option value="JUN" {{ $filterMonth == 'JUN' ? 'selected' : '' }}>June</option>
                        <option value="JUL" {{ $filterMonth == 'JUL' ? 'selected' : '' }}>July</option>
                        <option value="AUG" {{ $filterMonth == 'AUG' ? 'selected' : '' }}>August</option>
                        <option value="SEP" {{ $filterMonth == 'SEP' ? 'selected' : '' }}>September</option>
                        <option value="OCT" {{ $filterMonth == 'OCT' ? 'selected' : '' }}>October</option>
                        <option value="NOV" {{ $filterMonth == 'NOV' ? 'selected' : '' }}>November</option>
                        <option value="DEC" {{ $filterMonth == 'DEC' ? 'selected' : '' }}>December</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-500">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                </div>
                
                <!-- Year Filter -->
                <div class="relative">
                    <select name="year" 
                            id="yearFilter"
                            onchange="handleYearFilterChange()" 
                            class="appearance-none px-4 py-2.5 pr-10 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-500 focus:border-transparent bg-white text-gray-700 cursor-pointer min-w-[150px]">
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
                
                <!-- Farm Type Filter -->
                <div class="relative">
                    <select name="farm_type" onchange="document.getElementById('filterForm').submit()" class="appearance-none px-4 py-2.5 pr-10 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-500 focus:border-transparent bg-white text-gray-700 cursor-pointer min-w-[150px]">
                        <option value="">Farm Type</option>
                        <option value="Rainfed" {{ $filterFarmType == 'Rainfed' ? 'selected' : '' }}>Rainfed</option>
                        <option value="Irrigated" {{ $filterFarmType == 'Irrigated' ? 'selected' : '' }}>Irrigated</option>
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
        <div class="bg-white rounded-xl shadow-md p-6 card-animate hover-lift animate-scale-in animate-delay-300">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-gray-800">
                    @php
                        $monthNames = ['JAN' => 'January', 'FEB' => 'February', 'MAR' => 'March', 'APR' => 'April', 
                                      'MAY' => 'May', 'JUN' => 'June', 'JUL' => 'July', 'AUG' => 'August',
                                      'SEP' => 'September', 'OCT' => 'October', 'NOV' => 'November', 'DEC' => 'December'];
                    @endphp
                    @if($chartMode === 'crop_breakdown')
                        {{ ucwords(strtolower($filterMunicipality)) }} - Crop Breakdown
                        @if($filterMonth)
                            ({{ $monthNames[$filterMonth] ?? $filterMonth }} {{ $filterYear }})
                        @endif
                    @elseif($chartMode === 'crops')
                        Crop Production Breakdown
                        @if($filterMonth && $filterYear)
                            ({{ $monthNames[$filterMonth] ?? $filterMonth }} {{ $filterYear }})
                        @elseif($filterMonth)
                            ({{ $monthNames[$filterMonth] ?? $filterMonth }})
                        @elseif($filterYear)
                            ({{ $filterYear }})
                        @endif
                    @elseif($chartMode === 'municipalities')
                        Municipality Production Comparison by Crop
                        @if($filterMonth && $filterYear)
                            ({{ $monthNames[$filterMonth] ?? $filterMonth }} {{ $filterYear }})
                        @elseif($filterMonth)
                            ({{ $monthNames[$filterMonth] ?? $filterMonth }})
                        @elseif($filterYear)
                            ({{ $filterYear }})
                        @endif
                    @elseif($chartMode === 'monthly_crop')
                        {{ ucwords(strtolower($filterCrop)) }} - Monthly Production in {{ ucwords(strtolower($filterMunicipality)) }} ({{ $filterYear }})
                    @elseif($chartMode === 'monthly_year')
                        Monthly Production for {{ $filterYear }} (All Municipalities & Crops)
                    @elseif($chartMode === 'monthly')
                        @if($filterMunicipality)
                            {{ ucwords(strtolower($filterMunicipality)) }} - Monthly Production ({{ $filterYear }})
                        @else
                            Monthly Production Trend ({{ $filterYear }})
                        @endif
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
                    @if($chartMode === 'crop_breakdown')
                        Top 10 crops by production volume for {{ ucwords(strtolower($filterMunicipality)) }} in {{ $monthNames[$filterMonth] ?? $filterMonth }} {{ $filterYear }}
                    @elseif($chartMode === 'crops')
                        Top 10 crops by production volume
                        @if($filterMonth && $filterYear)
                            for {{ $monthNames[$filterMonth] ?? $filterMonth }} {{ $filterYear }}
                        @elseif($filterMonth)
                            for {{ $monthNames[$filterMonth] ?? $filterMonth }}
                        @elseif($filterYear)
                            for {{ $filterYear }}
                        @endif
                    @elseif($chartMode === 'municipalities')
                        Multiple municipality lines showing production across top 10 crops
                        @if($filterMonth && $filterYear)
                            for {{ $monthNames[$filterMonth] ?? $filterMonth }} {{ $filterYear }}
                        @elseif($filterMonth)
                            for {{ $monthNames[$filterMonth] ?? $filterMonth }}
                        @elseif($filterYear)
                            for {{ $filterYear }}
                        @endif
                    @elseif($chartMode === 'monthly_crop')
                        Monthly production breakdown for {{ ucwords(strtolower($filterCrop)) }} in {{ ucwords(strtolower($filterMunicipality)) }} ({{ $filterYear }})
                    @elseif($chartMode === 'monthly_year')
                        Monthly production breakdown for all municipalities and crops in {{ $filterYear }}
                    @elseif($chartMode === 'monthly')
                        @if($filterMunicipality)
                            Monthly production breakdown for {{ ucwords(strtolower($filterMunicipality)) }} in {{ $filterYear }}
                        @else
                            Monthly production breakdown for {{ $filterYear }}
                        @endif
                    @else
                        Each municipality's seasonal productivity per year
                    @endif
                </p>
                <div class="flex items-center gap-2 mt-2">
                    @if($productionTrend != 0)
                        <span class="text-sm text-gray-600">
                            @if($chartMode === 'crop_breakdown')
                                Top crop {{ $productionTrend > 0 ? 'leads by' : 'trails by' }} {{ number_format(abs($productionTrend), 1) }}%
                            @elseif($chartMode === 'crops')
                                Leading crop {{ $productionTrend > 0 ? 'up' : 'down' }} {{ number_format(abs($productionTrend), 1) }}% vs second
                            @elseif($chartMode === 'monthly_year' || $chartMode === 'monthly' || $chartMode === 'monthly_crop')
                                Production {{ $productionTrend > 0 ? 'up' : 'down' }} {{ number_format(abs($productionTrend), 1) }}% month-over-month
                            @else
                                Production {{ $productionTrend > 0 ? 'up' : 'down' }} {{ number_format(abs($productionTrend), 1) }}% 
                                @if($filterMunicipality)
                                    for {{ ucwords(strtolower($filterMunicipality)) }}
                                @elseif($filterCrop)
                                    for {{ ucwords(strtolower($filterCrop)) }}
                                @else
                                    year-over-year
                                @endif
                            @endif
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
                    <span class="text-xs text-gray-400">
                        @if($chartMode === 'crop_breakdown' || $chartMode === 'crops')
                            Comparing crop performance
                        @elseif($chartMode === 'municipalities')
                            Comparing municipality performance
                        @elseif($chartMode === 'monthly_year' || $chartMode === 'monthly' || $chartMode === 'monthly_crop')
                            Month-to-month comparison
                        @else
                            Year-over-year comparison
                        @endif
                    </span>
                </div>
                
                <!-- Zoom Controls -->
                <div class="flex items-center gap-2">
                    <button type="button" onclick="resetZoom()" class="px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg transition-colors flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        Reset Zoom
                    </button>
                    <span class="text-xs text-gray-500">💡 Scroll to zoom • Drag to pan</span>
                </div>
            </div>
            
            <!-- Chart Container -->
            @php
                // Debug: Check what data we have
                \Log::info('Chart Display Check:', [
                    'years_count' => count($years ?? []),
                    'municipalities_count' => count($municipalities ?? []),
                    'years' => $years ?? [],
                    'municipalities' => $municipalities ?? [],
                    'trendChartData_labels' => $trendChartData['labels'] ?? [],
                    'trendChartData_datasets_count' => count($trendChartData['datasets'] ?? [])
                ]);
                
                // Check if we have any chart data at all
                $hasChartData = isset($trendChartData) && 
                               isset($trendChartData['labels']) && 
                               isset($trendChartData['datasets']) &&
                               count($trendChartData['labels']) > 0 && 
                               count($trendChartData['datasets']) > 0;
            @endphp
            
            @if($hasChartData)
                <div class="h-[600px] relative">
                    <canvas id="trendChart"></canvas>
                </div>
            @else
                <div class="h-[600px] flex items-center justify-center bg-gray-50 rounded-lg border-2 border-dashed border-gray-200">
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

            <!-- Production Trend Indicator -->
            @if($hasChartData && $productionTrend !== null && $productionTrend != 0)
                <div class="mt-4 flex items-center justify-center">
                    <div class="flex items-center gap-2 text-sm">
                        @if($productionTrend > 0)
                            <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M12 7a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0V8.414l-4.293 4.293a1 1 0 01-1.414 0L8 10.414l-4.293 4.293a1 1 0 01-1.414-1.414l5-5a1 1 0 011.414 0L11 10.586 14.586 7H12z" clip-rule="evenodd"/>
                            </svg>
                            <span class="font-semibold text-gray-800">
                                @if($chartMode === 'crop_breakdown' || $chartMode === 'crops')
                                    Top crop leads by {{ number_format(abs($productionTrend), 1) }}%
                                @elseif($chartMode === 'municipalities')
                                    Top municipality leads by {{ number_format(abs($productionTrend), 1) }}%
                                @elseif($chartMode === 'monthly_year' || $chartMode === 'monthly')
                                    Production up by {{ number_format(abs($productionTrend), 1) }}% month-over-month
                                @else
                                    Production up by {{ number_format(abs($productionTrend), 1) }}% year-over-year
                                @endif
                            </span>
                        @elseif($productionTrend < 0)
                            <svg class="w-5 h-5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M12 13a1 1 0 100 2h5a1 1 0 001-1V9a1 1 0 10-2 0v2.586l-4.293-4.293a1 1 0 00-1.414 0L8 9.586 3.707 5.293a1 1 0 00-1.414 1.414l5 5a1 1 0 001.414 0L11 9.414 14.586 13H12z" clip-rule="evenodd"/>
                            </svg>
                            <span class="font-semibold text-gray-800">
                                @if($chartMode === 'crop_breakdown' || $chartMode === 'crops')
                                    Top crop leads by {{ number_format(abs($productionTrend), 1) }}%
                                @elseif($chartMode === 'municipalities')
                                    Top municipality leads by {{ number_format(abs($productionTrend), 1) }}%
                                @elseif($chartMode === 'monthly_year' || $chartMode === 'monthly' || $chartMode === 'monthly_crop')
                                    Production up by {{ number_format(abs($productionTrend), 1) }}% month-over-month
                                @else
                                    Production up by {{ number_format(abs($productionTrend), 1) }}% year-over-year
                                @endif
                            </span>
                        @elseif($productionTrend < 0)
                            <svg class="w-5 h-5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M12 13a1 1 0 100 2h5a1 1 0 001-1V9a1 1 0 10-2 0v2.586l-4.293-4.293a1 1 0 00-1.414 0L8 9.586 3.707 5.293a1 1 0 00-1.414 1.414l5 5a1 1 0 001.414 0L11 9.414 14.586 13H12z" clip-rule="evenodd"/>
                            </svg>
                            <span class="font-semibold text-gray-800">
                                @if($chartMode === 'monthly_year' || $chartMode === 'monthly' || $chartMode === 'monthly_crop')
                                    Production down by {{ number_format(abs($productionTrend), 1) }}% month-over-month
                                @else
                                    Production down by {{ number_format(abs($productionTrend), 1) }}% year-over-year
                                @endif
                            </span>
                        @endif
                    </div>
                </div>
                <div class="text-center mt-1">
                    <span class="text-xs text-gray-500">
                        @if($chartMode === 'crop_breakdown')
                            Comparing top performing crops
                        @elseif($chartMode === 'crops')
                            Comparing crop production volumes
                        @elseif($chartMode === 'monthly_year' || $chartMode === 'monthly' || $chartMode === 'monthly_crop')
                            Comparing consecutive months
                        @else
                            Comparing recent years
                        @endif
                    </span>
                </div>
            @endif
        </div>

        <!-- Key Metrics -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Total Area Planted/Harvested -->
            <div class="bg-white rounded-xl shadow-md p-6 card-animate hover-lift animate-fade-in-up animate-delay-300">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-sm font-medium text-gray-600">Total Area planted/Harvested</h3>
                    <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center animate-bounce">
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
            <div class="bg-white rounded-xl shadow-md p-6 card-animate hover-lift animate-fade-in-up animate-delay-400">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-sm font-medium text-gray-600">Average Yield</h3>
                    <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center animate-bounce">
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
                    <a href="{{ route('admin.crop-data.index') }}" class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm text-gray-700 hover:bg-gray-50 transition-colors text-center hover-scale">
                        View All Data
                    </a>
                    <a href="{{ route('admin.export-summary') }}" class="px-4 py-2 bg-yellow-400 hover:bg-yellow-500 text-gray-800 font-medium rounded-lg text-sm transition-colors whitespace-nowrap hover-scale">
                        Export Summary
                    </a>
                </div>
            </div>
        </div>

        <!-- Summary Cards and Demand Chart -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Summary Cards -->
            <div class="bg-white rounded-xl shadow-md p-6 card-animate hover-lift animate-fade-in-left animate-delay-500">
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
                    <div class="flex items-start gap-3 hover-lift p-3 rounded-lg transition-all">
                        <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center flex-shrink-0 animate-pulse-slow">
                            <svg class="w-6 h-6 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-semibold text-gray-800 mb-1">Top 3 Crops</h3>
                            <ul class="text-sm text-gray-600 space-y-1">
                                @if(isset($topCrops) && $topCrops->count() > 0)
                                    @foreach($topCrops->take(3) as $index => $crop)
                                        <li class="flex items-center gap-2 hover-scale transition-transform">
                                            <span class="font-medium text-green-600">{{ $index + 1 }}.</span>
                                            <span>{{ ucwords(strtolower($crop->crop)) }} - <span class="font-medium">{{ number_format($crop->total_production, 2) }} mt</span></span>
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
                    <div class="flex items-start gap-3 hover-lift p-3 rounded-lg transition-all">
                        <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center flex-shrink-0 animate-pulse-slow">
                            <svg class="w-6 h-6 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-semibold text-gray-800 mb-1">Most Productive Municipality</h3>
                            <p class="text-sm text-gray-600">
                                @if(isset($topMunicipality))
                                    <span class="font-semibold text-green-700">{{ ucwords(strtolower($topMunicipality->municipality)) }}</span>
                                    <span class="text-gray-500"> - {{ number_format($topMunicipality->total_production, 2) }} MT total</span>
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

        <!-- Announcements Management Widget -->
        <div class="bg-white rounded-xl shadow-md overflow-hidden card-animate hover-lift animate-fade-in-up animate-delay-550">
            <div class="bg-gradient-to-r from-amber-500 to-orange-500 px-6 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 3a1 1 0 00-1.447-.894L8.763 6H5a3 3 0 000 6h.28l1.771 5.316A1 1 0 008 18h1a1 1 0 001-1v-4.382l6.553 3.276A1 1 0 0018 15V3z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="text-white">
                            <h2 class="text-lg font-bold">Announcements</h2>
                            <p class="text-amber-100 text-sm">Manage farmer notifications</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="px-3 py-1 bg-white/20 text-white text-xs font-semibold rounded-full">
                            {{ $activeAnnouncementsCount ?? 0 }} Active
                        </span>
                        <a href="{{ route('admin.announcements.create') }}" class="px-4 py-2 bg-white text-orange-600 font-semibold rounded-lg hover:bg-orange-50 transition-colors text-sm">
                            + New
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="p-6">
                @if(isset($recentAnnouncements) && $recentAnnouncements->count() > 0)
                    <div class="space-y-3">
                        @foreach($recentAnnouncements->take(4) as $announcement)
                            <div class="flex items-start gap-3 p-3 rounded-lg border-l-4 transition-all hover:shadow-sm
                                {{ $announcement->priority === 'urgent' ? 'border-red-500 bg-red-50' : 
                                   ($announcement->priority === 'high' ? 'border-orange-500 bg-orange-50' : 
                                   ($announcement->priority === 'normal' ? 'border-blue-500 bg-blue-50' : 'border-gray-300 bg-gray-50')) }}">
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 mb-1">
                                        <h4 class="text-sm font-semibold text-gray-800 truncate">{{ $announcement->title }}</h4>
                                        @if($announcement->priority === 'urgent')
                                            <span class="px-2 py-0.5 text-xs font-bold bg-red-100 text-red-700 rounded-full">Urgent</span>
                                        @elseif($announcement->priority === 'high')
                                            <span class="px-2 py-0.5 text-xs font-bold bg-orange-100 text-orange-700 rounded-full">High</span>
                                        @endif
                                        @if(!$announcement->is_active)
                                            <span class="px-2 py-0.5 text-xs font-medium bg-gray-100 text-gray-600 rounded-full">Inactive</span>
                                        @endif
                                    </div>
                                    <p class="text-xs text-gray-600 line-clamp-1">{{ $announcement->content }}</p>
                                    <div class="flex items-center gap-3 mt-2 text-xs text-gray-400">
                                        <span>{{ $announcement->created_at->diffForHumans() }}</span>
                                        <span>•</span>
                                        <span>{{ ucfirst($announcement->target_audience) }}</span>
                                        @if($announcement->municipality)
                                            <span>•</span>
                                            <span>{{ ucwords(strtolower($announcement->municipality)) }}</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="flex items-center gap-2 flex-shrink-0">
                                    <a href="{{ route('admin.announcements.edit', $announcement) }}" class="p-1.5 text-gray-400 hover:text-blue-600 rounded transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    
                    <div class="mt-4 pt-4 border-t border-gray-100 flex items-center justify-between">
                        <span class="text-sm text-gray-500">{{ $totalAnnouncementsCount ?? 0 }} total announcements</span>
                        <a href="{{ route('admin.announcements.index') }}" class="text-sm font-medium text-green-600 hover:text-green-700 flex items-center gap-1">
                            View All
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    </div>
                @else
                    <div class="text-center py-8">
                        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
                            </svg>
                        </div>
                        <p class="text-gray-600 font-medium mb-1">No announcements yet</p>
                        <p class="text-gray-400 text-sm mb-4">Create your first announcement to notify farmers</p>
                        <a href="{{ route('admin.announcements.create') }}" class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Create Announcement
                        </a>
                    </div>
                @endif
            </div>
        </div>

        <!-- ML Predictions Section -->
        @if(isset($predictions) && $predictions['available'])
            <div id="predictions-section" class="bg-gradient-to-br from-green-50 to-blue-50 rounded-xl shadow-md p-6 border border-green-200 card-animate hover-lift animate-fade-in-up animate-delay-600">
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
                    <div class="flex items-center gap-3">
                        <span class="px-3 py-1 bg-green-600 text-white text-xs font-semibold rounded-full">
                            {{ $predictions['count'] }} Predictions
                        </span>
                    </div>
                </div>

                @if(!empty($predictions['predictions']))
                    @php
                        // Group predictions by year, then municipality, then crop
                        $groupedPredictions = collect($predictions['predictions'])
                            ->groupBy('year')
                            ->map(function($yearPredictions) {
                                return $yearPredictions->groupBy('municipality');
                            });
                    @endphp

                    @foreach($groupedPredictions as $year => $municipalityGroups)
                        <div class="mb-6">
                            <!-- Year Header -->
                            <div class="bg-gradient-to-r from-green-700 to-blue-700 text-white px-4 py-3 rounded-t-lg shadow-md">
                                <div class="flex items-center justify-between">
                                    <h3 class="font-bold text-lg">Predictions for {{ $year }}</h3>
                                    <span class="text-sm bg-white/20 px-3 py-1 rounded-full">
                                        {{ collect($municipalityGroups)->flatten(1)->count() }} predictions
                                    </span>
                                </div>
                            </div>

                            <!-- Municipality Predictions -->
                            <div class="bg-white border border-gray-200 rounded-b-lg p-4 shadow-sm">
                                @foreach($municipalityGroups as $municipality => $municipalityPredictions)
                                    <div class="mb-6 last:mb-0">
                                        @php
                                            // Group by crop within municipality
                                            $cropGroups = collect($municipalityPredictions)->groupBy('crop_type');
                                            $totalProduction = collect($municipalityPredictions)->sum('predicted_production');
                                            
                                            $colors = [
                                                'from-blue-500 to-blue-600', 'from-green-500 to-green-600', 
                                                'from-yellow-500 to-yellow-600', 'from-purple-500 to-purple-600',
                                                'from-pink-500 to-pink-600', 'from-orange-500 to-orange-600',
                                                'from-red-500 to-red-600', 'from-indigo-500 to-indigo-600',
                                                'from-teal-500 to-teal-600', 'from-cyan-500 to-cyan-600'
                                            ];
                                            $colorIndex = crc32($municipality) % count($colors);
                                            $gradient = $colors[$colorIndex];
                                        @endphp
                                        
                                        <!-- Municipality Header -->
                                        <div class="bg-gradient-to-r {{ $gradient }} text-white px-4 py-2 rounded-lg mb-3 shadow">
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center gap-2">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                                    </svg>
                                                    <span class="font-bold">{{ ucwords(strtolower($municipality)) }}</span>
                                                </div>
                                                <span class="font-bold text-lg">{{ number_format($totalProduction, 2) }} mt</span>
                                            </div>
                                        </div>

                                        <!-- Crop Predictions -->
                                        <div class="space-y-3 pl-4">
                                            @foreach($cropGroups as $crop => $cropPredictions)
                                                @php
                                                    $cropTotal = collect($cropPredictions)->sum('predicted_production');
                                                    $hasMonthly = count($cropPredictions) > 1;
                                                @endphp
                                                
                                                <div class="border-l-4 border-green-500 pl-4 py-2 bg-green-50 rounded-r">
                                                    <div class="flex items-center justify-between mb-2">
                                                        <span class="font-semibold text-gray-800 text-sm">
                                                            {{ ucwords(strtolower($crop)) }}
                                                        </span>
                                                        <span class="font-bold text-green-700">{{ number_format($cropTotal, 2) }} mt</span>
                                                    </div>
                                                    
                                                    @if($hasMonthly)
                                                        <!-- Monthly Breakdown -->
                                                        <div class="grid grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-2 mt-2">
                                                            @foreach($cropPredictions as $pred)
                                                                <div class="bg-white rounded px-2 py-1 text-xs shadow-sm hover:shadow-md transition-shadow">
                                                                    <div class="font-medium text-gray-600">{{ $pred['month'] }}</div>
                                                                    <div class="font-bold text-green-600">{{ number_format($pred['predicted_production'], 1) }} mt</div>
                                                                    @if(isset($pred['confidence']))
                                                                        <div class="text-gray-500 text-[10px]">{{ $pred['confidence'] }}</div>
                                                                    @endif
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    @else
                                                        <!-- Single Prediction -->
                                                        @foreach($cropPredictions as $pred)
                                                            <div class="flex items-center gap-2 text-xs text-gray-600">
                                                                <span class="font-medium">{{ $pred['month'] }}</span>
                                                                <span>•</span>
                                                                <span>{{ ucwords(strtolower($pred['farm_type'])) }}</span>
                                                                <span>•</span>
                                                                <span>{{ number_format($pred['area_harvested']) }} ha</span>
                                                                @if(isset($pred['confidence']))
                                                                    <span>•</span>
                                                                    <span class="text-green-600">{{ $pred['confidence'] }}</span>
                                                                @endif
                                                            </div>
                                                        @endforeach
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
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
        @endif
    </div>

    <!-- Resource Allocation Modal -->
    <div x-show="showResourceModal" 
         x-cloak
         @keydown.escape.window="showResourceModal = false"
         class="fixed inset-0 z-50 overflow-y-auto" 
         aria-labelledby="modal-title" 
         role="dialog" 
         aria-modal="true">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <!-- Background overlay -->
            <div x-show="showResourceModal"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @click="showResourceModal = false"
                 class="fixed inset-0 bg-black bg-opacity-60 transition-opacity" 
                 aria-hidden="true"></div>

            <!-- Center modal -->
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <!-- Modal panel -->
            <div x-show="showResourceModal"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="inline-block align-bottom bg-white rounded-lg text-left overflow-visible shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full"
                 @click.away="showResourceModal = false">
                
                <div class="bg-white px-10 py-8 rounded-lg">
                    <form method="POST" action="{{ route('admin.resources.store') }}" class="space-y-6">
                        @csrf

                        <!-- Modal Header -->
                        <div class="mb-6">
                            <h3 class="text-xl font-bold text-gray-900">Resource Allocation</h3>
                        </div>

                        <!-- Type of Resource -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-900 mb-2">
                                Type of Resource*
                            </label>
                            <select name="resource_type" 
                                    required 
                                    class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-md text-sm text-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-300 focus:border-transparent focus:bg-white transition-colors">
                                <option value="" class="text-gray-400">Select type of resource</option>
                                <option value="Seeds" class="text-gray-700">Seeds</option>
                                <option value="Fertilizer" class="text-gray-700">Fertilizer</option>
                                <option value="Equipment" class="text-gray-700">Equipment</option>
                                <option value="Tools" class="text-gray-700">Tools</option>
                                <option value="Other" class="text-gray-700">Other</option>
                            </select>
                        </div>

                        <!-- Quantity -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-900 mb-2">
                                Quantity*
                            </label>
                            <input type="number" 
                                   name="quantity" 
                                   required 
                                   step="0.01"
                                   min="0"
                                   placeholder="Enter a number"
                                   class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-md text-sm text-gray-700 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-300 focus:border-transparent focus:bg-white transition-colors">
                        </div>

                        <!-- Municipality -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-900 mb-2">
                                Municipality*
                            </label>
                            <select name="municipality" 
                                    required 
                                    class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-md text-sm text-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-300 focus:border-transparent focus:bg-white transition-colors">
                                <option value="" class="text-gray-400">Municipality</option>
                                @foreach($allMunicipalities as $municipality)
                                    <option value="{{ $municipality }}" class="text-gray-700">
                                        {{ ucwords(strtolower($municipality)) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Created by (auto-filled) -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-900 mb-2">
                                Created by (auto-filled)*
                            </label>
                            <input type="text" 
                                   name="created_by" 
                                   value="{{ auth()->user()->name ?? 'admin' }}" 
                                   readonly
                                   class="w-full px-4 py-2.5 bg-gray-100 border border-gray-300 rounded-md text-sm text-gray-500">
                        </div>

                        <!-- Created at (auto-filled) -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-900 mb-2">
                                Created at (auto-filled)*
                            </label>
                            <input type="text" 
                                   name="created_at_display" 
                                   value="{{ date('m/d/Y') }}" 
                                   readonly
                                   class="w-full px-4 py-2.5 bg-gray-100 border border-gray-300 rounded-md text-sm text-gray-500">
                        </div>

                        <!-- Submit Button -->
                        <div class="flex justify-start pt-4">
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

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/hammerjs@2.0.8"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-zoom@2.0.1/dist/chartjs-plugin-zoom.min.js"></script>
    <script>
        // Global variable to store chart instance
        let trendChartInstance = null;
        
        // Reset zoom function
        function resetZoom() {
            if (trendChartInstance) {
                trendChartInstance.resetZoom();
                console.log('Chart zoom reset');
            }
        }
        
        function dataAnalytics() {
            return {
                init() {
                    // Wait for Chart.js to be fully loaded
                    if (typeof Chart === 'undefined') {
                        console.log('Chart.js not loaded yet, waiting...');
                        setTimeout(() => this.init(), 100);
                        return;
                    }
                    console.log('Initializing trend chart...');
                    this.initTrendChart();
                },

                initTrendChart() {
                    const ctx = document.getElementById('trendChart');
                    if (!ctx) {
                        console.log('Chart canvas not found');
                        return;
                    }

                    // Get actual data from database (already formatted)
                    const chartData = @json($trendChartData);
                    console.log('Chart Data:', chartData);
                    console.log('Labels:', chartData.labels);
                    console.log('Datasets count:', chartData.datasets.length);
                    
                    // Log each dataset details
                    chartData.datasets.forEach((dataset, index) => {
                        console.log(`Dataset ${index} (${dataset.label}):`, dataset.data);
                        console.log(`  - Has data:`, dataset.data.some(val => val > 0));
                        console.log(`  - Max value:`, Math.max(...dataset.data));
                        console.log(`  - Sum:`, dataset.data.reduce((a, b) => a + b, 0));
                    });
                    
                    if (!chartData || !chartData.labels || !chartData.datasets) {
                        console.error('Invalid chart data structure');
                        return;
                    }

                    if (chartData.labels.length === 0 || chartData.datasets.length === 0) {
                        console.log('No data to display');
                        return;
                    }
                    
                    // Ensure all datasets have proper configuration
                    chartData.datasets = chartData.datasets.map(dataset => ({
                        ...dataset,
                        borderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 7,
                        pointHitRadius: 10,
                        fill: false
                    }));
                    
                    console.log('Final chart data being passed to Chart.js:', chartData);
                    
                    // Store chart instance globally for zoom reset
                    trendChartInstance = new Chart(ctx, {
                        type: 'line',
                        data: chartData,
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            animation: {
                                duration: 1000,
                                easing: 'easeInOutQuart',
                                delay: (context) => {
                                    let delay = 0;
                                    if (context.type === 'data' && context.mode === 'default') {
                                        delay = context.dataIndex * 30 + context.datasetIndex * 100;
                                    }
                                    return delay;
                                }
                            },
                            transitions: {
                                zoom: {
                                    animation: {
                                        duration: 500,
                                        easing: 'easeInOutQuart'
                                    }
                                },
                                pan: {
                                    animation: {
                                        duration: 300,
                                        easing: 'easeInOutQuad'
                                    }
                                }
                            },
                            plugins: {
                                legend: {
                                    display: true,
                                    position: 'bottom',
                                    labels: {
                                        padding: 12,
                                        font: {
                                            size: 13,
                                            weight: '500'
                                        },
                                        usePointStyle: true,
                                        pointStyle: 'circle',
                                        boxWidth: 10,
                                        boxHeight: 10
                                    }
                                },
                                tooltip: {
                                    mode: 'index',
                                    intersect: false,
                                    backgroundColor: 'rgba(0, 0, 0, 0.85)',
                                    padding: 16,
                                    titleFont: {
                                        size: 16,
                                        weight: 'bold'
                                    },
                                    bodyFont: {
                                        size: 15
                                    },
                                    callbacks: {
                                        label: function(context) {
                                            let label = context.dataset.label || '';
                                            if (label) {
                                                label += ': ';
                                            }
                                            // Ensure precise number display
                                            const value = typeof context.parsed.y === 'number' 
                                                ? context.parsed.y.toLocaleString(undefined, {
                                                    minimumFractionDigits: 2,
                                                    maximumFractionDigits: 2
                                                })
                                                : context.parsed.y;
                                            label += value + ' mt';
                                            return label;
                                        }
                                    }
                                },
                                zoom: {
                                    zoom: {
                                        wheel: {
                                            enabled: true,
                                            speed: 0.05
                                        },
                                        pinch: {
                                            enabled: true
                                        },
                                        mode: 'xy'
                                    },
                                    pan: {
                                        enabled: true,
                                        mode: 'xy',
                                        onPanComplete: function({chart}) {
                                            // Ensure chart updates properly after panning
                                            chart.update('none');
                                        }
                                    },
                                    limits: {
                                        x: {min: 'original', max: 'original', minRange: 2},
                                        y: {min: 0, max: 'original', minRange: 50}
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
                                            size: 18,
                                            weight: 'bold'
                                        },
                                        padding: { top: 0, bottom: 12 }
                                    },
                                    ticks: {
                                        font: {
                                            size: 15
                                        },
                                        callback: function(value) {
                                            return value.toLocaleString() + ' mt';
                                        },
                                        precision: 2
                                    },
                                    grid: {
                                        color: 'rgba(0, 0, 0, 0.1)',
                                        lineWidth: 1
                                    }
                                },
                                x: {
                                    title: {
                                        display: true,
                                        text: 'Year',
                                        font: {
                                            size: 18,
                                            weight: 'bold'
                                        },
                                        padding: { top: 12, bottom: 0 }
                                    },
                                    ticks: {
                                        font: {
                                            size: 15
                                        },
                                        autoSkip: true,
                                        maxRotation: 0,
                                        minRotation: 0
                                    },
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
                }
            }
        }
    </script>
    @endpush

    <!-- Municipality Details Modal -->
    <div x-show="showMunicipalityModal" 
         x-cloak
         @keydown.escape.window="closeMunicipalityModal()"
         style="display: none;"
         class="fixed inset-0 z-50 overflow-hidden" 
         aria-labelledby="modal-title" 
         role="dialog" 
         aria-modal="true">
        
        <!-- Background overlay -->
        <div x-show="showMunicipalityModal"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="closeMunicipalityModal()"
             class="fixed inset-0 bg-black bg-opacity-40 transition-opacity" 
             aria-hidden="true"></div>

        <!-- Sidebar panel -->
        <div class="fixed inset-y-0 right-0 flex max-w-full pl-10">
            <div x-show="showMunicipalityModal"
                 x-transition:enter="transform transition ease-in-out duration-300"
                 x-transition:enter-start="translate-x-full"
                 x-transition:enter-end="translate-x-0"
                 x-transition:leave="transform transition ease-in-out duration-300"
                 x-transition:leave-start="translate-x-0"
                 x-transition:leave-end="translate-x-full"
                 class="w-screen max-w-md">
                
                <div class="h-full flex flex-col bg-white shadow-xl overflow-y-auto">
                    <!-- Header with Close Button -->
                    <div class="sticky top-0 z-10 bg-gradient-to-r from-green-600 to-blue-600 px-6 py-4 flex items-center justify-between shadow-md">
                        <div class="flex items-center gap-3">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <h3 class="text-xl font-bold text-white" x-text="selectedMunicipality"></h3>
                        </div>
                        <button @click="closeMunicipalityModal()" 
                                type="button" 
                                class="text-white hover:text-gray-200 transition-colors rounded-full p-1 hover:bg-white/20">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    @php
                        $municipalityStats = [];
                        foreach($allMunicipalities as $mun) {
                            // Build query with all filters applied
                            $statsQuery = \App\Models\Crop::where('municipality', $mun);
                            
                            // Apply all filters to show data for selected year, crop, month
                            if ($filterYear) {
                                $statsQuery->where('year', $filterYear);
                            }
                            if ($filterMonth) {
                                $statsQuery->where('month', $filterMonth);
                            }
                            if ($filterCrop) {
                                $statsQuery->where('crop', $filterCrop);
                            }
                            if ($filterFarmType) {
                                $statsQuery->where('farm_type', $filterFarmType);
                            }
                            
                            $stats = $statsQuery->selectRaw('
                                    SUM(production) / 1000 as total_production,
                                    SUM(area_harvested) as area_harvested,
                                    AVG(productivity) as productivity,
                                    COUNT(DISTINCT month) as records
                                ')
                                ->first();
                            
                            $municipalityStats[$mun] = $stats;
                        }
                    @endphp

                    <!-- Content -->
                    <div class="flex-1 px-6 py-6">
                        <p class="text-sm text-gray-600 mb-6 animate-fade-in">Click on municipality data below</p>

                        <template x-for="(municipality, index) in {{ json_encode(array_keys($municipalityStats)) }}" :key="index">
                            <div x-show="selectedMunicipality === municipality" class="space-y-6">
                                <!-- Overview Section -->
                                <div class="bg-gradient-to-br from-green-50 to-blue-50 rounded-lg p-5 border border-green-200 animate-fade-in-up animate-delay-100">
                                    <h4 class="text-sm font-semibold text-gray-700 mb-4">OVERVIEW</h4>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div class="hover-lift">
                                            <p class="text-xs text-gray-600 mb-1">Total Production</p>
                                            <p class="text-xl font-bold text-green-700">
                                                @foreach($municipalityStats as $munName => $stat)
                                                    <span x-show="selectedMunicipality === '{{ $munName }}'">
                                                        {{ number_format($stat->total_production ?? 0, 2) }} MT
                                                    </span>
                                                @endforeach
                                            </p>
                                        </div>
                                        <div class="hover-lift">
                                            <p class="text-xs text-gray-600 mb-1">Area Harvested</p>
                                            <p class="text-xl font-bold text-green-700">
                                                @foreach($municipalityStats as $munName => $stat)
                                                    <span x-show="selectedMunicipality === '{{ $munName }}'">
                                                        {{ number_format($stat->area_harvested ?? 0) }} ha
                                                    </span>
                                                @endforeach
                                            </p>
                                        </div>
                                        <div class="hover-lift">
                                            <p class="text-xs text-gray-600 mb-1">Productivity</p>
                                            <p class="text-xl font-bold text-green-700">
                                                @foreach($municipalityStats as $munName => $stat)
                                                    <span x-show="selectedMunicipality === '{{ $munName }}'">
                                                        {{ number_format($stat->productivity ?? 0, 2) }} kg/ha
                                                    </span>
                                                @endforeach
                                            </p>
                                        </div>
                                        <div class="hover-lift">
                                            <p class="text-xs text-gray-600 mb-1">Records</p>
                                            <p class="text-xl font-bold text-green-700">
                                                @foreach($municipalityStats as $munName => $stat)
                                                    <span x-show="selectedMunicipality === '{{ $munName }}'">
                                                        {{ $stat->records ?? 0 }} months
                                                    </span>
                                                @endforeach
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                @php
                                    $farmTypeDistributions = [];
                                    $farmTypeMonthsData = [];
                                    foreach($allMunicipalities as $mun) {
                                        // Build query with filters for farm type distribution
                                        $irrigatedQuery = \App\Models\Crop::where('municipality', $mun)
                                            ->where('farm_type', 'Irrigated');
                                        $rainfedQuery = \App\Models\Crop::where('municipality', $mun)
                                            ->where('farm_type', 'Rainfed');
                                        
                                        // Apply all filters
                                        if ($filterYear) {
                                            $irrigatedQuery->where('year', $filterYear);
                                            $rainfedQuery->where('year', $filterYear);
                                        }
                                        if ($filterMonth) {
                                            $irrigatedQuery->where('month', $filterMonth);
                                            $rainfedQuery->where('month', $filterMonth);
                                        }
                                        if ($filterCrop) {
                                            $irrigatedQuery->where('crop', $filterCrop);
                                            $rainfedQuery->where('crop', $filterCrop);
                                        }
                                        
                                        $irrigated = $irrigatedQuery->sum('production') / 1000; // Convert to MT
                                        $rainfed = $rainfedQuery->sum('production') / 1000; // Convert to MT
                                        $total = $irrigated + $rainfed;
                                        
                                        $farmTypeDistributions[$mun] = [
                                            'irrigated' => $total > 0 ? ($irrigated / $total) * 100 : 50,
                                            'rainfed' => $total > 0 ? ($rainfed / $total) * 100 : 50,
                                            'irrigated_mt' => $irrigated,
                                            'rainfed_mt' => $rainfed
                                        ];
                                        
                                        // Get months for farm type data
                                        $monthsQuery = \App\Models\Crop::where('municipality', $mun);
                                        if ($filterYear) {
                                            $monthsQuery->where('year', $filterYear);
                                        }
                                        if ($filterMonth) {
                                            $monthsQuery->where('month', $filterMonth);
                                        }
                                        if ($filterCrop) {
                                            $monthsQuery->where('crop', $filterCrop);
                                        }
                                        $months = $monthsQuery->distinct()->pluck('month')->toArray();
                                        $farmTypeMonthsData[$mun] = $months;
                                    }
                                @endphp

                                <!-- Farm Type Distribution -->
                                <div class="bg-white rounded-lg">
                                    <h4 class="text-sm font-semibold text-gray-700 mb-2">FARM TYPE DISTRIBUTION</h4>
                                    
                                    @foreach($farmTypeDistributions as $munName => $distribution)
                                        <div x-show="selectedMunicipality === '{{ $munName }}'">
                                            <!-- Period Info -->
                                            @if(isset($farmTypeMonthsData[$munName]) && count($farmTypeMonthsData[$munName]) > 0)
                                                <p class="text-xs text-gray-500 mb-3">
                                                    @if(count($farmTypeMonthsData[$munName]) == 1)
                                                        Data from: <span class="font-semibold">{{ $farmTypeMonthsData[$munName][0] }}</span>
                                                    @elseif(count($farmTypeMonthsData[$munName]) <= 3)
                                                        Data from: <span class="font-semibold">{{ implode(', ', $farmTypeMonthsData[$munName]) }}</span>
                                                    @else
                                                        Data from: <span class="font-semibold">{{ count($farmTypeMonthsData[$munName]) }} months</span> 
                                                        ({{ $farmTypeMonthsData[$munName][0] }} - {{ end($farmTypeMonthsData[$munName]) }})
                                                    @endif
                                                    @if($filterYear)
                                                        in {{ $filterYear }}
                                                    @endif
                                                </p>
                                            @endif
                                            
                                            <!-- Irrigated -->
                                            <div class="mb-4">
                                                <div class="flex items-center justify-between mb-2">
                                                    <span class="text-sm font-medium text-gray-700 uppercase">Irrigated</span>
                                                    <span class="text-sm font-bold text-green-600">{{ number_format($distribution['irrigated'], 1) }}%</span>
                                                </div>
                                                <div class="w-full bg-gray-200 rounded-full h-3">
                                                    <div class="bg-green-500 h-3 rounded-full transition-all duration-500" style="width: {{ $distribution['irrigated'] }}%"></div>
                                                </div>
                                                <span class="text-xs text-gray-500 mt-1 block">{{ number_format($distribution['irrigated_mt'], 2) }} MT</span>
                                            </div>

                                            <!-- Rainfed -->
                                            <div class="mb-4">
                                                <div class="flex items-center justify-between mb-2">
                                                    <span class="text-sm font-medium text-gray-700 uppercase">Rainfed</span>
                                                    <span class="text-sm font-bold text-green-600">{{ number_format($distribution['rainfed'], 1) }}%</span>
                                                </div>
                                                <div class="w-full bg-gray-200 rounded-full h-3">
                                                    <div class="bg-green-500 h-3 rounded-full transition-all duration-500" style="width: {{ $distribution['rainfed'] }}%"></div>
                                                </div>
                                                <span class="text-xs text-gray-500 mt-1 block">{{ number_format($distribution['rainfed_mt'], 2) }} MT</span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                @php
                                    $monthlyData = [];
                                    $yearlyData = [];
                                    $topCropsData = [];
                                    
                                    // Determine if we should show monthly or yearly view
                                    // Show yearly view only when no meaningful filters are selected
                                    $showYearlyView = empty($filterYear) && empty($filterCrop) && empty($filterMonth);
                                    
                                    foreach($allMunicipalities as $mun) {
                                        if ($showYearlyView) {
                                            // Show yearly trends when no filters selected
                                            $years = \App\Models\Crop::where('municipality', $mun)
                                                ->select('year', \DB::raw('SUM(production) / 1000 as production'))
                                                ->groupBy('year')
                                                ->orderBy('year', 'desc')
                                                ->limit(10)
                                                ->get();
                                            $yearlyData[$mun] = $years;
                                            
                                            // Get top 10 crops for this municipality (all years)
                                            $topCrops = \App\Models\Crop::where('municipality', $mun)
                                                ->select('crop', \DB::raw('SUM(production) / 1000 as production'))
                                                ->groupBy('crop')
                                                ->orderByDesc('production')
                                                ->limit(10)
                                                ->get();
                                            $topCropsData[$mun] = $topCrops;
                                        } else {
                                            // Show monthly production with filters
                                            $monthsQuery = \App\Models\Crop::where('municipality', $mun);
                                            
                                            // Apply all filters
                                            if ($filterYear) {
                                                $monthsQuery->where('year', $filterYear);
                                            }
                                            if ($filterCrop) {
                                                $monthsQuery->where('crop', $filterCrop);
                                            }
                                            if ($filterFarmType) {
                                                $monthsQuery->where('farm_type', $filterFarmType);
                                            }
                                            
                                            $months = $monthsQuery->select('month', \DB::raw('SUM(production) / 1000 as production'))
                                                ->groupBy('month')
                                                ->orderByRaw("FIELD(month, 'JAN', 'FEB', 'MAR', 'APR', 'MAY', 'JUN', 'JUL', 'AUG', 'SEP', 'OCT', 'NOV', 'DEC')")
                                                ->get();
                                            $monthlyData[$mun] = $months;
                                        }
                                    }
                                @endphp

                                <!-- Monthly Production OR Yearly Trends -->
                                <div class="bg-white rounded-lg">
                                    @if($showYearlyView)
                                        <h4 class="text-sm font-semibold text-gray-700 mb-4">YEARLY PRODUCTION TRENDS</h4>
                                        
                                        @foreach($yearlyData as $munName => $years)
                                            <div x-show="selectedMunicipality === '{{ $munName }}'" class="space-y-2">
                                                @php
                                                    $maxProduction = $years->max('production') ?: 1;
                                                @endphp
                                                @foreach($years as $yearData)
                                                    <div class="flex items-center gap-3">
                                                        <span class="text-xs font-medium text-gray-600 w-12">{{ $yearData->year }}</span>
                                                        <div class="flex-1 bg-gray-100 rounded h-8 relative overflow-hidden">
                                                            <div class="absolute inset-y-0 left-0 bg-blue-500 transition-all duration-500" 
                                                                 style="width: {{ ($yearData->production / $maxProduction) * 100 }}%"></div>
                                                            <span class="absolute inset-0 flex items-center justify-center text-xs font-semibold text-gray-700">
                                                                {{ number_format($yearData->production, 2) }} MT
                                                            </span>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endforeach
                                    @else
                                        <h4 class="text-sm font-semibold text-gray-700 mb-4">MONTHLY PRODUCTION</h4>
                                        
                                        @foreach($monthlyData as $munName => $months)
                                            <div x-show="selectedMunicipality === '{{ $munName }}'" class="space-y-2">
                                                @if($months->count() > 0)
                                                    @php
                                                        $maxProduction = $months->max('production') ?: 1;
                                                    @endphp
                                                    @foreach($months as $monthData)
                                                        <div class="flex items-center gap-3">
                                                            <span class="text-xs font-medium text-gray-600 w-8">{{ $monthData->month }}</span>
                                                            <div class="flex-1 bg-gray-100 rounded h-8 relative overflow-hidden">
                                                                <div class="absolute inset-y-0 left-0 bg-green-500 transition-all duration-500" 
                                                                     style="width: {{ ($monthData->production / $maxProduction) * 100 }}%"></div>
                                                                <span class="absolute inset-0 flex items-center justify-center text-xs font-semibold text-gray-700">
                                                                    {{ number_format($monthData->production, 2) }} MT
                                                                </span>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                @else
                                                    <p class="text-sm text-gray-500 italic py-4 text-center">
                                                        No production data available for the selected filters.
                                                        @if($filterYear)
                                                            <br><span class="text-xs">Try selecting a different year or clearing filters.</span>
                                                        @endif
                                                    </p>
                                                @endif
                                            </div>
                                        @endforeach
                                    @endif
                                </div>

                                <!-- Top Crops (only shown in yearly view) -->
                                @if($showYearlyView)
                                    <div class="bg-white rounded-lg mt-6">
                                        <h4 class="text-sm font-semibold text-gray-700 mb-4">TOP CROPS (ALL YEARS)</h4>
                                        
                                        @foreach($topCropsData as $munName => $topCrops)
                                            <div x-show="selectedMunicipality === '{{ $munName }}'" class="space-y-2">
                                                @php
                                                    $maxCropProduction = $topCrops->max('production') ?: 1;
                                                @endphp
                                                @foreach($topCrops as $cropData)
                                                    <div class="flex items-center gap-3">
                                                        <span class="text-xs font-medium text-gray-600 w-24 truncate" title="{{ $cropData->crop }}">{{ ucfirst(strtolower($cropData->crop)) }}</span>
                                                        <div class="flex-1 bg-gray-100 rounded h-8 relative overflow-hidden">
                                                            <div class="absolute inset-y-0 left-0 bg-green-600 transition-all duration-500" 
                                                                 style="width: {{ ($cropData->production / $maxCropProduction) * 100 }}%"></div>
                                                            <span class="absolute inset-0 flex items-center justify-center text-xs font-semibold text-gray-700">
                                                                {{ number_format($cropData->production, 2) }} MT
                                                            </span>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endforeach
                                    </div>
                                @endif

                                @php
                                    $cropDistributions = [];
                                    $cropMonthsData = [];
                                    foreach($allMunicipalities as $mun) {
                                        // Build query with filters for crop distribution
                                        $cropsQuery = \App\Models\Crop::where('municipality', $mun);
                                        
                                        // Apply filters
                                        if ($filterYear) {
                                            $cropsQuery->where('year', $filterYear);
                                        }
                                        if ($filterMonth) {
                                            $cropsQuery->where('month', $filterMonth);
                                        }
                                        if ($filterFarmType) {
                                            $cropsQuery->where('farm_type', $filterFarmType);
                                        }
                                        
                                        $crops = $cropsQuery->select('crop', \DB::raw('SUM(production) as total_production'))
                                            ->groupBy('crop')
                                            ->orderByDesc('total_production')
                                            ->limit(8)
                                            ->get();
                                        $cropDistributions[$mun] = $crops;
                                        
                                        // Get months for this municipality's data
                                        $monthsQuery = \App\Models\Crop::where('municipality', $mun);
                                        if ($filterYear) {
                                            $monthsQuery->where('year', $filterYear);
                                        }
                                        if ($filterMonth) {
                                            $monthsQuery->where('month', $filterMonth);
                                        }
                                        if ($filterFarmType) {
                                            $monthsQuery->where('farm_type', $filterFarmType);
                                        }
                                        $months = $monthsQuery->distinct()->pluck('month')->toArray();
                                        $cropMonthsData[$mun] = $months;
                                    }
                                @endphp

                                <!-- Crop Distribution Chart -->
                                <div class="bg-white rounded-lg">
                                    <h4 class="text-sm font-semibold text-gray-700 mb-2">CROP DISTRIBUTION</h4>
                                    
                                    @foreach($cropDistributions as $munName => $crops)
                                        <div x-show="selectedMunicipality === '{{ $munName }}'" class="space-y-3">
                                            <!-- Period Info -->
                                            @if(isset($cropMonthsData[$munName]) && count($cropMonthsData[$munName]) > 0)
                                                <p class="text-xs text-gray-500 mb-3">
                                                    @if(count($cropMonthsData[$munName]) == 1)
                                                        Data from: <span class="font-semibold">{{ $cropMonthsData[$munName][0] }}</span>
                                                    @elseif(count($cropMonthsData[$munName]) <= 3)
                                                        Data from: <span class="font-semibold">{{ implode(', ', $cropMonthsData[$munName]) }}</span>
                                                    @else
                                                        Data from: <span class="font-semibold">{{ count($cropMonthsData[$munName]) }} months</span> 
                                                        ({{ $cropMonthsData[$munName][0] }} - {{ end($cropMonthsData[$munName]) }})
                                                    @endif
                                                    @if($filterYear)
                                                        in {{ $filterYear }}
                                                    @endif
                                                </p>
                                            @endif
                                            
                                            @php
                                                $totalProduction = $crops->sum('total_production');
                                                $colors = [
                                                    'bg-blue-500', 'bg-green-500', 'bg-yellow-500', 'bg-red-500',
                                                    'bg-purple-500', 'bg-pink-500', 'bg-indigo-500', 'bg-orange-500'
                                                ];
                                            @endphp
                                            
                                            <!-- Pie Chart Canvas -->
                                            <div class="flex justify-center mb-4">
                                                <canvas id="cropChart_{{ str_replace(' ', '_', $munName) }}" 
                                                        class="max-w-[250px] max-h-[250px]"
                                                        data-municipality="{{ $munName }}"
                                                        data-crops="{{ json_encode($crops->pluck('crop')->toArray()) }}"
                                                        data-production="{{ json_encode($crops->pluck('total_production')->map(function($val) { return round($val / 1000, 2); })->toArray()) }}">
                                                </canvas>
                                            </div>

                                            <!-- Crop List -->
                                            @foreach($crops as $index => $crop)
                                                @php
                                                    $percentage = $totalProduction > 0 ? ($crop->total_production / $totalProduction) * 100 : 0;
                                                @endphp
                                                <div class="flex items-center justify-between py-2 border-b border-gray-100">
                                                    <div class="flex items-center gap-2">
                                                        <div class="w-3 h-3 rounded-full {{ $colors[$index % count($colors)] }}"></div>
                                                        <span class="text-sm font-medium text-gray-700">{{ ucwords(strtolower($crop->crop)) }}</span>
                                                    </div>
                                                    <div class="text-right">
                                                        <span class="text-sm font-bold text-gray-800">{{ number_format($percentage, 1) }}%</span>
                                                        <span class="text-xs text-gray-500 block">{{ number_format($crop->total_production / 1000, 2) }} mt</span>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        [x-cloak] { display: none !important; }
    </style>

    <script>
        // Initialize crop distribution charts when modal opens
        document.addEventListener('alpine:initialized', () => {
            // Wait for Alpine to finish initializing
            setTimeout(() => {
                initializeCropCharts();
            }, 500);
        });

        function initializeCropCharts() {
            const canvases = document.querySelectorAll('[id^="cropChart_"]');
            
            canvases.forEach(canvas => {
                const municipality = canvas.dataset.municipality;
                const crops = JSON.parse(canvas.dataset.crops);
                const production = JSON.parse(canvas.dataset.production);
                
                if (crops.length === 0) return;

                const colors = [
                    'rgb(59, 130, 246)', 'rgb(34, 197, 94)', 'rgb(234, 179, 8)', 'rgb(239, 68, 68)',
                    'rgb(168, 85, 247)', 'rgb(236, 72, 153)', 'rgb(99, 102, 241)', 'rgb(251, 146, 60)'
                ];

                new Chart(canvas, {
                    type: 'doughnut',
                    data: {
                        labels: crops.map(crop => crop.charAt(0) + crop.slice(1).toLowerCase()),
                        datasets: [{
                            data: production,
                            backgroundColor: colors.slice(0, crops.length),
                            borderColor: '#ffffff',
                            borderWidth: 2
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        const label = context.label || '';
                                        const value = context.parsed || 0;
                                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                        const percentage = ((value / total) * 100).toFixed(1);
                                        return label + ': ' + value.toFixed(2) + ' mt (' + percentage + '%)';
                                    }
                                }
                            }
                        }
                    }
                });
            });
        }
        
        /**
         * Smart filter handling - Auto-clears month filter when crop + municipality + year are all active
         * This ensures users see all 12 months for the selected crop/municipality/year combination
         */
        function checkAndClearMonthIfNeeded() {
            const crop = document.getElementById('cropFilter').value;
            const municipality = document.getElementById('municipalityFilter').value;
            const year = document.getElementById('yearFilter').value;
            
            // If all three are selected, clear month to show monthly chart
            if (crop && municipality && year) {
                document.getElementById('monthFilter').value = '';
            }
        }
        
        function handleCropFilterChange() {
            checkAndClearMonthIfNeeded();
            document.getElementById('filterForm').submit();
        }
        
        function handleMunicipalityFilterChange() {
            checkAndClearMonthIfNeeded();
            document.getElementById('filterForm').submit();
        }
        
        function handleYearFilterChange() {
            checkAndClearMonthIfNeeded();
            document.getElementById('filterForm').submit();
        }
    </script>
</x-admin-layout>