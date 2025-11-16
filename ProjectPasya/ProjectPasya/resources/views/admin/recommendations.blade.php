<x-admin-layout>
    <x-slot name="title">Recommendations</x-slot>

    @push('styles')
    <style>
        [x-cloak] { display: none !important; }
    </style>
    @endpush

    <div class="space-y-6" x-data="{ showSubsidyModal: {{ $errors->any() ? 'true' : 'false' }} }">
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
            <h2 class="text-xl font-bold text-gray-800 mb-6">Climate Resilience</h2>
            
            <!-- Municipality Weather Cards -->
            <div class="bg-white rounded-xl border border-gray-200 p-6 mb-6">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    
                    @foreach($municipalityWeather as $weather)
                    <!-- {{ $weather['municipality'] }} -->
                    <div class="border border-gray-200 rounded-lg p-4">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <div class="text-sm font-semibold text-gray-800">BENGUET</div>
                                <div class="text-xs text-gray-600">{{ $weather['municipality'] }}</div>
                            </div>
                        </div>
                        
                        <!-- 4-Day Forecast -->
                        <div class="grid grid-cols-4 gap-2">
                            @foreach($weather['forecast'] as $day)
                            <div class="text-center">
                                <div class="text-xs text-gray-600 mb-1">{{ $day['day'] }}</div>
                                <div class="text-xs text-gray-500 mb-2">{{ $day['date'] }}</div>
                                <div class="text-3xl mb-2">{{ $day['icon'] }}</div>
                                <div class="text-xs font-semibold text-gray-800">{{ $day['condition'] }}</div>
                                <div class="text-xs font-medium text-gray-700 mt-1">{{ $day['temp'] }}</div>
                                <div class="text-xs text-gray-500">AQI {{ $day['aqi'] }}</div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endforeach

                </div>
            </div>

            <!-- Hourly Forecast & Recommendations -->
            <div class="bg-gradient-to-r from-gray-400 to-gray-300 rounded-xl p-6 mb-6">
                <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
                    
                    <!-- Hourly Weather -->
                    <div class="lg:col-span-2">
                        <div class="grid grid-cols-6 gap-3 mb-4">
                            @foreach($hourlyForecast as $hour)
                            <div class="text-center text-white">
                                <div class="text-sm font-medium mb-2">{{ $hour['time'] }}</div>
                                <div class="text-3xl mb-2">{{ $hour['icon'] }}</div>
                                <div class="text-lg font-semibold">{{ $hour['temp'] }}</div>
                            </div>
                            @endforeach
                        </div>
                        
                        <!-- Municipality Search -->
                        <input type="text" placeholder="Check municipality weather per hour" 
                               class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-yellow-500">
                    </div>

                    <!-- Recommendations Panel -->
                    <div class="lg:col-span-2 bg-white rounded-lg p-4">
                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div>
                                <div class="text-sm font-semibold text-gray-700 mb-1">Optimal Planting Window</div>
                                <div class="text-xs text-gray-600">{{ $optimalWindow }}</div>
                            </div>
                            <div>
                                <div class="text-sm font-semibold text-gray-700 mb-1">Best Crops</div>
                                <div class="text-xs text-gray-600">{{ $bestCrops }}</div>
                            </div>
                        </div>
                        <div class="mb-4">
                            <div class="text-sm font-semibold text-gray-700 mb-1">Climate Risk</div>
                            <div class="text-2xl font-bold text-gray-800">{{ $climateRisk }}%</div>
                        </div>
                        <button class="w-full px-4 py-2 bg-yellow-400 hover:bg-yellow-500 text-gray-800 font-semibold rounded-lg transition-colors">
                            Recommend Now
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
                <button type="button" @click="showSubsidyModal = true" class="px-4 py-2 bg-yellow-400 hover:bg-yellow-500 text-gray-800 font-semibold rounded-lg transition-colors flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Allocate Subsidy
                </button>
            </div>

            <!-- Filters and Actions Row -->
            <form method="GET" action="{{ route('admin.recommendations') }}" class="mb-6">
                <div class="flex items-center gap-3">
                    <!-- Crop Filter -->
                    <div class="flex-1">
                        <input type="text" name="crop" placeholder="Crop" value="{{ $filterCrop }}" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
                    </div>

                    <!-- Status Filter -->
                    <div class="flex-1">
                        <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
                            <option value="">Status</option>
                            <option value="Approved" {{ $filterStatus == 'Approved' ? 'selected' : '' }}>Approved</option>
                            <option value="Pending" {{ $filterStatus == 'Pending' ? 'selected' : '' }}>Pending</option>
                            <option value="Rejected" {{ $filterStatus == 'Rejected' ? 'selected' : '' }}>Rejected</option>
                        </select>
                    </div>

                    <!-- Reset Button -->
                    <div>
                        <a href="{{ route('admin.recommendations') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md hover:bg-gray-50 text-gray-700 font-medium transition-colors">
                            Reset
                            <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </a>
                    </div>

                    <!-- View Button -->
                    <div class="text-sm text-gray-600">
                        <button type="button" class="px-3 py-2 text-gray-500 hover:text-gray-700">
                            View
                        </button>
                    </div>

                    <!-- Allocate Subsidy Button (duplicate for design) -->
                    <div>
                        <button type="button" @click="showSubsidyModal = true" class="px-6 py-2 bg-yellow-400 hover:bg-yellow-500 text-gray-800 font-semibold rounded-lg transition-colors">
                            Allocate Subsidy
                        </button>
                    </div>
                </div>
            </form>

            <!-- Data Table -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left w-10">
                                <input type="checkbox" class="rounded border-gray-300">
                            </th>
                            <th class="px-6 py-3 text-left">
                                <button class="flex items-center gap-1 text-xs font-medium text-gray-500 uppercase tracking-wider hover:text-gray-700">
                                    Full Name
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"/>
                                    </svg>
                                </button>
                            </th>
                            <th class="px-6 py-3 text-left">
                                <button class="flex items-center gap-1 text-xs font-medium text-gray-500 uppercase tracking-wider hover:text-gray-700">
                                    Crop
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"/>
                                    </svg>
                                </button>
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subsidy Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subsidy Amt</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Updated</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
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
                                    <div class="text-sm text-gray-700">{{ $subsidy->crop }}</div>
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
                                    <div class="text-sm text-gray-700">
                                        @if($subsidy->subsidy_amount)
                                            ₱{{ number_format($subsidy->subsidy_amount, 0) }}
                                        @else
                                            <span class="text-gray-400">N/A</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-700">{{ $subsidy->updated_at->format('Y-m-d') }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <button class="text-gray-400 hover:text-gray-600">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/>
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
                <div class="text-sm text-gray-500">
                    @if($subsidies->total() > 0)
                        0 of {{ $subsidies->total() }} row(s) selected.
                    @else
                        0 of 0 row(s) selected.
                    @endif
                </div>
                
                <div class="flex items-center gap-6">
                    <div class="flex items-center gap-2">
                        <span class="text-sm text-gray-600">Rows per page</span>
                        <select class="px-2 py-1 border border-gray-300 rounded text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
                            <option>10</option>
                            <option>25</option>
                            <option>50</option>
                            <option>100</option>
                        </select>
                    </div>

                    <div class="text-sm text-gray-600">
                        Page {{ $subsidies->currentPage() }} of {{ $subsidies->lastPage() }}
                    </div>

                    <div class="flex items-center gap-1">
                        <a href="{{ $subsidies->url(1) }}" class="p-1 rounded hover:bg-gray-100 {{ $subsidies->onFirstPage() ? 'text-gray-300 cursor-not-allowed' : 'text-gray-600' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/>
                            </svg>
                        </a>
                        <a href="{{ $subsidies->previousPageUrl() }}" class="p-1 rounded hover:bg-gray-100 {{ $subsidies->onFirstPage() ? 'text-gray-300 cursor-not-allowed' : 'text-gray-600' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                            </svg>
                        </a>
                        <a href="{{ $subsidies->nextPageUrl() }}" class="p-1 rounded hover:bg-gray-100 {{ !$subsidies->hasMorePages() ? 'text-gray-300 cursor-not-allowed' : 'text-gray-600' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                        <a href="{{ $subsidies->url($subsidies->lastPage()) }}" class="p-1 rounded hover:bg-gray-100 {{ !$subsidies->hasMorePages() ? 'text-gray-300 cursor-not-allowed' : 'text-gray-600' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"/>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>            <!-- Footer Info -->
            <div class="mt-6 flex items-center justify-between text-sm text-gray-600">
                <div>0 of 50 row(s) selected</div>
                <div class="flex items-center gap-4">
                    <div class="flex items-center gap-2">
                        <span>Rows per page:</span>
                        <select class="border border-gray-300 rounded px-2 py-1 text-sm">
                            <option>10</option>
                            <option>25</option>
                            <option>50</option>
                        </select>
                    </div>
                    <span>Page 1 of 5</span>
                    <div class="flex items-center gap-1">
                        <button class="px-2 py-1 border border-gray-300 rounded hover:bg-gray-50 disabled:opacity-50" disabled>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/>
                            </svg>
                        </button>
                        <button class="px-2 py-1 border border-gray-300 rounded hover:bg-gray-50 disabled:opacity-50" disabled>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                            </svg>
                        </button>
                        <button class="px-2 py-1 border border-gray-300 rounded hover:bg-gray-50">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </button>
                        <button class="px-2 py-1 border border-gray-300 rounded hover:bg-gray-50">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Allocate Subsidy Modal -->
        <div x-show="showSubsidyModal" 
             x-cloak
             @keydown.escape.window="showSubsidyModal = false"
             class="fixed inset-0 z-50 overflow-y-auto"
             style="display: none;">
            
            <!-- Backdrop -->
            <div x-show="showSubsidyModal" 
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
                 @click="showSubsidyModal = false"></div>

            <!-- Modal Content -->
            <div class="flex min-h-full items-center justify-center p-4">
                <div x-show="showSubsidyModal"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     class="relative bg-white rounded-lg shadow-xl max-w-2xl w-full p-8">
                    
                    <!-- Modal Header -->
                    <div class="mb-6">
                        <h3 class="text-xl font-bold text-gray-800">(Form) Allocate Subsidy</h3>
                    </div>

                    <!-- Subsidy Form -->
                    <form method="POST" action="{{ route('admin.subsidies.store') }}" class="space-y-6">
                        @csrf
                        
                        <div>
                            <h4 class="text-base font-semibold text-gray-800 mb-4">Allocate Subsidy</h4>
                            <p class="text-sm text-gray-600 mb-4">All required fields are marked with *</p>
                        </div>

                        <!-- Form Grid -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Full Name -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Full Name<span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="full_name" required placeholder="Enter full name" value="{{ old('full_name') }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                            </div>

                            <!-- Farmer ID -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Farmer ID<span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="farmer_id" required placeholder="e.g., ID-COOP-0001" value="{{ old('farmer_id') }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                            </div>

                            <!-- Municipality -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Municipality<span class="text-red-500">*</span>
                                </label>
                                <select name="municipality" required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                                    <option value="">Select municipality</option>
                                    @foreach($municipalities as $municipality)
                                        <option value="{{ $municipality }}" {{ old('municipality') == $municipality ? 'selected' : '' }}>
                                            {{ $municipality }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Crop -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Crop<span class="text-red-500">*</span>
                                </label>
                                <select name="crop" required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                                    <option value="">Select crop</option>
                                    @foreach($crops as $crop)
                                        <option value="{{ $crop }}" {{ old('crop') == $crop ? 'selected' : '' }}>
                                            {{ $crop }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Subsidy Status -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Subsidy Status
                                </label>
                                <select name="subsidy_status"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                                    <option value="Pending" {{ old('subsidy_status', 'Pending') == 'Pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="Approved" {{ old('subsidy_status') == 'Approved' ? 'selected' : '' }}>Approved</option>
                                    <option value="Rejected" {{ old('subsidy_status') == 'Rejected' ? 'selected' : '' }}>Rejected</option>
                                </select>
                            </div>

                            <!-- Subsidy Amount -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Subsidy Amount (₱)
                                </label>
                                <input type="number" name="subsidy_amount" placeholder="Enter amount" step="0.01" min="0" value="{{ old('subsidy_amount') }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                            </div>

                            <!-- Farm Type -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Farm Type
                                </label>
                                <select name="farm_type"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                                    <option value="">Select farm type</option>
                                    <option value="Rainfed" {{ old('farm_type') == 'Rainfed' ? 'selected' : '' }}>Rainfed</option>
                                    <option value="Irrigated" {{ old('farm_type') == 'Irrigated' ? 'selected' : '' }}>Irrigated</option>
                                    <option value="Upland" {{ old('farm_type') == 'Upland' ? 'selected' : '' }}>Upland</option>
                                    <option value="Lowland" {{ old('farm_type') == 'Lowland' ? 'selected' : '' }}>Lowland</option>
                                </select>
                            </div>

                            <!-- Year -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Year
                                </label>
                                <input type="number" name="year" placeholder="e.g., {{ date('Y') }}" min="2000" max="{{ date('Y') + 5 }}" value="{{ old('year', date('Y')) }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                            </div>

                            <!-- Area Planted (ha) -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Area Planted (ha)
                                </label>
                                <input type="number" name="area_planted" placeholder="Enter area in hectares" step="0.01" min="0" value="{{ old('area_planted') }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                            </div>

                            <!-- Area Harvested (ha) -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Area Harvested (ha)
                                </label>
                                <input type="number" name="area_harvested" placeholder="Enter area in hectares" step="0.01" min="0" value="{{ old('area_harvested') }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                            </div>

                            <!-- Production (mt) -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Production (MT)
                                </label>
                                <input type="number" name="production" placeholder="Enter production in metric tons" step="0.01" min="0" value="{{ old('production') }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                            </div>

                            <!-- Productivity (kg/ha) - Auto-calculated -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Productivity (kg/ha)
                                </label>
                                <input type="number" name="productivity" placeholder="Auto-calculated" step="0.01" min="0" value="{{ old('productivity') }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                                <p class="mt-1 text-xs text-gray-500">Leave blank to auto-calculate from production/area harvested</p>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="flex justify-end gap-3 pt-4">
                            <button type="button" @click="showSubsidyModal = false" class="px-6 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold rounded-md transition-colors">
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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
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
                                beginAtZero: true,
                                title: {
                                    display: true,
                                    text: 'Seeds (kg)',
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
