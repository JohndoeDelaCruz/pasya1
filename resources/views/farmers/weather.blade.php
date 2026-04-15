<x-farmer-layout>
    <x-slot name="title">Weather</x-slot>

    @push('styles')
    <style>
        [x-cloak] { display: none !important; }
        .weather-card { transition: all 0.3s ease; }
        .weather-card:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(0,0,0,0.1); }
    </style>
    @endpush

    <div class="space-y-6" x-data="farmerWeather()" x-init="init()">
        <!-- Page Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Weather</h1>
                <p class="text-sm text-gray-500 mt-1">Real-time weather conditions in Benguet</p>
            </div>
            <button @click="refreshCurrent()" :disabled="loadingCurrent"
                class="flex items-center gap-2 px-3 py-2 bg-sky-600 hover:bg-sky-700 disabled:bg-gray-400 text-white text-sm font-medium rounded-lg transition-colors">
                <svg class="w-4 h-4" :class="loadingCurrent && 'animate-spin'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                <span x-text="loadingCurrent ? 'Loading...' : 'Refresh'"></span>
            </button>
        </div>

        <!-- Main Weather Display -->
        <div x-show="currentWeather" x-cloak x-transition class="bg-white rounded-xl shadow-lg overflow-hidden">
            <!-- Gradient Header -->
            <div class="bg-gradient-to-r from-sky-500 to-blue-600 px-6 py-6 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sky-200 text-sm font-medium">Current Weather in</p>
                        <h2 class="text-2xl font-bold mt-1" x-text="currentMunicipality"></h2>
                        <p class="text-sky-100 text-sm mt-1 flex items-center gap-2">
                            <span x-text="currentWeather?.description || ''"></span>
                            <span>&bull;</span>
                            <span x-text="currentWeather?.is_daytime ? 'Daytime' : 'Nighttime'"></span>
                        </p>
                    </div>
                    <div class="text-right">
                        <div class="text-5xl font-bold leading-none" x-text="currentWeather?.temperature?.display || '--'"></div>
                        <p class="text-sky-200 text-sm mt-2">Feels like <span class="font-semibold text-white" x-text="currentWeather?.feels_like?.display || '--'"></span></p>
                    </div>
                </div>
            </div>

            <!-- Weather Details -->
            <div class="grid grid-cols-3 sm:grid-cols-6 gap-3 p-5">
                <div class="bg-blue-50 rounded-lg p-3 text-center">
                    <p class="text-xs text-gray-500">Humidity</p>
                    <p class="text-lg font-bold text-blue-700 mt-1" x-text="currentWeather?.humidity_percent != null ? currentWeather.humidity_percent + '%' : '--'"></p>
                </div>
                <div class="bg-sky-50 rounded-lg p-3 text-center">
                    <p class="text-xs text-gray-500">Rain</p>
                    <p class="text-lg font-bold text-sky-700 mt-1" x-text="currentWeather?.precipitation_probability_percent != null ? currentWeather.precipitation_probability_percent + '%' : '--'"></p>
                </div>
                <div class="bg-teal-50 rounded-lg p-3 text-center">
                    <p class="text-xs text-gray-500">Wind</p>
                    <p class="text-lg font-bold text-teal-700 mt-1" x-text="currentWeather?.wind?.speed?.display || '--'"></p>
                </div>
                <div class="bg-amber-50 rounded-lg p-3 text-center">
                    <p class="text-xs text-gray-500">UV Index</p>
                    <p class="text-lg font-bold text-amber-700 mt-1" x-text="currentWeather?.uv_index != null ? currentWeather.uv_index : '--'"></p>
                </div>
                <div class="bg-gray-50 rounded-lg p-3 text-center">
                    <p class="text-xs text-gray-500">Clouds</p>
                    <p class="text-lg font-bold text-gray-700 mt-1" x-text="currentWeather?.cloud_cover_percent != null ? currentWeather.cloud_cover_percent + '%' : '--'"></p>
                </div>
                <div class="bg-purple-50 rounded-lg p-3 text-center">
                    <p class="text-xs text-gray-500">Storm</p>
                    <p class="text-lg font-bold text-purple-700 mt-1" x-text="currentWeather?.thunderstorm_probability_percent != null ? currentWeather.thunderstorm_probability_percent + '%' : '--'"></p>
                </div>
            </div>

            <div class="px-5 pb-4 text-xs text-gray-400" x-text="currentWeather?.observed_at ? 'Updated ' + new Date(currentWeather.observed_at).toLocaleString() : ''"></div>
        </div>

        <!-- Loading State -->
        <div x-show="loadingCurrent && !currentWeather" class="bg-white rounded-xl shadow-md p-10 text-center">
            <div class="w-10 h-10 border-3 border-sky-300 border-t-sky-600 rounded-full animate-spin mx-auto mb-3"></div>
            <p class="text-gray-500">Loading weather data...</p>
        </div>

        <!-- Error State -->
        <div x-show="errorMessage && !currentWeather" x-cloak class="bg-red-50 border border-red-200 rounded-xl p-6 text-center">
            <svg class="w-10 h-10 mx-auto text-red-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
            </svg>
            <p class="text-red-700 font-medium" x-text="errorMessage"></p>
            <button @click="loadWeather(currentMunicipality)" class="mt-3 text-sm text-red-600 hover:text-red-800 underline">Try again</button>
        </div>

        <!-- Farming Tips Based on Weather -->
        <div x-show="currentWeather" x-cloak x-transition class="bg-white rounded-xl shadow-md p-5">
            <h3 class="font-semibold text-gray-800 mb-3 flex items-center gap-2">
                <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M11 3a1 1 0 10-2 0v1a1 1 0 102 0V3zM15.657 5.757a1 1 0 00-1.414-1.414l-.707.707a1 1 0 001.414 1.414l.707-.707zM18 10a1 1 0 01-1 1h-1a1 1 0 110-2h1a1 1 0 011 1zM5.05 6.464A1 1 0 106.464 5.05l-.707-.707a1 1 0 00-1.414 1.414l.707.707zM5 10a1 1 0 01-1 1H3a1 1 0 110-2h1a1 1 0 011 1zM8 16v-1h4v1a2 2 0 11-4 0zM12 14c.015-.34.208-.646.477-.859a4 4 0 10-4.954 0c.27.213.462.519.476.859h4.002z"/>
                </svg>
                Farming Tips for Today
            </h3>
            <div class="space-y-2">
                <template x-for="tip in getFarmingTips()" :key="tip">
                    <div class="flex items-start gap-2 text-sm text-gray-700">
                        <span class="text-green-500 mt-0.5 flex-shrink-0">✓</span>
                        <span x-text="tip"></span>
                    </div>
                </template>
            </div>
        </div>

        <!-- Other Municipalities -->
        <div>
            <h3 class="font-semibold text-gray-800 mb-3">Other Municipalities</h3>
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
                <template x-for="m in municipalities.filter(m => m !== currentMunicipality)" :key="m">
                    <button @click="switchMunicipality(m)"
                        class="weather-card bg-white rounded-lg shadow-sm p-4 text-left border border-gray-100 hover:border-sky-300">
                        <template x-if="otherWeather[m]">
                            <div>
                                <p class="font-medium text-gray-800 text-sm" x-text="m"></p>
                                <p class="text-xl font-bold text-gray-900 mt-1" x-text="otherWeather[m]?.temperature?.display || '--'"></p>
                                <p class="text-xs text-gray-500 mt-1" x-text="otherWeather[m]?.description || ''"></p>
                            </div>
                        </template>
                        <template x-if="!otherWeather[m]">
                            <div>
                                <p class="font-medium text-gray-800 text-sm" x-text="m"></p>
                                <p class="text-xs text-gray-400 mt-2">Tap to view</p>
                            </div>
                        </template>
                    </button>
                </template>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function farmerWeather() {
            return {
                municipalities: @json($municipalities),
                currentMunicipality: @json($farmerMunicipality) || @json($municipalities[0] ?? 'La Trinidad'),
                currentWeather: null,
                otherWeather: {},
                loadingCurrent: false,
                errorMessage: null,

                init() {
                    this.loadWeather(this.currentMunicipality);
                    // Load neighboring municipalities in background
                    this.municipalities.filter(m => m !== this.currentMunicipality).slice(0, 3).forEach((m, i) => {
                        setTimeout(() => this.loadOther(m), (i + 1) * 500);
                    });
                },

                async loadWeather(municipality) {
                    this.loadingCurrent = true;
                    this.errorMessage = null;

                    try {
                        const params = new URLSearchParams({ municipality });
                        const response = await fetch(`/api/weather/current?${params}`);
                        const data = await response.json();

                        if (!response.ok || !data.success) {
                            throw new Error(data.message || 'Failed to load weather');
                        }

                        this.currentWeather = data.weather;
                        this.currentMunicipality = municipality;
                    } catch (error) {
                        this.errorMessage = error.message || 'Unable to load weather data';
                    } finally {
                        this.loadingCurrent = false;
                    }
                },

                async loadOther(municipality) {
                    try {
                        const params = new URLSearchParams({ municipality });
                        const response = await fetch(`/api/weather/current?${params}`);
                        const data = await response.json();
                        if (response.ok && data.success) {
                            this.otherWeather[municipality] = data.weather;
                        }
                    } catch (e) {
                        // Silently fail for background loads
                    }
                },

                switchMunicipality(municipality) {
                    if (this.otherWeather[municipality]) {
                        // Save current to other
                        if (this.currentWeather) {
                            this.otherWeather[this.currentMunicipality] = this.currentWeather;
                        }
                        this.currentWeather = this.otherWeather[municipality];
                        this.currentMunicipality = municipality;
                        delete this.otherWeather[municipality];
                    } else {
                        this.loadWeather(municipality);
                    }
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                },

                refreshCurrent() {
                    this.loadWeather(this.currentMunicipality);
                },

                getFarmingTips() {
                    const w = this.currentWeather;
                    if (!w) return [];

                    const tips = [];
                    const rain = w.precipitation_probability_percent || 0;
                    const humidity = w.humidity_percent || 0;
                    const uv = w.uv_index || 0;
                    const windSpeed = w.wind?.speed?.value || 0;
                    const temp = w.temperature?.value || 0;

                    if (rain > 60) {
                        tips.push('High chance of rain — postpone pesticide spraying and fertilizer application.');
                        tips.push('Ensure proper drainage in your fields to prevent waterlogging.');
                    } else if (rain > 30) {
                        tips.push('Moderate rain expected — good time for transplanting seedlings.');
                    } else {
                        tips.push('Low rain probability — check irrigation and water your crops if needed.');
                    }

                    if (humidity > 80) {
                        tips.push('High humidity increases risk of fungal diseases — inspect crops for early signs.');
                    }

                    if (uv > 7) {
                        tips.push('High UV levels — avoid prolonged field work between 10 AM and 3 PM. Wear protective gear.');
                    }

                    if (windSpeed > 25) {
                        tips.push('Strong winds — secure crop supports and avoid spraying chemicals.');
                    }

                    if (temp > 30) {
                        tips.push('Hot temperatures — apply mulch to retain soil moisture and shade young plants.');
                    } else if (temp < 15) {
                        tips.push('Cool weather — protect frost-sensitive crops with covers overnight.');
                    }

                    if (tips.length === 0) {
                        tips.push('Good weather conditions for general farming activities.');
                    }

                    return tips;
                }
            };
        }
    </script>
    @endpush
</x-farmer-layout>
