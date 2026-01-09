<x-farmer-layout>
    <x-slot name="title">Harvest History & Crop List</x-slot>

    <div class="h-full overflow-auto bg-gray-100" x-data="harvestHistory()">
        <div class="p-6">
            <!-- Header -->
            <div class="bg-gradient-to-r from-green-500 via-green-600 to-emerald-600 text-white px-8 py-6 rounded-2xl mb-6 shadow-lg">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div>
                        <h1 class="text-2xl font-bold mb-1">Harvest History & Crop List</h1>
                        <p class="text-green-100 text-sm">Track your farming activities and production</p>
                    </div>
                    <div class="flex items-center space-x-3">
                        <button @click="showAddModal = true" class="flex items-center space-x-2 bg-white text-green-600 hover:bg-green-50 px-4 py-2 rounded-xl font-medium transition shadow-sm">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            <span>Add Harvest</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Summary Cards -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white rounded-xl shadow-sm p-5 border border-gray-100">
                    <div class="flex items-center space-x-3">
                        <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.5 16a3.5 3.5 0 01-.369-6.98 4 4 0 117.753-1.977A4.5 4.5 0 1113.5 16h-8z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 uppercase tracking-wider">Total Production</p>
                            <p class="text-xl font-bold text-gray-800"><span x-text="summary.totalProduction"></span> MT</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-xl shadow-sm p-5 border border-gray-100">
                    <div class="flex items-center space-x-3">
                        <div class="w-12 h-12 bg-amber-100 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-amber-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h8a2 2 0 012 2v12a1 1 0 110 2h-3a1 1 0 01-1-1v-2a1 1 0 00-1-1H9a1 1 0 00-1 1v2a1 1 0 01-1 1H4a1 1 0 110-2V4zm3 1h2v2H7V5zm2 4H7v2h2V9zm2-4h2v2h-2V5zm2 4h-2v2h2V9z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 uppercase tracking-wider">Area Harvested</p>
                            <p class="text-xl font-bold text-gray-800"><span x-text="summary.totalArea"></span> ha</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-xl shadow-sm p-5 border border-gray-100">
                    <div class="flex items-center space-x-3">
                        <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
                                <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 uppercase tracking-wider">Crop Types</p>
                            <p class="text-xl font-bold text-gray-800" x-text="summary.cropCount"></p>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-xl shadow-sm p-5 border border-gray-100">
                    <div class="flex items-center space-x-3">
                        <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 uppercase tracking-wider">Year</p>
                            <p class="text-xl font-bold text-gray-800" x-text="summary.year"></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabs -->
            <div class="flex space-x-1 mb-6 bg-white rounded-xl p-1 shadow-sm inline-flex">
                <button @click="activeTab = 'history'" 
                        :class="activeTab === 'history' ? 'bg-green-500 text-white' : 'text-gray-600 hover:bg-gray-100'"
                        class="px-6 py-2.5 rounded-lg text-sm font-medium transition">
                    Harvest History
                </button>
                <button @click="activeTab = 'crops'" 
                        :class="activeTab === 'crops' ? 'bg-green-500 text-white' : 'text-gray-600 hover:bg-gray-100'"
                        class="px-6 py-2.5 rounded-lg text-sm font-medium transition">
                    My Crops
                </button>
                <button @click="activeTab = 'analytics'" 
                        :class="activeTab === 'analytics' ? 'bg-green-500 text-white' : 'text-gray-600 hover:bg-gray-100'"
                        class="px-6 py-2.5 rounded-lg text-sm font-medium transition">
                    Analytics
                </button>
            </div>

            <!-- Harvest History Tab -->
            <div x-show="activeTab === 'history'" x-transition>
                <!-- Filters -->
                <div class="flex flex-wrap gap-3 mb-4">
                    <select x-model="filterYear" class="px-4 py-2 bg-white border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-green-500">
                        <option value="">All Years</option>
                        <option value="2025">2025</option>
                        <option value="2024">2024</option>
                        <option value="2023">2023</option>
                    </select>
                    <select x-model="filterCrop" class="px-4 py-2 bg-white border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-green-500">
                        <option value="">All Crops</option>
                        <template x-for="crop in cropTypes" :key="crop">
                            <option :value="crop" x-text="crop"></option>
                        </template>
                    </select>
                    <input type="text" x-model="searchQuery" placeholder="Search..." 
                           class="px-4 py-2 bg-white border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-green-500">
                </div>

                <!-- History Table -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50 border-b border-gray-200">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Crop</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Date</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Area (ha)</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Production (MT)</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Productivity</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <template x-for="(record, index) in filteredHistory" :key="index">
                                    <tr class="hover:bg-gray-50 transition">
                                        <td class="px-6 py-4">
                                            <div class="flex items-center space-x-3">
                                                <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                                                    <span class="text-lg" x-text="getCropEmoji(record.crop)"></span>
                                                </div>
                                                <span class="font-medium text-gray-800" x-text="record.crop"></span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-gray-600" x-text="formatDate(record.year, record.month)"></td>
                                        <td class="px-6 py-4 text-gray-600" x-text="record.area_harvested"></td>
                                        <td class="px-6 py-4">
                                            <span class="font-semibold text-gray-800" x-text="record.production"></span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="px-2.5 py-1 bg-green-100 text-green-700 text-xs font-medium rounded-full" 
                                                  x-text="record.productivity + ' MT/ha'"></span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <button @click="viewDetails(record)" class="text-green-600 hover:text-green-700 font-medium text-sm">
                                                View Details
                                            </button>
                                        </td>
                                    </tr>
                                </template>
                                <template x-if="filteredHistory.length === 0">
                                    <tr>
                                        <td colspan="6" class="px-6 py-12 text-center">
                                            <div class="flex flex-col items-center">
                                                <svg class="w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                </svg>
                                                <p class="text-gray-500">No harvest records found</p>
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- My Crops Tab -->
            <div x-show="activeTab === 'crops'" x-transition>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <template x-for="crop in myCrops" :key="crop.name">
                        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 hover:shadow-lg transition">
                            <div class="flex items-start justify-between mb-4">
                                <div class="w-14 h-14 bg-green-100 rounded-xl flex items-center justify-center text-2xl">
                                    <span x-text="crop.emoji"></span>
                                </div>
                                <span class="px-2.5 py-1 text-xs font-medium rounded-full"
                                      :class="crop.status === 'Growing' ? 'bg-green-100 text-green-700' : (crop.status === 'Ready' ? 'bg-amber-100 text-amber-700' : 'bg-gray-100 text-gray-600')"
                                      x-text="crop.status"></span>
                            </div>
                            <h3 class="text-lg font-bold text-gray-800 mb-1" x-text="crop.name"></h3>
                            <p class="text-sm text-gray-500 mb-4" x-text="crop.variety"></p>
                            
                            <div class="space-y-2">
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-500">Area Planted</span>
                                    <span class="font-medium text-gray-800" x-text="crop.area + ' ha'"></span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-500">Planted On</span>
                                    <span class="font-medium text-gray-800" x-text="crop.plantedDate"></span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-500">Expected Harvest</span>
                                    <span class="font-medium text-green-600" x-text="crop.expectedHarvest"></span>
                                </div>
                            </div>
                            
                            <!-- Progress Bar -->
                            <div class="mt-4">
                                <div class="flex justify-between text-xs text-gray-500 mb-1">
                                    <span>Growth Progress</span>
                                    <span x-text="crop.progress + '%'"></span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-green-500 h-2 rounded-full transition-all duration-500" :style="'width: ' + crop.progress + '%'"></div>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Analytics Tab -->
            <div x-show="activeTab === 'analytics'" x-transition>
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Production by Crop -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                        <h3 class="text-lg font-bold text-gray-800 mb-4">Production by Crop</h3>
                        <div class="space-y-4">
                            <template x-for="(data, index) in productionByCrop" :key="index">
                                <div>
                                    <div class="flex justify-between text-sm mb-1">
                                        <span class="font-medium text-gray-700" x-text="data.crop"></span>
                                        <span class="text-gray-500" x-text="data.production + ' MT'"></span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-3">
                                        <div class="h-3 rounded-full transition-all duration-500"
                                             :class="['bg-green-500', 'bg-emerald-500', 'bg-teal-500', 'bg-lime-500', 'bg-green-400'][index % 5]"
                                             :style="'width: ' + (data.production / maxProduction * 100) + '%'"></div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Monthly Trend -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                        <h3 class="text-lg font-bold text-gray-800 mb-4">Monthly Production Trend</h3>
                        <div class="h-64 flex items-end justify-between px-2">
                            <template x-for="(month, index) in monthlyTrend" :key="index">
                                <div class="flex-1 flex flex-col items-center mx-1">
                                    <div class="w-full max-w-[40px] bg-green-500 rounded-t transition-all duration-500"
                                         :style="'height: ' + (month.value * 2) + 'px'"></div>
                                    <span class="text-xs text-gray-500 mt-2" x-text="month.month"></span>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add Harvest Modal -->
        <div x-show="showAddModal" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             class="fixed inset-0 z-50 overflow-y-auto" 
             style="display: none;">
            <div class="fixed inset-0 bg-black bg-opacity-50" @click="showAddModal = false"></div>
            
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative bg-white rounded-2xl shadow-xl max-w-lg w-full overflow-hidden" @click.stop>
                    <div class="px-6 py-5 bg-gradient-to-r from-green-500 to-emerald-500 text-white">
                        <div class="flex items-center justify-between">
                            <h3 class="text-xl font-bold">Add Harvest Record</h3>
                            <button @click="showAddModal = false" class="p-2 hover:bg-white/20 rounded-lg transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                    
                    <form @submit.prevent="addHarvest()" class="p-6 space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Crop Type</label>
                            <select x-model="newHarvest.crop" required class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                <option value="">Select a crop</option>
                                <template x-for="crop in cropTypes" :key="crop">
                                    <option :value="crop" x-text="crop"></option>
                                </template>
                            </select>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Month</label>
                                <select x-model="newHarvest.month" required class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                    <option value="">Select month</option>
                                    <option value="1">January</option>
                                    <option value="2">February</option>
                                    <option value="3">March</option>
                                    <option value="4">April</option>
                                    <option value="5">May</option>
                                    <option value="6">June</option>
                                    <option value="7">July</option>
                                    <option value="8">August</option>
                                    <option value="9">September</option>
                                    <option value="10">October</option>
                                    <option value="11">November</option>
                                    <option value="12">December</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Year</label>
                                <select x-model="newHarvest.year" required class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                    <option value="">Select year</option>
                                    <option value="2025">2025</option>
                                    <option value="2024">2024</option>
                                    <option value="2023">2023</option>
                                </select>
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Area Harvested (hectares)</label>
                            <input type="number" step="0.01" x-model="newHarvest.area" required 
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                   placeholder="e.g., 2.5">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Production (metric tons)</label>
                            <input type="number" step="0.01" x-model="newHarvest.production" required 
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                   placeholder="e.g., 15.5">
                        </div>
                        
                        <div class="flex space-x-3 pt-4">
                            <button type="button" @click="showAddModal = false" 
                                    class="flex-1 px-4 py-2.5 border border-gray-300 text-gray-700 rounded-xl hover:bg-gray-50 transition font-medium">
                                Cancel
                            </button>
                            <button type="submit" 
                                    class="flex-1 px-4 py-2.5 bg-green-500 hover:bg-green-600 text-white rounded-xl transition font-medium">
                                Save Record
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Details Modal -->
        <div x-show="showDetailsModal" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             class="fixed inset-0 z-50 overflow-y-auto" 
             style="display: none;">
            <div class="fixed inset-0 bg-black bg-opacity-50" @click="showDetailsModal = false"></div>
            
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative bg-white rounded-2xl shadow-xl max-w-md w-full overflow-hidden" @click.stop>
                    <div class="px-6 py-5 bg-gradient-to-r from-green-500 to-emerald-500 text-white">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <span class="text-3xl" x-text="getCropEmoji(selectedRecord?.crop || '')"></span>
                                <div>
                                    <h3 class="text-xl font-bold" x-text="selectedRecord?.crop"></h3>
                                    <p class="text-green-100 text-sm" x-text="formatDate(selectedRecord?.year, selectedRecord?.month)"></p>
                                </div>
                            </div>
                            <button @click="showDetailsModal = false" class="p-2 hover:bg-white/20 rounded-lg transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                    
                    <div class="p-6 space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="p-4 bg-gray-50 rounded-xl">
                                <p class="text-xs text-gray-500 uppercase tracking-wider mb-1">Area Harvested</p>
                                <p class="text-xl font-bold text-gray-800"><span x-text="selectedRecord?.area_harvested"></span> ha</p>
                            </div>
                            <div class="p-4 bg-gray-50 rounded-xl">
                                <p class="text-xs text-gray-500 uppercase tracking-wider mb-1">Production</p>
                                <p class="text-xl font-bold text-gray-800"><span x-text="selectedRecord?.production"></span> MT</p>
                            </div>
                        </div>
                        <div class="p-4 bg-green-50 rounded-xl">
                            <p class="text-xs text-gray-500 uppercase tracking-wider mb-1">Productivity</p>
                            <p class="text-2xl font-bold text-green-600"><span x-text="selectedRecord?.productivity"></span> MT/ha</p>
                        </div>
                    </div>
                    
                    <div class="px-6 py-4 bg-gray-50 border-t">
                        <button @click="showDetailsModal = false" class="w-full bg-green-500 hover:bg-green-600 text-white font-medium py-2.5 rounded-xl transition">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function harvestHistory() {
            return {
                activeTab: 'history',
                filterYear: '',
                filterCrop: '',
                searchQuery: '',
                showAddModal: false,
                showDetailsModal: false,
                selectedRecord: null,
                
                summary: @json($summary ?? ['totalProduction' => '0', 'totalArea' => '0', 'cropCount' => 0, 'year' => 2025]),
                
                cropTypes: ['CABBAGE', 'CARROTS', 'POTATOES', 'BROCCOLI', 'CAULIFLOWER', 'LETTUCE', 'TOMATOES', 'BELL PEPPER', 'STRING BEANS', 'SAYOTE'],
                
                harvestHistory: @json($crops ?? []),
                
                myCrops: [
                    { name: 'Cabbage', variety: 'Green Star', emoji: '🥬', status: 'Growing', area: 1.5, plantedDate: 'Oct 15, 2025', expectedHarvest: 'Jan 15, 2026', progress: 65 },
                    { name: 'Carrots', variety: 'Nantes', emoji: '🥕', status: 'Ready', area: 0.8, plantedDate: 'Sep 1, 2025', expectedHarvest: 'Dec 20, 2025', progress: 95 },
                    { name: 'Potatoes', variety: 'Atlantic', emoji: '🥔', status: 'Growing', area: 2.0, plantedDate: 'Nov 1, 2025', expectedHarvest: 'Feb 1, 2026', progress: 40 },
                    { name: 'Broccoli', variety: 'Marathon', emoji: '🥦', status: 'Planted', area: 0.5, plantedDate: 'Dec 1, 2025', expectedHarvest: 'Mar 1, 2026', progress: 15 },
                ],
                
                productionByCrop: [
                    { crop: 'Cabbage', production: 150 },
                    { crop: 'Carrots', production: 85 },
                    { crop: 'Potatoes', production: 120 },
                    { crop: 'Broccoli', production: 45 },
                    { crop: 'Lettuce', production: 30 },
                ],
                
                monthlyTrend: [
                    { month: 'Jul', value: 45 },
                    { month: 'Aug', value: 62 },
                    { month: 'Sep', value: 55 },
                    { month: 'Oct', value: 78 },
                    { month: 'Nov', value: 85 },
                    { month: 'Dec', value: 70 },
                ],
                
                newHarvest: {
                    crop: '',
                    month: '',
                    year: '',
                    area: '',
                    production: ''
                },
                
                get maxProduction() {
                    return Math.max(...this.productionByCrop.map(d => d.production));
                },
                
                get filteredHistory() {
                    return this.harvestHistory.filter(record => {
                        const matchesYear = !this.filterYear || record.year == this.filterYear;
                        const matchesCrop = !this.filterCrop || record.crop === this.filterCrop;
                        const matchesSearch = !this.searchQuery || record.crop.toLowerCase().includes(this.searchQuery.toLowerCase());
                        return matchesYear && matchesCrop && matchesSearch;
                    });
                },
                
                getCropEmoji(crop) {
                    const emojis = {
                        'CABBAGE': '🥬',
                        'CARROTS': '🥕',
                        'POTATOES': '🥔',
                        'BROCCOLI': '🥦',
                        'CAULIFLOWER': '🥬',
                        'LETTUCE': '🥗',
                        'TOMATOES': '🍅',
                        'BELL PEPPER': '🫑',
                        'STRING BEANS': '🫛',
                        'SAYOTE': '🥒',
                    };
                    return emojis[crop?.toUpperCase()] || '🌱';
                },
                
                formatDate(year, month) {
                    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                    return months[month - 1] + ' ' + year;
                },
                
                viewDetails(record) {
                    this.selectedRecord = record;
                    this.showDetailsModal = true;
                },
                
                addHarvest() {
                    // Add to local array (in production, send to API)
                    const productivity = (parseFloat(this.newHarvest.production) / parseFloat(this.newHarvest.area)).toFixed(2);
                    this.harvestHistory.unshift({
                        crop: this.newHarvest.crop,
                        month: parseInt(this.newHarvest.month),
                        year: parseInt(this.newHarvest.year),
                        area_harvested: parseFloat(this.newHarvest.area).toFixed(2),
                        production: parseFloat(this.newHarvest.production).toFixed(2),
                        productivity: productivity
                    });
                    
                    // Reset form
                    this.newHarvest = { crop: '', month: '', year: '', area: '', production: '' };
                    this.showAddModal = false;
                }
            }
        }
    </script>
    @endpush
</x-farmer-layout>
