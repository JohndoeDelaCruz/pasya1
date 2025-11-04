<x-admin-layout>
    <x-slot name="title">Recommendations</x-slot>

    @push('styles')
    <style>
        [x-cloak] { display: none !important; }
    </style>
    @endpush

    <div class="space-y-6" x-data="{ showSubsidyModal: false }">
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

        <!-- Page Header -->
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold text-gray-800">Dashboard</h1>
        </div>

        <!-- Recommendations Section -->
        <div>
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Recommendations</h2>
            
            <!-- Weather Cards Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                
                <!-- Climate Resilience Card -->
                <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-xl shadow-md p-6 border border-green-200">
                    <h3 class="text-lg font-bold text-gray-800 mb-3">Climate Resilience</h3>
                    
                    <!-- Today's Weather -->
                    <div class="mb-4">
                        <div class="text-sm text-gray-600 mb-2">Today</div>
                        <div class="flex items-center gap-4">
                            <div class="text-5xl">☀️</div>
                            <div>
                                <div class="text-3xl font-bold text-gray-800">31°</div>
                                <div class="text-sm text-gray-600">H: 31° L: 27°</div>
                            </div>
                        </div>
                    </div>

                    <!-- 4-Day Forecast -->
                    <div class="grid grid-cols-4 gap-2 mb-4">
                        <div class="text-center">
                            <div class="text-xs text-gray-600 mb-1">Mon 4</div>
                            <div class="text-2xl mb-1">☀️</div>
                            <div class="text-xs font-medium text-gray-700">33°</div>
                            <div class="text-xs text-gray-500">27°</div>
                        </div>
                        <div class="text-center">
                            <div class="text-xs text-gray-600 mb-1">Tue 5</div>
                            <div class="text-2xl mb-1">⛅</div>
                            <div class="text-xs font-medium text-gray-700">32°</div>
                            <div class="text-xs text-gray-500">28°</div>
                        </div>
                        <div class="text-center">
                            <div class="text-xs text-gray-600 mb-1">Wed 6</div>
                            <div class="text-2xl mb-1">🌧️</div>
                            <div class="text-xs font-medium text-gray-700">30°</div>
                            <div class="text-xs text-gray-500">26°</div>
                        </div>
                        <div class="text-center">
                            <div class="text-xs text-gray-600 mb-1">Thu 7</div>
                            <div class="text-2xl mb-1">☁️</div>
                            <div class="text-xs font-medium text-gray-700">31°</div>
                            <div class="text-xs text-gray-500">27°</div>
                        </div>
                    </div>

                    <p class="text-sm text-gray-600 mb-3">Climate resilience is very important especially in farm. These are the things you should do...</p>
                    <button class="w-full px-4 py-2 bg-yellow-400 hover:bg-yellow-500 text-gray-800 font-semibold rounded-lg transition-colors">
                        Read More
                    </button>
                </div>

                <!-- Disaster Planting Window Card -->
                <div class="bg-white rounded-xl shadow-md p-6 border border-gray-200">
                    <h3 class="text-lg font-bold text-gray-800 mb-4">Disaster Planting Window</h3>
                    
                    <!-- Weekly Weather -->
                    <div class="mb-4">
                        <div class="flex justify-between items-end mb-2">
                            <div class="text-center">
                                <div class="text-2xl mb-1">☁️</div>
                                <div class="text-xs font-medium text-gray-700">19°</div>
                            </div>
                            <div class="text-center">
                                <div class="text-2xl mb-1">☁️</div>
                                <div class="text-xs font-medium text-gray-700">19°</div>
                            </div>
                            <div class="text-center">
                                <div class="text-2xl mb-1">⛅</div>
                                <div class="text-xs font-medium text-gray-700">22°</div>
                            </div>
                            <div class="text-center">
                                <div class="text-2xl mb-1">⛅</div>
                                <div class="text-xs font-medium text-gray-700">23°</div>
                            </div>
                            <div class="text-center">
                                <div class="text-2xl mb-1">⛅</div>
                                <div class="text-xs font-medium text-gray-700">24°</div>
                            </div>
                            <div class="text-center">
                                <div class="text-2xl mb-1">⛅</div>
                                <div class="text-xs font-medium text-gray-700">24°</div>
                            </div>
                        </div>
                        <div class="text-xs text-gray-500 text-center">Climate area highly significant status for next...</div>
                    </div>

                    <p class="text-sm text-gray-600 mb-3">A disaster is coming, you should not plant in Bocaue, Bulacan...</p>
                    <button class="w-full px-4 py-2 bg-yellow-400 hover:bg-yellow-500 text-gray-800 font-semibold rounded-lg transition-colors">
                        See More
                    </button>
                </div>

                <!-- Best Crops Card -->
                <div class="bg-white rounded-xl shadow-md p-6 border border-gray-200">
                    <h3 class="text-lg font-bold text-gray-800 mb-4">Best Crops</h3>
                    <p class="text-sm text-gray-600 mb-3 min-h-[120px]">Best crops for your area based on climate and soil conditions. Rice, Corn, and Vegetables are recommended.</p>
                    <button class="w-full px-4 py-2 bg-yellow-400 hover:bg-yellow-500 text-gray-800 font-semibold rounded-lg transition-colors">
                        View Details
                    </button>
                </div>

                <!-- Climate Rule Card -->
                <div class="bg-white rounded-xl shadow-md p-6 border border-gray-200 lg:col-span-3">
                    <h3 class="text-lg font-bold text-gray-800 mb-4">Climate Rule</h3>
                    <p class="text-sm text-gray-600 mb-3">The climate is hot in Bocaue, do not plant crops that are sensitive to heat...</p>
                    <button class="px-4 py-2 bg-yellow-400 hover:bg-yellow-500 text-gray-800 font-semibold rounded-lg transition-colors">
                        See More
                    </button>
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
                <button @click="showSubsidyModal = true" class="px-4 py-2 bg-yellow-400 hover:bg-yellow-500 text-gray-800 font-semibold rounded-lg transition-colors">
                    Create Subsidy
                </button>
            </div>
                    Create Subsidy
                </button>
            </div>

            <!-- Filters -->
            <form method="GET" action="{{ route('admin.recommendations') }}" class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
                <!-- Full Name Filter -->
                <div>
                    <input type="text" name="full_name" placeholder="Full Name" value="{{ request('full_name') }}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
                </div>

                <!-- Crop Filter -->
                <div>
                    <input type="text" name="crop" placeholder="Crop" value="{{ request('crop') }}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
                </div>

                <!-- Location Filter -->
                <div>
                    <input type="text" name="location" placeholder="Location" value="{{ request('location') }}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
                </div>

                <!-- Reset Button -->
                <div>
                    <a href="{{ route('admin.recommendations') }}" class="block w-full px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium rounded-md text-center transition-colors">
                        Reset
                    </a>
                </div>

                <!-- Submit Button -->
                <div>
                    <button type="submit" class="w-full px-4 py-2 bg-yellow-400 hover:bg-yellow-500 text-gray-800 font-semibold rounded-md transition-colors">
                        Generate
                    </button>
                </div>
            </form>

            <!-- Data Table -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left">
                                <input type="checkbox" class="rounded border-gray-300">
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Full Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Crop</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subsidy Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subsidy Amt</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Updated</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <!-- Example Row 1 -->
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <input type="checkbox" class="rounded border-gray-300">
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">Maria Angelica M. Torres</div>
                                <div class="text-xs text-gray-500">ID: 123-456</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Cabbage</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">
                                    Approved
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">₱5,000</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">2025-06-10</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <button class="text-blue-600 hover:text-blue-800 font-medium">Edit</button>
                            </td>
                        </tr>
                        <!-- Example Row 2 -->
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <input type="checkbox" class="rounded border-gray-300">
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">Juan D. Cruz</div>
                                <div class="text-xs text-gray-500">ID: 789-012</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Broccoli</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-800">
                                    Pending
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">₱3,000</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">2025-06-08</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <button class="text-blue-600 hover:text-blue-800 font-medium">Edit</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Footer Info -->
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
                            <!-- Municipality -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Municipality<span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="municipality" required placeholder="Municipality"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                            </div>

                            <!-- Farm Type -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Farm Type<span class="text-red-500">*</span>
                                </label>
                                <select name="farm_type" required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                                    <option value="">Select farm type</option>
                                    <option value="Rainfed">Rainfed</option>
                                    <option value="Irrigated">Irrigated</option>
                                </select>
                            </div>

                            <!-- Year -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Year<span class="text-red-500">*</span>
                                </label>
                                <input type="number" name="year" required placeholder="Year" min="2020" max="2050"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                            </div>

                            <!-- Crop -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Crop<span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="crop" required placeholder="Crop"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                            </div>

                            <!-- Area Planted (ha) -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Area Planted (ha)<span class="text-red-500">*</span>
                                </label>
                                <input type="number" name="area_planted" required placeholder="Enter a number" step="0.01" min="0"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                            </div>

                            <!-- Area Harvested (ha) -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Area Harvested (ha)<span class="text-red-500">*</span>
                                </label>
                                <input type="number" name="area_harvested" required placeholder="Enter a number" step="0.01" min="0"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                            </div>

                            <!-- Production (mt) -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Production (mt)<span class="text-red-500">*</span>
                                </label>
                                <input type="number" name="production" required placeholder="Enter a number" step="0.01" min="0"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                            </div>

                            <!-- Productivity (mt/ha) -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Productivity (mt/ha)
                                </label>
                                <input type="number" name="productivity" placeholder="Enter a number" step="0.01" min="0"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="flex justify-start pt-4">
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
            // Allocation vs Need Bar Chart
            const ctx = document.getElementById('allocationChart');
            if (ctx) {
                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: ['Cabbage', 'Carrot', 'Broccoli', 'Lettuce', 'Beans', 'Potato'],
                        datasets: [
                            {
                                label: 'Needed Seeds (kg)',
                                data: [850, 1100, 950, 450, 1000, 1050],
                                backgroundColor: 'rgba(234, 179, 8, 0.8)', // Yellow
                                borderColor: 'rgb(234, 179, 8)',
                                borderWidth: 1,
                                borderRadius: 4
                            },
                            {
                                label: 'Allocated Seeds (kg)',
                                data: [550, 900, 650, 850, 700, 850],
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
