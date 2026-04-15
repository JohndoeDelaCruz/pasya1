<x-farmer-layout>
    <x-slot name="title">Price Watch</x-slot>

    <div class="h-full overflow-auto bg-gray-100" x-data="priceWatch()">
        <div class="p-3 sm:p-6">
            <!-- Header -->
            <div class="bg-gradient-to-r from-green-500 via-green-600 to-emerald-600 text-white px-4 py-4 sm:px-8 sm:py-6 rounded-2xl mb-4 sm:mb-6 shadow-lg">
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

            <!-- Filter Tabs - Simple for Farmers -->
            <div class="mb-6">
                <p class="text-sm text-gray-600 mb-3">Filter:</p>
                <div class="flex flex-wrap gap-2">
                    <button @click="activeCategory = 'all'" 
                            :class="activeCategory === 'all' ? 'bg-green-500 text-white' : 'bg-white text-gray-600 hover:bg-gray-50'"
                            class="px-4 py-2 rounded-full text-sm font-medium transition shadow-sm">
                        📋 All
                    </button>
                    <button @click="activeCategory = 'Leafy Vegetables'" 
                            :class="activeCategory === 'Leafy Vegetables' ? 'bg-green-500 text-white' : 'bg-white text-gray-600 hover:bg-gray-50'"
                            class="px-4 py-2 rounded-full text-sm font-medium transition shadow-sm">
                        🥬 Pechay, Lettuce
                    </button>
                    <button @click="activeCategory = 'Root Vegetables'" 
                            :class="activeCategory === 'Root Vegetables' ? 'bg-green-500 text-white' : 'bg-white text-gray-600 hover:bg-gray-50'"
                            class="px-4 py-2 rounded-full text-sm font-medium transition shadow-sm">
                        🥕 Carrots, Potato
                    </button>
                    <button @click="activeCategory = 'Fruit Vegetables'" 
                            :class="activeCategory === 'Fruit Vegetables' ? 'bg-green-500 text-white' : 'bg-white text-gray-600 hover:bg-gray-50'"
                            class="px-4 py-2 rounded-full text-sm font-medium transition shadow-sm">
                        🍅 Tomato, Sayote
                    </button>
                    <button @click="activeCategory = 'Cruciferous'" 
                            :class="activeCategory === 'Cruciferous' ? 'bg-green-500 text-white' : 'bg-white text-gray-600 hover:bg-gray-50'"
                            class="px-4 py-2 rounded-full text-sm font-medium transition shadow-sm">
                        🥦 Broccoli, Cabbage
                    </button>
                    <button @click="activeCategory = 'Legumes'" 
                            :class="activeCategory === 'Legumes' ? 'bg-green-500 text-white' : 'bg-white text-gray-600 hover:bg-gray-50'"
                            class="px-4 py-2 rounded-full text-sm font-medium transition shadow-sm">
                        🫛 Beans
                    </button>
                </div>
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

            <!-- Price Trends Module -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-5">
                    <div>
                        <h2 class="text-xl font-bold text-gray-800">Price Trends</h2>
                        <p class="text-sm text-gray-500">Last 6 months comparison</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <label class="text-xs font-medium text-gray-500 uppercase tracking-wider">View</label>
                        <select x-model="selectedTrendCrop" class="px-4 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-green-500">
                            <option value="all">Market Basket</option>
                            <template x-for="dataset in trendData.datasets" :key="dataset.label">
                                <option :value="dataset.label" x-text="dataset.label"></option>
                            </template>
                        </select>
                    </div>
                </div>

                <!-- Trend Snapshot -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-5">
                    <div class="bg-green-50 border border-green-100 rounded-xl p-4">
                        <p class="text-xs text-green-700 uppercase tracking-wider">Latest Avg</p>
                        <p class="text-2xl font-bold text-green-800" x-text="formatPeso(trendSummary.latest)"></p>
                    </div>
                    <div class="bg-amber-50 border border-amber-100 rounded-xl p-4">
                        <p class="text-xs text-amber-700 uppercase tracking-wider">Peak Price</p>
                        <p class="text-2xl font-bold text-amber-800" x-text="formatPeso(trendSummary.peak)"></p>
                    </div>
                    <div class="bg-blue-50 border border-blue-100 rounded-xl p-4">
                        <p class="text-xs text-blue-700 uppercase tracking-wider">6-Month Change</p>
                        <p class="text-2xl font-bold"
                           :class="trendSummary.momentum >= 0 ? 'text-blue-800' : 'text-red-700'"
                           x-text="(trendSummary.momentum >= 0 ? '+' : '') + trendSummary.momentum.toFixed(1) + '%'">
                        </p>
                    </div>
                </div>

                <!-- Chart Panel -->
                <div class="trend-chart-panel relative rounded-2xl border border-gray-100 bg-gradient-to-b from-gray-50 to-white p-4 sm:p-5">
                    <template x-if="visibleTrendDatasets.length === 0">
                        <div class="h-64 flex items-center justify-center text-sm text-gray-500">
                            No trend data available yet.
                        </div>
                    </template>

                    <template x-if="visibleTrendDatasets.length > 0">
                        <div>
                            <div class="relative h-64 sm:h-72">
                                <!-- Horizontal Grid + Y labels -->
                                <div class="absolute inset-0">
                                    <template x-for="(tick, index) in trendTicks" :key="index">
                                        <div class="absolute left-0 right-0 border-t border-dashed border-gray-200"
                                             :style="'top: ' + ((index / (trendTicks.length - 1)) * 100) + '%'">
                                            <span class="absolute -top-2 left-0 text-[11px] text-gray-400 bg-white px-1"
                                                  x-text="'₱' + tick.toFixed(0)"></span>
                                        </div>
                                    </template>
                                </div>

                                <!-- Trend Lines -->
                                <svg class="absolute inset-0 w-full h-full" viewBox="0 0 1000 300" preserveAspectRatio="none">
                                    <template x-for="dataset in visibleTrendDatasets" :key="dataset.label">
                                        <g>
                                            <path :d="buildTrendPath(dataset)"
                                                  :stroke="dataset.color"
                                                  stroke-width="3"
                                                  fill="none"
                                                  stroke-linecap="round"
                                                  stroke-linejoin="round"
                                                  class="drop-shadow-sm"></path>

                                            <template x-for="(value, pointIndex) in dataset.data" :key="dataset.label + '-' + pointIndex">
                                                <circle :cx="getTrendX(pointIndex, dataset.data.length)"
                                                        :cy="getTrendY(value)"
                                                        r="5"
                                                        :fill="dataset.color"
                                                        class="cursor-pointer transition-transform hover:scale-125"
                                                        @mouseenter="showTrendPointTooltip($event, dataset.label, trendData.labels[pointIndex], value, dataset.color)"
                                                        @mouseleave="hideTrendPointTooltip()"></circle>
                                            </template>
                                        </g>
                                    </template>
                                </svg>

                                <!-- Tooltip -->
                                <div x-show="trendTooltip.show"
                                     x-transition
                                     class="absolute z-20 pointer-events-none bg-white shadow-lg border border-gray-200 rounded-xl px-3 py-2 text-xs"
                                     :style="'left:' + trendTooltip.x + 'px; top:' + trendTooltip.y + 'px; transform: translate(-50%, -115%);'"
                                     style="display: none;">
                                    <p class="font-semibold text-gray-800" x-text="trendTooltip.label"></p>
                                    <p class="text-gray-500" x-text="trendTooltip.month"></p>
                                    <p class="font-bold" :style="'color:' + trendTooltip.color" x-text="formatPeso(trendTooltip.value)"></p>
                                </div>
                            </div>

                            <!-- Month Labels -->
                            <div class="mt-3 grid" :style="'grid-template-columns: repeat(' + trendData.labels.length + ', minmax(0, 1fr));'">
                                <template x-for="(month, index) in trendData.labels" :key="index">
                                    <div class="text-center text-xs text-gray-500 font-medium" x-text="month"></div>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Interactive Legend -->
                <div class="flex flex-wrap justify-center gap-2 mt-5 pt-4 border-t border-gray-100">
                    <button @click="selectedTrendCrop = 'all'"
                            class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-medium border transition"
                            :class="selectedTrendCrop === 'all' ? 'bg-gray-900 text-white border-gray-900' : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-50'">
                        All Crops
                    </button>
                    <template x-for="dataset in trendData.datasets" :key="dataset.label">
                        <button @click="selectedTrendCrop = dataset.label"
                                class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-medium border transition"
                                :class="selectedTrendCrop === dataset.label ? 'text-white border-transparent' : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-50'"
                                :style="selectedTrendCrop === dataset.label ? 'background-color:' + dataset.color : ''">
                            <span class="w-2.5 h-2.5 rounded-full"
                                  :style="selectedTrendCrop === dataset.label ? 'background: rgba(255,255,255,0.9)' : 'background:' + dataset.color"></span>
                            <span x-text="dataset.label"></span>
                        </button>
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
                trendTooltip: {
                    show: false,
                    x: 0,
                    y: 0,
                    label: '',
                    month: '',
                    value: 0,
                    color: '#22c55e',
                },
                
                prices: @json($prices),
                
                trendData: @json($trends),
                
                get filteredPrices() {
                    return this.prices.filter(price => {
                        const matchesCategory = this.activeCategory === 'all' || price.category === this.activeCategory;
                        const matchesSearch = price.name.toLowerCase().includes(this.searchQuery.toLowerCase());
                        return matchesCategory && matchesSearch;
                    });
                },

                get visibleTrendDatasets() {
                    const datasets = this.trendData?.datasets || [];
                    if (this.selectedTrendCrop === 'all') {
                        return datasets;
                    }
                    return datasets.filter(dataset => dataset.label === this.selectedTrendCrop);
                },

                get trendMaxValue() {
                    const values = this.visibleTrendDatasets.flatMap(dataset => dataset.data || []);
                    const max = values.length ? Math.max(...values.map(value => Number(value) || 0)) : 0;
                    return Math.max(max, 1);
                },

                get trendTicks() {
                    const max = this.trendMaxValue;
                    const steps = 4;
                    return Array.from({ length: steps + 1 }, (_, index) => {
                        const ratio = (steps - index) / steps;
                        return max * ratio;
                    });
                },

                get trendSummary() {
                    const datasets = this.visibleTrendDatasets;
                    if (!datasets.length) {
                        return { latest: 0, peak: 0, momentum: 0 };
                    }

                    const allValues = datasets.flatMap(dataset => dataset.data.map(value => Number(value) || 0));
                    const latestValues = datasets.map(dataset => Number(dataset.data[dataset.data.length - 1]) || 0);
                    const firstValues = datasets.map(dataset => Number(dataset.data[0]) || 0);

                    const latest = latestValues.reduce((sum, value) => sum + value, 0) / latestValues.length;
                    const first = firstValues.reduce((sum, value) => sum + value, 0) / firstValues.length;
                    const peak = allValues.length ? Math.max(...allValues) : 0;
                    const momentum = first > 0 ? ((latest - first) / first) * 100 : 0;

                    return { latest, peak, momentum };
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

                formatPeso(value) {
                    return `₱${(Number(value) || 0).toFixed(2)}`;
                },

                getTrendX(index, points) {
                    const leftPadding = 58;
                    const rightPadding = 22;
                    const width = 1000 - leftPadding - rightPadding;
                    if (points <= 1) {
                        return leftPadding + width / 2;
                    }
                    return leftPadding + ((width * index) / (points - 1));
                },

                getTrendY(value) {
                    const topPadding = 24;
                    const bottomPadding = 24;
                    const height = 300 - topPadding - bottomPadding;
                    const normalized = (Number(value) || 0) / this.trendMaxValue;
                    return topPadding + (height * (1 - normalized));
                },

                buildTrendPath(dataset) {
                    const points = dataset.data.map((value, index) => {
                        const x = this.getTrendX(index, dataset.data.length);
                        const y = this.getTrendY(value);
                        return `${x},${y}`;
                    });

                    return points.length ? `M ${points.join(' L ')}` : '';
                },

                showTrendPointTooltip(event, label, month, value, color) {
                    const panel = event.currentTarget.closest('.trend-chart-panel');
                    if (!panel) {
                        return;
                    }

                    const rect = panel.getBoundingClientRect();

                    this.trendTooltip = {
                        show: true,
                        x: event.clientX - rect.left,
                        y: event.clientY - rect.top,
                        label,
                        month,
                        value: Number(value) || 0,
                        color,
                    };
                },

                hideTrendPointTooltip() {
                    this.trendTooltip.show = false;
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
