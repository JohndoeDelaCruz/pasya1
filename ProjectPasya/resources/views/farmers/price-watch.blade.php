<x-farmer-layout>
    <x-slot name="title">Price Watch</x-slot>

    <div class="h-full overflow-auto bg-gray-100" x-data="priceWatch()">
        <div class="p-6">
            <!-- Header -->
            <div class="bg-gradient-to-r from-green-500 via-green-600 to-emerald-600 text-white px-8 py-6 rounded-2xl mb-6 shadow-lg">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div>
                        <h1 class="text-2xl font-bold mb-1">Daily Price Watch</h1>
                        <p class="text-green-100 text-sm">La Trinidad Trading Post Prices</p>
                    </div>
                    <div class="flex items-center space-x-3">
                        <div class="bg-white/20 backdrop-blur-sm rounded-xl px-4 py-2">
                            <p class="text-xs text-green-100">Last Updated</p>
                            <p class="font-semibold">{{ now()->format('M d, Y g:i A') }}</p>
                        </div>
                        <button @click="refreshPrices()" class="p-3 bg-white/20 hover:bg-white/30 rounded-xl transition">
                            <svg class="w-5 h-5" :class="{ 'animate-spin': loading }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Filter Tabs -->
            <div class="flex flex-wrap gap-2 mb-6">
                <button @click="activeCategory = 'all'" 
                        :class="activeCategory === 'all' ? 'bg-green-500 text-white' : 'bg-white text-gray-600 hover:bg-gray-50'"
                        class="px-4 py-2 rounded-full text-sm font-medium transition shadow-sm">
                    All Crops
                </button>
                <button @click="activeCategory = 'Leafy Vegetables'" 
                        :class="activeCategory === 'Leafy Vegetables' ? 'bg-green-500 text-white' : 'bg-white text-gray-600 hover:bg-gray-50'"
                        class="px-4 py-2 rounded-full text-sm font-medium transition shadow-sm">
                    🥬 Leafy
                </button>
                <button @click="activeCategory = 'Root Vegetables'" 
                        :class="activeCategory === 'Root Vegetables' ? 'bg-green-500 text-white' : 'bg-white text-gray-600 hover:bg-gray-50'"
                        class="px-4 py-2 rounded-full text-sm font-medium transition shadow-sm">
                    🥕 Root
                </button>
                <button @click="activeCategory = 'Fruit Vegetables'" 
                        :class="activeCategory === 'Fruit Vegetables' ? 'bg-green-500 text-white' : 'bg-white text-gray-600 hover:bg-gray-50'"
                        class="px-4 py-2 rounded-full text-sm font-medium transition shadow-sm">
                    🍅 Fruit
                </button>
                <button @click="activeCategory = 'Cruciferous'" 
                        :class="activeCategory === 'Cruciferous' ? 'bg-green-500 text-white' : 'bg-white text-gray-600 hover:bg-gray-50'"
                        class="px-4 py-2 rounded-full text-sm font-medium transition shadow-sm">
                    🥦 Cruciferous
                </button>
                <button @click="activeCategory = 'Legumes'" 
                        :class="activeCategory === 'Legumes' ? 'bg-green-500 text-white' : 'bg-white text-gray-600 hover:bg-gray-50'"
                        class="px-4 py-2 rounded-full text-sm font-medium transition shadow-sm">
                    🫛 Legumes
                </button>
            </div>

            <!-- Search Bar -->
            <div class="relative mb-6">
                <input type="text" 
                       x-model="searchQuery" 
                       placeholder="Search crops..." 
                       class="w-full pl-12 pr-4 py-3 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-transparent shadow-sm">
                <svg class="w-5 h-5 text-gray-400 absolute left-4 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>

            <!-- Price Cards Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 mb-8">
                <template x-for="price in filteredPrices" :key="price.name">
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 hover:shadow-lg transition-all duration-300 cursor-pointer"
                         @click="showPriceDetail(price)">
                        <div class="flex items-start justify-between mb-3">
                            <div class="w-12 h-12 rounded-xl flex items-center justify-center overflow-hidden"
                                 :class="getCategoryBgClass(price.category)">
                                <img :src="price.image" :alt="price.name" class="w-full h-full object-cover" onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                                <span class="text-2xl hidden" x-text="price.emoji"></span>
                            </div>
                            <span class="px-2 py-1 text-xs font-medium rounded-full"
                                  :class="price.change >= 0 ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'">
                                <span x-text="price.change >= 0 ? '↑' : '↓'"></span>
                                <span x-text="Math.abs(price.change).toFixed(2)"></span>
                            </span>
                        </div>
                        <h3 class="font-semibold text-gray-800 mb-1" x-text="price.name"></h3>
                        <p class="text-xs text-gray-500 mb-3" x-text="price.category"></p>
                        <div class="flex items-baseline space-x-1">
                            <span class="text-2xl font-bold text-gray-900">₱</span>
                            <span class="text-2xl font-bold text-gray-900" x-text="price.price.toFixed(2)"></span>
                            <span class="text-sm text-gray-500">/ <span x-text="price.unit"></span></span>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Price Trends Chart -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h2 class="text-xl font-bold text-gray-800">Price Trends</h2>
                        <p class="text-sm text-gray-500">Last 6 months comparison</p>
                    </div>
                    <select x-model="selectedTrendCrop" class="px-4 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-green-500">
                        <option value="all">All Selected</option>
                        <template x-for="dataset in trendData.datasets" :key="dataset.label">
                            <option :value="dataset.label" x-text="dataset.label"></option>
                        </template>
                    </select>
                </div>
                
                <!-- Simple Chart Visualization -->
                <div class="relative h-64">
                    <div class="absolute inset-0 flex items-end justify-between px-2">
                        <template x-for="(month, index) in trendData.labels" :key="index">
                            <div class="flex-1 flex flex-col items-center space-y-1 mx-1">
                                <!-- Bars -->
                                <div class="w-full flex justify-center space-x-1">
                                    <template x-for="(dataset, dIndex) in trendData.datasets.slice(0, 5)" :key="dataset.label">
                                        <div class="w-3 rounded-t transition-all duration-500"
                                             :style="'height: ' + Math.min(dataset.data[index] * 0.5, 200) + 'px; background-color: ' + dataset.color"></div>
                                    </template>
                                </div>
                                <span class="text-xs text-gray-500" x-text="month"></span>
                            </div>
                        </template>
                    </div>
                </div>
                
                <!-- Legend (Dynamic) -->
                <div class="flex flex-wrap justify-center gap-4 mt-4 pt-4 border-t border-gray-100">
                    <template x-for="dataset in trendData.datasets.slice(0, 5)" :key="dataset.label">
                        <div class="flex items-center space-x-2">
                            <div class="w-3 h-3 rounded" :style="'background-color: ' + dataset.color"></div>
                            <span class="text-sm text-gray-600" x-text="dataset.label"></span>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <!-- Price Detail Modal -->
        <div x-show="showDetailModal" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             class="fixed inset-0 z-50 overflow-y-auto" 
             style="display: none;">
            <div class="fixed inset-0 bg-black bg-opacity-50" @click="showDetailModal = false"></div>
            
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative bg-white rounded-2xl shadow-xl max-w-md w-full overflow-hidden" @click.stop>
                    <div class="px-6 py-5 bg-gradient-to-r from-green-500 to-emerald-500 text-white">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="w-16 h-16 rounded-xl overflow-hidden bg-white/20 flex items-center justify-center">
                                    <img :src="selectedPrice?.image" :alt="selectedPrice?.name" class="w-full h-full object-cover" onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                                    <span class="text-4xl hidden" x-text="selectedPrice?.emoji"></span>
                                </div>
                                <div>
                                    <h3 class="text-xl font-bold" x-text="selectedPrice?.name"></h3>
                                    <p class="text-green-100 text-sm" x-text="selectedPrice?.category"></p>
                                </div>
                            </div>
                            <button @click="showDetailModal = false" class="p-2 hover:bg-white/20 rounded-lg transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                    
                    <div class="p-6">
                        <div class="text-center mb-6">
                            <p class="text-sm text-gray-500 mb-1">Current Price</p>
                            <div class="flex items-baseline justify-center space-x-1">
                                <span class="text-4xl font-bold text-gray-900">₱</span>
                                <span class="text-4xl font-bold text-gray-900" x-text="selectedPrice?.price?.toFixed(2)"></span>
                                <span class="text-lg text-gray-500">/ <span x-text="selectedPrice?.unit"></span></span>
                            </div>
                            <div class="mt-2 inline-flex items-center px-3 py-1 rounded-full text-sm font-medium"
                                 :class="selectedPrice?.change >= 0 ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'">
                                <span x-text="selectedPrice?.change >= 0 ? '↑' : '↓'"></span>
                                <span class="ml-1">₱<span x-text="Math.abs(selectedPrice?.change || 0).toFixed(2)"></span> from yesterday</span>
                            </div>
                        </div>
                        
                        <div class="space-y-3">
                            <div class="flex justify-between p-3 bg-gray-50 rounded-xl">
                                <span class="text-gray-600">Weekly Average</span>
                                <span class="font-semibold text-gray-800">₱<span x-text="((selectedPrice?.price || 0) * 0.95).toFixed(2)"></span></span>
                            </div>
                            <div class="flex justify-between p-3 bg-gray-50 rounded-xl">
                                <span class="text-gray-600">Monthly Average</span>
                                <span class="font-semibold text-gray-800">₱<span x-text="((selectedPrice?.price || 0) * 0.92).toFixed(2)"></span></span>
                            </div>
                            <div class="flex justify-between p-3 bg-gray-50 rounded-xl">
                                <span class="text-gray-600">Last Year Same Period</span>
                                <span class="font-semibold text-gray-800">₱<span x-text="((selectedPrice?.price || 0) * 0.85).toFixed(2)"></span></span>
                            </div>
                        </div>
                        
                        <div class="mt-6 p-4 bg-green-50 border border-green-200 rounded-xl">
                            <div class="flex items-start space-x-3">
                                <svg class="w-5 h-5 text-green-600 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                </svg>
                                <div>
                                    <p class="text-sm font-medium text-green-800">Market Insight</p>
                                    <p class="text-sm text-green-700 mt-1" x-text="selectedPrice?.change >= 0 ? 'Prices are trending up. Good time to sell if you have harvest ready.' : 'Prices are down. Consider storing if possible or selling to cooperatives.'"></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="px-6 py-4 bg-gray-50 border-t">
                        <button @click="showDetailModal = false" class="w-full bg-green-500 hover:bg-green-600 text-white font-medium py-2.5 rounded-xl transition">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function priceWatch() {
            return {
                loading: false,
                activeCategory: 'all',
                searchQuery: '',
                selectedTrendCrop: 'all',
                showDetailModal: false,
                selectedPrice: null,
                
                prices: @json($prices),
                
                trendData: @json($trends),
                
                get filteredPrices() {
                    return this.prices.filter(price => {
                        const matchesCategory = this.activeCategory === 'all' || price.category === this.activeCategory;
                        const matchesSearch = price.name.toLowerCase().includes(this.searchQuery.toLowerCase());
                        return matchesCategory && matchesSearch;
                    });
                },
                
                getCategoryBgClass(category) {
                    const classes = {
                        'Leafy Vegetables': 'bg-green-100',
                        'Root Vegetables': 'bg-orange-100',
                        'Fruit Vegetables': 'bg-red-100',
                        'Cruciferous': 'bg-emerald-100',
                        'Legumes': 'bg-lime-100',
                    };
                    return classes[category] || 'bg-gray-100';
                },
                
                showPriceDetail(price) {
                    this.selectedPrice = price;
                    this.showDetailModal = true;
                },
                
                async refreshPrices() {
                    this.loading = true;
                    // Simulate API call
                    await new Promise(resolve => setTimeout(resolve, 1000));
                    // In production, fetch from API
                    this.loading = false;
                }
            }
        }
    </script>
    @endpush
</x-farmer-layout>
