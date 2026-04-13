<x-admin-layout>
    <x-slot name="title">Data & Analytics</x-slot>

    <div class="space-y-5 lg:space-y-6 admin-dashboard-shell dashboard-reduced-motion" x-data="{ 
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
        <!-- Page Header -->
        <div class="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
            <div class="max-w-3xl">
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-green-700">Admin Dashboard</p>
                <h1 class="mt-1 text-3xl font-bold text-gray-900 lg:text-4xl">Data & Analytics</h1>
                <p class="mt-2 text-sm text-gray-600 lg:text-base">
                    Monitor crop production performance, track municipality trends, and review AI forecasts from one workspace.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('admin.export-summary') }}" class="inline-flex items-center px-4 py-2.5 bg-white border border-gray-300 text-gray-700 font-semibold rounded-lg hover:bg-gray-50 transition-colors shadow-sm">
                    Export Summary
                </a>
                <button type="button"
                        @click="showResourceModal = true"
                        class="inline-flex items-center px-4 py-2.5 bg-green-700 hover:bg-green-800 text-white font-semibold rounded-lg transition-colors shadow-sm">
                    Allocate Resource
                </button>
                <button onclick="document.getElementById('predictions-section')?.scrollIntoView({ behavior: 'smooth' })"
                        class="inline-flex items-center px-4 py-2.5 bg-emerald-100 hover:bg-emerald-200 text-emerald-800 font-semibold rounded-lg transition-colors border border-emerald-200">
                    View AI Predictions
                </button>
            </div>
        </div>

        <!-- Filter Bar -->
        <form method="GET" action="{{ route('admin.dashboard') }}" id="filterForm" class="admin-section-card p-4 lg:p-5">
            <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="admin-section-title">Filters</h2>
                    <p class="admin-section-subtitle">Refine production data by crop, location, period, and farm type.</p>
                </div>
                <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center justify-center px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-lg transition-colors whitespace-nowrap">
                    Reset All
                </a>
            </div>

            <div class="admin-filter-grid">
                <!-- Crop Filter -->
                <div class="admin-filter-field relative">
                    <label for="cropFilter">Crop</label>
                    <select name="crop"
                            id="cropFilter"
                            onchange="handleCropFilterChange()"
                            class="appearance-none w-full px-4 py-2.5 pr-10 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-500 focus:border-transparent bg-white text-gray-700 cursor-pointer">
                        <option value="">All crops</option>
                        @foreach(App\Models\Crop::select('crop')->distinct()->orderBy('crop')->pluck('crop') as $crop)
                            <option value="{{ $crop }}" {{ request('crop') == $crop ? 'selected' : '' }}>
                                {{ ucwords(strtolower($crop)) }}
                            </option>
                        @endforeach
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 top-6 flex items-center px-3 text-gray-500">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                </div>

                <!-- Municipality Filter -->
                <div class="admin-filter-field relative">
                    <label for="municipalityFilter">Municipality</label>
                    <select name="municipality"
                            id="municipalityFilter"
                            onchange="handleMunicipalityFilterChange()"
                            class="appearance-none w-full px-4 py-2.5 pr-10 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-500 focus:border-transparent bg-white text-gray-700 cursor-pointer">
                        <option value="">All municipalities</option>
                        @foreach($allMunicipalities as $municipality)
                            <option value="{{ $municipality }}" {{ $filterMunicipality == $municipality ? 'selected' : '' }}>
                                {{ ucwords(strtolower($municipality)) }}
                            </option>
                        @endforeach
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 top-6 flex items-center px-3 text-gray-500">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                </div>

                <!-- Month Filter -->
                <div class="admin-filter-field relative">
                    <label for="monthFilter">Month</label>
                    <select name="month"
                            id="monthFilter"
                            onchange="document.getElementById('filterForm').submit()"
                            class="appearance-none w-full px-4 py-2.5 pr-10 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-500 focus:border-transparent bg-white text-gray-700 cursor-pointer">
                        <option value="">All months</option>
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
                    <div class="pointer-events-none absolute inset-y-0 right-0 top-6 flex items-center px-3 text-gray-500">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                </div>

                <!-- Year Filter -->
                <div class="admin-filter-field relative">
                    <label for="yearFilter">Year</label>
                    <select name="year"
                            id="yearFilter"
                            onchange="handleYearFilterChange()"
                            class="appearance-none w-full px-4 py-2.5 pr-10 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-500 focus:border-transparent bg-white text-gray-700 cursor-pointer">
                        <option value="">All years</option>
                        @foreach($allYears as $year)
                            <option value="{{ $year }}" {{ $filterYear == $year ? 'selected' : '' }}>{{ $year }}</option>
                        @endforeach
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 top-6 flex items-center px-3 text-gray-500">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                </div>

                <!-- Farm Type Filter -->
                <div class="admin-filter-field relative">
                    <label for="farmTypeFilter">Farm Type</label>
                    <select id="farmTypeFilter" name="farm_type" onchange="document.getElementById('filterForm').submit()" class="appearance-none w-full px-4 py-2.5 pr-10 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-500 focus:border-transparent bg-white text-gray-700 cursor-pointer">
                        <option value="">All types</option>
                        <option value="RAINFED" {{ $filterFarmType == 'RAINFED' ? 'selected' : '' }}>Rainfed</option>
                        <option value="IRRIGATED" {{ $filterFarmType == 'IRRIGATED' ? 'selected' : '' }}>Irrigated</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 top-6 flex items-center px-3 text-gray-500">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                </div>
            </div>

            @if($filterMunicipality)
                <div class="mt-4 flex flex-wrap items-center gap-3">
                    <button type="button"
                            @click="toggleMunicipalityModal()"
                            class="inline-flex items-center gap-2 px-4 py-2.5 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition-colors shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                        <span x-text="showMunicipalityModal ? 'Hide Municipality Details' : 'View Municipality Details'"></span>
                    </button>
                    <p class="text-sm text-gray-600">
                        Focused on <span class="font-semibold text-gray-800">{{ ucwords(strtolower($filterMunicipality)) }}</span>.
                    </p>
                </div>
            @endif
        </form>

        <!-- Crop Production Chart -->
        <div class="admin-section-card p-5 lg:p-6">
            <div class="admin-section-header mb-5">
                <div class="flex items-start gap-3">
                    <div class="admin-kpi-icon">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                    <div>
                        @php
                            $monthNames = ['JAN' => 'January', 'FEB' => 'February', 'MAR' => 'March', 'APR' => 'April',
                                          'MAY' => 'May', 'JUN' => 'June', 'JUL' => 'July', 'AUG' => 'August',
                                          'SEP' => 'September', 'OCT' => 'October', 'NOV' => 'November', 'DEC' => 'December'];
                        @endphp
                        <h2 class="admin-section-title">
                            @if($chartMode === 'crop_breakdown' || $chartMode === 'crops')
                                Top Crop Production Breakdown
                            @elseif($chartMode === 'municipalities')
                                Municipality Performance Comparison
                            @elseif($chartMode === 'monthly_crop' || $chartMode === 'monthly' || $chartMode === 'monthly_year')
                                Monthly Harvest Overview
                            @else
                                Year-over-Year Production Overview
                            @endif
                        </h2>
                        <p class="admin-section-subtitle mt-1">
                            @if($chartMode === 'crop_breakdown')
                                Showing crops produced in {{ ucwords(strtolower($filterMunicipality)) }}
                                @if($filterMonth) during {{ $monthNames[$filterMonth] ?? $filterMonth }} @endif
                                @if($filterYear) {{ $filterYear }} @endif
                            @elseif($chartMode === 'crops')
                                Showing top crops by harvest amount
                            @elseif($chartMode === 'municipalities')
                                Comparing production across different municipalities
                            @elseif($chartMode === 'monthly_crop')
                                {{ ucwords(strtolower($filterCrop)) }} production each month in {{ ucwords(strtolower($filterMunicipality)) }}
                            @elseif($chartMode === 'monthly_year')
                                Total production each month for {{ $filterYear }}
                            @elseif($chartMode === 'monthly')
                                Monthly harvest for {{ $filterMunicipality ? ucwords(strtolower($filterMunicipality)) : 'all areas' }}
                            @else
                                Total harvested volume over time across all municipalities
                            @endif
                        </p>
                    </div>
                </div>
                <span class="admin-chip bg-green-100 text-green-800">Click data points for details</span>
            </div>

            @php
                $hasChartData = isset($trendChartData) &&
                               isset($trendChartData['labels']) &&
                               isset($trendChartData['datasets']) &&
                               count($trendChartData['labels']) > 0 &&
                               count($trendChartData['datasets']) > 0;
            @endphp

            @if($hasChartData)
                <div class="admin-chart-card relative h-[440px] md:h-[500px] xl:h-[560px] p-2 sm:p-3">
                    <canvas id="trendChart" class="h-full w-full cursor-pointer"></canvas>
                </div>
            @else
                <div class="admin-chart-card h-[440px] md:h-[500px] xl:h-[560px] flex items-center justify-center border-2 border-dashed border-gray-200">
                    <div class="text-center px-4">
                        <svg class="w-20 h-20 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                        <p class="text-gray-700 font-semibold text-lg mb-1">No data matches your current filters</p>
                        <p class="text-sm text-gray-600 mb-4">
                            @if($filterMunicipality || $filterMonth || $filterYear)
                                Try adjusting your filters or clear them to see all production records.
                            @else
                                Upload crop production data to unlock trend analytics.
                            @endif
                        </p>
                        @if($filterMunicipality || $filterMonth || $filterYear)
                            <a href="{{ route('admin.dashboard') }}" class="inline-block px-4 py-2 bg-gray-700 hover:bg-gray-800 text-white rounded-lg text-sm transition-colors">
                                Clear Filters
                            </a>
                        @else
                            <a href="{{ route('admin.crop-data.upload') }}" class="inline-block px-4 py-2 bg-green-700 hover:bg-green-800 text-white rounded-lg text-sm transition-colors">
                                Upload Data
                            </a>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        <!-- Chart Details Side Panel - Slides from right on click -->
        <div id="chart-details-panel" class="fixed top-0 right-0 z-[2000] h-full w-full transform translate-x-full transition-transform duration-300 ease-in-out overflow-y-auto border-l border-gray-200 bg-gray-50 shadow-2xl sm:w-[420px] xl:w-[460px]">
            <div class="p-4 lg:p-6">
                <!-- Close Button -->
                <button onclick="closeChartDetailsPanel()" class="absolute top-3 right-3 z-10 rounded-full border border-gray-200 bg-white p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-50 lg:top-4 lg:right-4" aria-label="Close chart details panel">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>

                <!-- Panel Header -->
                <div class="mb-4 lg:mb-6 pr-10">
                    <div class="flex items-start gap-3 mb-2">
                        <div class="admin-kpi-icon">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                        </div>
                        <div>
                            <h2 id="panel-title" class="text-xl lg:text-2xl font-bold text-gray-800">Production Details</h2>
                            <p id="panel-subtitle" class="text-sm text-gray-600">Select a point from the chart to inspect exact values.</p>
                        </div>
                    </div>
                    <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Press Esc to close</p>
                </div>

                <!-- Panel Content -->
                <div id="chart-panel-content" class="space-y-6">
                    <!-- Time Period Badge -->
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <span id="panel-period">-</span>
                        </span>
                    </div>

                    <!-- Production Value Card -->
                    <div class="bg-gradient-to-r from-green-50 to-green-100 rounded-lg p-5 border border-green-200">
                        <h3 class="text-sm font-semibold text-gray-700 uppercase mb-3">Production Summary</h3>
                        <div class="text-center py-4">
                            <p id="panel-production-value" class="text-4xl font-bold text-green-700">-</p>
                            <p class="text-sm text-gray-600 mt-1">Total Harvest</p>
                        </div>
                    </div>

                    <!-- Municipality/Crop Info -->
                    <div class="bg-white rounded-lg p-5 border border-gray-200">
                        <h3 class="text-sm font-semibold text-gray-700 uppercase mb-3">Details</h3>
                        <div id="panel-details-content" class="space-y-3">
                            <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                <span class="text-gray-600">Municipality/Crop</span>
                                <span id="panel-entity" class="font-semibold text-gray-800">-</span>
                            </div>
                            <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                <span class="text-gray-600">Time Period</span>
                                <span id="panel-time-detail" class="font-semibold text-gray-800">-</span>
                            </div>
                        </div>
                    </div>

                    <!-- All Municipalities for this Period -->
                    <div class="bg-white rounded-lg p-5 border border-gray-200">
                        <h3 class="text-sm font-semibold text-gray-700 uppercase mb-3">All Data for This Period</h3>
                        <div id="panel-all-data" class="space-y-2 max-h-[300px] overflow-y-auto">
                            <!-- Will be populated dynamically -->
                        </div>
                    </div>

                    <!-- Comparison Section -->
                    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg p-5 border border-blue-200">
                        <h3 class="text-sm font-semibold text-gray-700 uppercase mb-3">Quick Stats</h3>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="text-center">
                                <p class="text-xs text-gray-600 mb-1">Rank</p>
                                <p id="panel-rank" class="text-2xl font-bold text-blue-700">#-</p>
                            </div>
                            <div class="text-center">
                                <p class="text-xs text-gray-600 mb-1">% of Total</p>
                                <p id="panel-percentage" class="text-2xl font-bold text-indigo-700">-%</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Overlay for side panel -->
        <div id="chart-panel-overlay" onclick="closeChartDetailsPanel()" class="fixed inset-0 bg-black bg-opacity-30 z-[1999] hidden"></div>

        <!-- Key Metrics -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Total Area Planted/Harvested -->
            <div class="admin-section-card p-5 lg:p-6">
                <div class="mb-3 flex items-start justify-between gap-3">
                    <div>
                        <h3 class="text-xs font-semibold uppercase tracking-wide text-gray-600">Total Area Planted and Harvested</h3>
                        <p class="text-sm text-gray-600 mt-1">Combined planted and harvested footprint.</p>
                    </div>
                    <div class="admin-kpi-icon">
                        <svg class="w-6 h-6 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h8a2 2 0 012 2v12a1 1 0 110 2h-3a1 1 0 01-1-1v-2a1 1 0 00-1-1H9a1 1 0 00-1 1v2a1 1 0 01-1 1H4a1 1 0 110-2V4zm3 1h2v2H7V5zm2 4H7v2h2V9zm2-4h2v2h-2V5zm2 4h-2v2h2V9z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                </div>
                <div class="admin-kpi-value">{{ number_format($totalAreaHarvested ?? 0, 0) }} <span class="text-xl font-semibold text-gray-500">ha</span></div>
                <div class="admin-kpi-meta mt-2 flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                    </svg>
                    <span>Updated {{ $lastUpdate->format('F Y') ?? 'July 2025' }}</span>
                </div>
            </div>

            <!-- Average Yield -->
            <div class="admin-section-card p-5 lg:p-6">
                <div class="mb-3 flex items-start justify-between gap-3">
                    <div>
                        <h3 class="text-xs font-semibold uppercase tracking-wide text-gray-600">Average Yield</h3>
                        <p class="text-sm text-gray-600 mt-1">Average output per hectare across selected records.</p>
                    </div>
                    <div class="admin-kpi-icon">
                        <svg class="w-6 h-6 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z"/>
                        </svg>
                    </div>
                </div>
                <div class="admin-kpi-value">{{ number_format($averageYield ?? 0, 2) }} <span class="text-xl font-semibold text-gray-500">mt/ha</span></div>
                <div class="admin-kpi-meta mt-2 flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                    </svg>
                    <span>Updated {{ $lastUpdate->format('F Y') ?? 'July 2025' }}</span>
                </div>
                <div class="mt-4 flex gap-2">
                    <a href="{{ route('admin.crop-data.index') }}" class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors text-center">
                        View All Data
                    </a>
                </div>
            </div>
        </div>

        <!-- Summary and Announcements -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Performance Snapshot -->
            <div class="admin-section-card p-5 lg:p-6">
                <div class="admin-section-header mb-4">
                    <div>
                        <h2 class="admin-section-title">Performance Snapshot</h2>
                        <p class="admin-section-subtitle">Current leaders based on the active filters.</p>
                    </div>
                </div>

                <div class="space-y-4">
                    <div class="rounded-lg border border-gray-200 bg-gray-50 p-4">
                        <h3 class="text-sm font-semibold text-gray-800 mb-3">Top 3 Crops</h3>
                        @if(isset($topCrops) && $topCrops->count() > 0)
                            @php
                                $topCropEntries = $topCrops->take(3);
                                $topCropMax = max($topCropEntries->max('total_production'), 1);
                            @endphp
                            <ul class="space-y-2.5">
                                @foreach($topCropEntries as $index => $crop)
                                    <li class="rounded-lg border border-gray-100 bg-white p-2.5">
                                        <div class="flex items-center justify-between gap-2">
                                            <div class="flex items-center gap-2 min-w-0">
                                                <span class="text-xs font-bold text-green-700">{{ $index + 1 }}</span>
                                                <span class="text-sm font-medium text-gray-700 truncate">{{ ucwords(strtolower($crop->crop)) }}</span>
                                            </div>
                                            <span class="text-sm font-semibold text-gray-800 whitespace-nowrap">{{ number_format($crop->total_production, 2) }} mt</span>
                                        </div>
                                        <div class="mt-2 h-1.5 w-full rounded-full bg-gray-200">
                                            <div class="h-1.5 rounded-full bg-green-600" style="width: {{ max(($crop->total_production / $topCropMax) * 100, 4) }}%"></div>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p class="text-sm text-gray-500 italic">No crop data available for selected filters.</p>
                        @endif
                        <p class="admin-kpi-meta mt-3">Updated {{ $lastUpdate->format('M d, Y') }}</p>
                    </div>

                    <div class="rounded-lg border border-gray-200 bg-white p-4">
                        <h3 class="text-sm font-semibold text-gray-800 mb-2">Most Productive Municipality</h3>
                        <p class="text-sm text-gray-600">
                            @if(isset($topMunicipality))
                                <span class="font-semibold text-green-700">{{ ucwords(strtolower($topMunicipality->municipality)) }}</span>
                                <span class="text-gray-500"> - {{ number_format($topMunicipality->total_production, 2) }} mt total</span>
                            @else
                                <span class="text-gray-500 italic">No data available for selected filters.</span>
                            @endif
                        </p>
                        <p class="admin-kpi-meta mt-2">Updated {{ $lastUpdate->format('M d, Y') }}</p>
                    </div>
                </div>
            </div>

        <!-- Announcements Management Widget -->
        <div class="admin-section-card overflow-hidden">
            <div class="bg-gradient-to-r from-green-700 to-emerald-700 px-6 py-4">
                <div class="flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 3a1 1 0 00-1.447-.894L8.763 6H5a3 3 0 000 6h.28l1.771 5.316A1 1 0 008 18h1a1 1 0 001-1v-4.382l6.553 3.276A1 1 0 0018 15V3z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="text-white">
                            <h2 class="text-lg font-bold">Announcements</h2>
                            <p class="text-green-100 text-sm">Manage farmer notifications</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="admin-chip bg-white/20 text-white">
                            {{ $activeAnnouncementsCount ?? 0 }} Active
                        </span>
                        <a href="{{ route('admin.announcements.create') }}" class="px-4 py-2 bg-white text-green-700 font-semibold rounded-lg hover:bg-green-50 transition-colors text-sm">
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
        </div>

        <!-- ML Predictions Section -->
        @if(isset($predictions) && $predictions['available'])
            <div id="predictions-section" class="admin-section-card p-5 lg:p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 bg-green-600 rounded-lg flex items-center justify-center">
                            <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                            </svg>
                        </div>
                        <div>
                            <h2 class="admin-section-title">AI Production Predictions</h2>
                            <p class="admin-section-subtitle">Machine learning forecasts based on historical production patterns.</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="admin-chip bg-green-600 text-white">
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
                        <details class="mb-4 overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm" {{ $loop->first ? 'open' : '' }}>
                            <summary class="cursor-pointer list-none bg-gray-50 px-4 py-3">
                                <div class="flex items-center justify-between gap-3">
                                    <h3 class="text-base font-semibold text-gray-800">Predictions for {{ $year }}</h3>
                                    <span class="admin-chip bg-green-100 text-green-700">
                                        {{ collect($municipalityGroups)->flatten(1)->count() }} records
                                    </span>
                                </div>
                            </summary>

                            <div class="space-y-3 border-t border-gray-200 p-4">
                                @foreach($municipalityGroups as $municipality => $municipalityPredictions)
                                    @php
                                        $cropGroups = collect($municipalityPredictions)->groupBy('crop_type');
                                        $totalProduction = collect($municipalityPredictions)->sum('predicted_production');
                                    @endphp

                                    <details class="rounded-lg border border-gray-200 bg-white" {{ $loop->first ? 'open' : '' }}>
                                        <summary class="cursor-pointer list-none px-4 py-3">
                                            <div class="flex items-center justify-between gap-3">
                                                <div class="flex items-center gap-2">
                                                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                                    </svg>
                                                    <span class="font-semibold text-gray-800">{{ ucwords(strtolower($municipality)) }}</span>
                                                </div>
                                                <span class="text-sm font-bold text-green-700">{{ number_format($totalProduction, 2) }} mt</span>
                                            </div>
                                        </summary>

                                        <div class="space-y-3 border-t border-gray-100 p-4">
                                            @foreach($cropGroups as $crop => $cropPredictions)
                                                @php
                                                    $cropTotal = collect($cropPredictions)->sum('predicted_production');
                                                    $hasMonthly = count($cropPredictions) > 1;
                                                @endphp

                                                <div class="rounded-lg border border-gray-200 bg-gray-50 p-3">
                                                    <div class="mb-2 flex items-center justify-between gap-2">
                                                        <span class="text-sm font-semibold text-gray-800">{{ ucwords(strtolower($crop)) }}</span>
                                                        <span class="text-sm font-bold text-green-700">{{ number_format($cropTotal, 2) }} mt</span>
                                                    </div>

                                                    @if($hasMonthly)
                                                        <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-2">
                                                            @foreach($cropPredictions as $pred)
                                                                <div class="rounded-md border border-gray-200 bg-white px-2.5 py-2 text-xs">
                                                                    <div class="font-medium text-gray-600">{{ $pred['month'] }}</div>
                                                                    <div class="font-bold text-gray-800">{{ number_format($pred['predicted_production'], 1) }} mt</div>
                                                                    @if(isset($pred['confidence']))
                                                                        <div class="text-[10px] text-gray-500">{{ $pred['confidence'] }}</div>
                                                                    @endif
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    @else
                                                        @foreach($cropPredictions as $pred)
                                                            <div class="flex flex-wrap items-center gap-2 text-xs text-gray-600">
                                                                <span class="font-medium text-gray-700">{{ $pred['month'] }}</span>
                                                                <span>•</span>
                                                                <span>{{ ucwords(strtolower($pred['farm_type'])) }}</span>
                                                                <span>•</span>
                                                                <span>{{ number_format($pred['area_harvested']) }} ha</span>
                                                                @if(isset($pred['confidence']))
                                                                    <span>•</span>
                                                                    <span class="text-green-700">{{ $pred['confidence'] }}</span>
                                                                @endif
                                                            </div>
                                                        @endforeach
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    </details>
                                @endforeach
                            </div>
                        </details>
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
                                    class="px-8 py-2.5 bg-green-700 hover:bg-green-800 text-white font-semibold rounded-md transition-colors shadow-sm">
                                Submit
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        // Global variable to store chart instance
        let trendChartInstance = null;
        // Store chart data globally for panel access
        let globalChartData = null;
        
        // Chart Details Panel Functions
        function openChartDetailsPanel() {
            document.getElementById('chart-details-panel').classList.remove('translate-x-full');
            document.getElementById('chart-panel-overlay').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }
        
        function closeChartDetailsPanel() {
            document.getElementById('chart-details-panel').classList.add('translate-x-full');
            document.getElementById('chart-panel-overlay').classList.add('hidden');
            document.body.style.overflow = '';
        }
        
        // Format number in a friendly way
        function formatProductionValue(value) {
            if (value >= 1000000) {
                return (value / 1000000).toFixed(2) + ' Million mt';
            } else if (value >= 1000) {
                return (value / 1000).toFixed(1) + ' Thousand mt';
            } else {
                return value.toLocaleString(undefined, {
                    minimumFractionDigits: 0,
                    maximumFractionDigits: 2
                }) + ' mt';
            }
        }
        
        // Handle chart click to show side panel
        function handleChartClick(event, elements, chart) {
            if (!elements || elements.length === 0) return;
            
            const element = elements[0];
            const datasetIndex = element.datasetIndex;
            const dataIndex = element.index;
            
            const dataset = chart.data.datasets[datasetIndex];
            const label = chart.data.labels[dataIndex];
            const value = dataset.data[dataIndex];
            const entityName = dataset.label;
            
            // Update panel content
            document.getElementById('panel-title').textContent = entityName;
            document.getElementById('panel-subtitle').textContent = 'Production Details';
            document.getElementById('panel-period').textContent = label;
            document.getElementById('panel-production-value').textContent = formatProductionValue(value);
            document.getElementById('panel-entity').textContent = entityName;
            document.getElementById('panel-time-detail').textContent = label;
            
            // Calculate all data for this time period and populate the panel
            let allDataHtml = '';
            let totalForPeriod = 0;
            let dataForPeriod = [];
            
            chart.data.datasets.forEach((ds, idx) => {
                const dsValue = ds.data[dataIndex] || 0;
                totalForPeriod += dsValue;
                dataForPeriod.push({ name: ds.label, value: dsValue, color: ds.borderColor });
            });
            
            // Sort by value descending
            dataForPeriod.sort((a, b) => b.value - a.value);
            
            // Find rank of clicked item
            const rank = dataForPeriod.findIndex(d => d.name === entityName) + 1;
            const percentage = totalForPeriod > 0 ? ((value / totalForPeriod) * 100).toFixed(1) : 0;
            
            document.getElementById('panel-rank').textContent = '#' + rank;
            document.getElementById('panel-percentage').textContent = percentage + '%';
            
            // Build all data list
            dataForPeriod.forEach((item, idx) => {
                const isSelected = item.name === entityName;
                const bgClass = isSelected ? 'bg-green-50 border-green-200' : 'bg-gray-50 border-gray-100';
                const textClass = isSelected ? 'text-green-700 font-bold' : 'text-gray-700';
                
                allDataHtml += `
                    <div class="flex justify-between items-center p-2 rounded-lg border ${bgClass}">
                        <div class="flex items-center gap-2">
                            <div class="w-3 h-3 rounded-full" style="background-color: ${item.color}"></div>
                            <span class="text-sm ${textClass}">${item.name}</span>
                        </div>
                        <span class="text-sm font-semibold ${textClass}">${formatProductionValue(item.value)}</span>
                    </div>
                `;
            });
            
            document.getElementById('panel-all-data').innerHTML = allDataHtml;
            
            // Open the panel
            openChartDetailsPanel();
        }
        
        // Close panel on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeChartDetailsPanel();
            }
        });
        
        function dataAnalytics() {
            return {
                init() {
                    if (typeof Chart === 'undefined') {
                        setTimeout(() => this.init(), 100);
                        return;
                    }

                    this.initTrendChart();
                },

                initTrendChart() {
                    const ctx = document.getElementById('trendChart');
                    if (!ctx) {
                        return;
                    }

                    const chartData = @json($trendChartData);
                    
                    if (!chartData || !chartData.labels || !chartData.datasets) {
                        console.error('Invalid chart data structure');
                        return;
                    }

                    if (chartData.labels.length === 0 || chartData.datasets.length === 0) {
                        return;
                    }

                    const hasYearLabels = chartData.labels.every((label) => /^\d{4}$/.test(String(label)));
                    
                    // Modern chart styling with enhanced visual effects
                    const isSingleDataset = chartData.datasets.length === 1;
                    chartData.datasets = chartData.datasets.map((dataset, index) => {
                        // Extract RGB values for gradient
                        const colorMatch = dataset.borderColor ? dataset.borderColor.match(/rgb\((\d+),\s*(\d+),\s*(\d+)\)/) : null;
                        const r = colorMatch ? colorMatch[1] : '16';
                        const g = colorMatch ? colorMatch[2] : '185';
                        const b = colorMatch ? colorMatch[3] : '129';
                        
                        return {
                            ...dataset,
                            borderWidth: 3,
                            pointRadius: 6,
                            pointHoverRadius: 12,
                            pointHitRadius: 20,
                            pointBackgroundColor: dataset.borderColor || 'rgb(' + r + ', ' + g + ', ' + b + ')',
                            pointBorderColor: '#ffffff',
                            pointBorderWidth: 2,
                            pointHoverBackgroundColor: '#ffffff',
                            pointHoverBorderColor: dataset.borderColor || 'rgb(' + r + ', ' + g + ', ' + b + ')',
                            pointHoverBorderWidth: 3,
                            fill: isSingleDataset ? 'origin' : false,
                            backgroundColor: isSingleDataset 
                                ? 'rgba(' + r + ', ' + g + ', ' + b + ', 0.12)' 
                                : 'transparent',
                            tension: 0.35,
                            borderCapStyle: 'round',
                            borderJoinStyle: 'round'
                        };
                    });
                    
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
                            plugins: {
                                legend: {
                                    display: true,
                                    position: 'bottom',
                                    labels: {
                                        padding: 16,
                                        font: {
                                            size: 13,
                                            weight: '600',
                                            family: "'Inter', 'Segoe UI', sans-serif"
                                        },
                                        usePointStyle: true,
                                        pointStyle: 'rectRounded',
                                        boxWidth: 12,
                                        boxHeight: 12
                                    }
                                },
                                tooltip: {
                                    mode: 'nearest',
                                    intersect: true,
                                    backgroundColor: 'rgba(15, 23, 42, 0.96)',
                                    titleColor: '#f8fafc',
                                    bodyColor: '#e2e8f0',
                                    borderColor: 'rgba(99, 102, 241, 0.4)',
                                    borderWidth: 1,
                                    cornerRadius: 10,
                                    padding: { top: 12, bottom: 12, left: 14, right: 14 },
                                    titleFont: {
                                        size: 13,
                                        weight: '700',
                                        family: "'Inter', 'Segoe UI', sans-serif"
                                    },
                                    bodyFont: {
                                        size: 22,
                                        weight: '700',
                                        family: "'Inter', 'Segoe UI', sans-serif"
                                    },
                                    footerFont: {
                                        size: 11,
                                        weight: '400',
                                        family: "'Inter', 'Segoe UI', sans-serif"
                                    },
                                    footerColor: 'rgba(148, 163, 184, 0.9)',
                                    bodySpacing: 4,
                                    footerMarginTop: 8,
                                    displayColors: false,
                                    caretSize: 6,
                                    caretPadding: 8,
                                    callbacks: {
                                        title: function(context) {
                                            const item = context[0];
                                            const name = item.dataset.label || '';
                                            return name + '  ·  ' + item.label;
                                        },
                                        label: function(context) {
                                            const value = context.parsed.y;
                                            if (value >= 1000000) {
                                                return (value / 1000000).toFixed(2) + 'M mt';
                                            } else if (value >= 1000) {
                                                return Number(value.toFixed(0)).toLocaleString() + ' mt';
                                            }
                                            return value.toFixed(2) + ' mt';
                                        },
                                        footer: function(context) {
                                            const item = context[0];
                                            const labelIndex = item.dataIndex;
                                            const chart = item.chart;
                                            
                                            // Gather all datasets' values at this label index
                                            const entries = [];
                                            chart.data.datasets.forEach((ds, i) => {
                                                const val = ds.data[labelIndex];
                                                if (val > 0 && !ds.hidden) {
                                                    entries.push({ name: ds.label, value: val });
                                                }
                                            });
                                            
                                            if (entries.length <= 1) return '';
                                            
                                            // Sort descending and take top 3
                                            entries.sort((a, b) => b.value - a.value);
                                            const rank = entries.findIndex(e => e.name === item.dataset.label) + 1;
                                            const top3 = entries.slice(0, 3);
                                            
                                            const lines = ['─────────────────'];
                                            lines.push('Rank #' + rank + ' of ' + entries.length);
                                            top3.forEach((e, i) => {
                                                const marker = e.name === item.dataset.label ? ' ◀' : '';
                                                const fmtVal = e.value >= 1000 
                                                    ? Number(e.value.toFixed(0)).toLocaleString() 
                                                    : e.value.toFixed(1);
                                                lines.push((i + 1) + '. ' + e.name + ': ' + fmtVal + ' mt' + marker);
                                            });
                                            if (entries.length > 3) {
                                                lines.push('   +' + (entries.length - 3) + ' more');
                                            }
                                            
                                            return lines;
                                        }
                                    }
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    title: {
                                        display: true,
                                        text: 'Total Harvest (Metric Tons)',
                                        font: {
                                            size: 13,
                                            weight: '600',
                                            family: "'Inter', 'Segoe UI', sans-serif"
                                        },
                                        color: '#374151',
                                        padding: { top: 0, bottom: 10 }
                                    },
                                    ticks: {
                                        font: {
                                            size: 12,
                                            family: "'Inter', 'Segoe UI', sans-serif"
                                        },
                                        color: '#6B7280',
                                        callback: function(value) {
                                            // User-friendly number formatting
                                            if (value >= 1000000) {
                                                return (value / 1000000).toFixed(1) + ' Million';
                                            } else if (value >= 1000) {
                                                return (value / 1000).toFixed(0) + 'K';
                                            }
                                            return value.toLocaleString();
                                        },
                                        precision: 0,
                                        maxTicksLimit: 6  // Less ticks for cleaner look
                                    },
                                    grid: {
                                        color: 'rgba(229, 231, 235, 0.5)',
                                        lineWidth: 1,
                                        drawBorder: false
                                    },
                                    border: {
                                        display: false
                                    }
                                },
                                x: {
                                    title: {
                                        display: true,
                                        text: 'Time Period',
                                        font: {
                                            size: 13,
                                            weight: '600',
                                            family: "'Inter', 'Segoe UI', sans-serif"
                                        },
                                        color: '#374151',
                                        padding: { top: 10, bottom: 0 }
                                    },
                                    ticks: {
                                        font: {
                                            size: 12,
                                            weight: '500',
                                            family: "'Inter', 'Segoe UI', sans-serif"
                                        },
                                        color: '#374151',
                                        autoSkip: !hasYearLabels,
                                        maxRotation: 0,
                                        minRotation: 0
                                    },
                                    grid: {
                                        display: false
                                    },
                                    border: {
                                        display: false
                                    },
                                    grid: {
                                        display: false
                                    }
                                }
                            },
                            interaction: {
                                mode: 'point',
                                intersect: true
                            },
                            onHover: function(event, elements) {
                                const nativeEvent = event?.native;
                                if (nativeEvent?.target) {
                                    nativeEvent.target.style.cursor = elements && elements.length ? 'pointer' : 'default';
                                }
                            },
                            onClick: function(event, elements, chart) {
                                handleChartClick(event, elements, chart);
                            }
                        }
                    });
                    
                    // Store chart data globally
                    globalChartData = chartData;
                }
            }
        }
        
        // Initialize chart when DOM is ready (fallback for timing issues)
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                if (typeof dataAnalytics === 'function' && !trendChartInstance) {
                    const analytics = dataAnalytics();
                    if (analytics && typeof analytics.init === 'function') {
                        analytics.init();
                    }
                }
            }, 200);
        });
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
                                    SUM(production) as total_production,
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
                                <div class="bg-gradient-to-br from-green-50 to-blue-50 rounded-lg p-5 border border-green-200">
                                    <h4 class="text-sm font-semibold text-gray-700 mb-4">OVERVIEW</h4>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div class="hover-lift">
                                            <p class="text-xs text-gray-600 mb-1">Total Production</p>
                                            <p class="text-xl font-bold text-green-700">
                                                @foreach($municipalityStats as $munName => $stat)
                                                    <span x-show="selectedMunicipality === '{{ $munName }}'">
                                                        {{ number_format($stat->total_production ?? 0, 2) }} mt
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
                                                        {{ number_format($stat->productivity ?? 0, 2) }} mt/ha
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
                                        
                                        $irrigated = $irrigatedQuery->sum('production'); // Production is already in mt
                                        $rainfed = $rainfedQuery->sum('production'); // Production is already in mt
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
                                                <span class="text-xs text-gray-500 mt-1 block">{{ number_format($distribution['irrigated_mt'], 2) }} mt</span>
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
                                                <span class="text-xs text-gray-500 mt-1 block">{{ number_format($distribution['rainfed_mt'], 2) }} mt</span>
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
                                                ->select('year', \DB::raw('SUM(production) as production'))
                                                ->groupBy('year')
                                                ->orderBy('year', 'desc')
                                                ->limit(10)
                                                ->get();
                                            $yearlyData[$mun] = $years;
                                            
                                            // Get top 10 crops for this municipality (all years)
                                            $topCrops = \App\Models\Crop::where('municipality', $mun)
                                                ->select('crop', \DB::raw('SUM(production) as production'))
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
                                            
                                            $months = $monthsQuery->select('month', \DB::raw('SUM(production) as production'))
                                                ->groupBy('month')
                                                ->orderByRaw("CASE month WHEN 'JAN' THEN 1 WHEN 'FEB' THEN 2 WHEN 'MAR' THEN 3 WHEN 'APR' THEN 4 WHEN 'MAY' THEN 5 WHEN 'JUN' THEN 6 WHEN 'JUL' THEN 7 WHEN 'AUG' THEN 8 WHEN 'SEP' THEN 9 WHEN 'OCT' THEN 10 WHEN 'NOV' THEN 11 WHEN 'DEC' THEN 12 ELSE 13 END")
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
                                                                {{ number_format($yearData->production, 2) }} mt
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
                                                                    {{ number_format($monthData->production, 2) }} mt
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
                                                                {{ number_format($cropData->production, 2) }} mt
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
                                                        data-production="{{ json_encode($crops->pluck('total_production')->map(function($val) { return round($val, 2); })->toArray()) }}">
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
                                                        <span class="text-xs text-gray-500 block">{{ number_format($crop->total_production, 2) }} mt</span>
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

                // Modern vibrant color palette matching the main chart
                const colors = [
                    'rgb(16, 185, 129)',   // Emerald
                    'rgb(59, 130, 246)',   // Blue
                    'rgb(245, 158, 11)',   // Amber
                    'rgb(239, 68, 68)',    // Red
                    'rgb(139, 92, 246)',   // Violet
                    'rgb(236, 72, 153)',   // Pink
                    'rgb(6, 182, 212)',    // Cyan
                    'rgb(249, 115, 22)',   // Orange
                    'rgb(34, 197, 94)',    // Green
                    'rgb(99, 102, 241)'    // Indigo
                ];

                new Chart(canvas, {
                    type: 'doughnut',
                    data: {
                        labels: crops.map(crop => crop.charAt(0) + crop.slice(1).toLowerCase()),
                        datasets: [{
                            data: production,
                            backgroundColor: colors.slice(0, crops.length),
                            borderColor: '#ffffff',
                            borderWidth: 3,
                            hoverBorderWidth: 4,
                            hoverOffset: 8
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        cutout: '60%',
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                backgroundColor: 'rgba(17, 24, 39, 0.95)',
                                titleColor: '#fff',
                                bodyColor: 'rgba(255, 255, 255, 0.9)',
                                borderColor: 'rgba(255, 255, 255, 0.1)',
                                borderWidth: 1,
                                cornerRadius: 10,
                                padding: 12,
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