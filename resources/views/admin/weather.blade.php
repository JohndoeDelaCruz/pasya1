<x-admin-layout>
    <x-slot name="title">Weather Monitoring</x-slot>

    @push('styles')
    <style>
        [x-cloak] { display: none !important; }
        .weather-card { transition: all 0.25s ease; }
        .weather-card:hover { transform: translateY(-3px); box-shadow: 0 10px 30px rgba(0,0,0,0.08); }
        .weather-stat { backdrop-filter: blur(8px); }
        .detail-grid-enter { animation: slideUp 0.35s ease-out; }
        @keyframes slideUp { from { opacity: 0; transform: translateY(12px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes pulse-slow { 0%, 100% { opacity: 1; } 50% { opacity: 0.6; } }
        .pulse-slow { animation: pulse-slow 2s ease-in-out infinite; }
    </style>
    @endpush

    <div class="space-y-6" x-data="weatherDashboard()" x-init="init()">
        <!-- Page Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-gray-800">Weather Monitoring</h1>
                <p class="text-xs sm:text-sm text-gray-500 mt-1">Real-time weather conditions across Benguet municipalities</p>
            </div>
            <div class="flex items-center gap-3">
                <button @click="refreshAll()" :disabled="loadingAll"
                    class="flex items-center gap-2 px-4 py-2 bg-sky-600 hover:bg-sky-700 disabled:bg-gray-400 text-white text-sm font-medium rounded-lg transition-colors">
                    <svg class="w-4 h-4" :class="loadingAll && 'animate-spin'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    <span x-text="loadingAll ? 'Refreshing...' : 'Refresh All'"></span>
                </button>
            </div>
        </div>

        <!-- Selected Municipality Detail Card -->
        <div x-show="selectedWeather" x-cloak x-transition class="detail-grid-enter bg-white rounded-2xl shadow-lg overflow-hidden border border-gray-100">
            <!-- Compact header with temp + stats in one row -->
            <div class="bg-gradient-to-br from-sky-500 via-blue-500 to-indigo-600 px-5 py-4 text-white">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center backdrop-blur-sm">
                            <span class="text-xl" x-text="selectedWeather?.is_daytime ? '☀️' : '🌙'"></span>
                        </div>
                        <div>
                            <h2 class="text-lg font-bold leading-tight" x-text="selectedMunicipality"></h2>
                            <p class="text-sky-100 text-xs mt-0.5">
                                <span x-text="selectedWeather?.description || 'Loading...'"></span>
                                <span class="mx-1 opacity-50">&bull;</span>
                                <span x-text="selectedWeather?.is_daytime ? 'Daytime' : 'Nighttime'"></span>
                            </p>
                        </div>
                    </div>
                    <div class="text-right flex items-baseline gap-1">
                        <span class="text-3xl font-extrabold tracking-tight" x-text="selectedWeather?.temperature?.display || '--'"></span>
                        <div class="text-xs text-sky-200 ml-1">
                            Feels <span x-text="selectedWeather?.feels_like?.display || '--'"></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Compact inline weather stats -->
            <div class="grid grid-cols-3 sm:grid-cols-6 gap-px bg-gray-100">
                <div class="bg-white px-3 sm:px-4 py-3 text-center">
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Humidity</p>
                    <p class="text-sm sm:text-base font-bold text-blue-600 mt-0.5" x-text="selectedWeather?.humidity_percent != null ? selectedWeather.humidity_percent + '%' : '--'"></p>
                </div>
                <div class="bg-white px-3 sm:px-4 py-3 text-center">
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Rain</p>
                    <p class="text-sm sm:text-base font-bold text-sky-600 mt-0.5" x-text="selectedWeather?.precipitation_probability_percent != null ? selectedWeather.precipitation_probability_percent + '%' : '--'"></p>
                </div>
                <div class="bg-white px-3 sm:px-4 py-3 text-center">
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Wind</p>
                    <p class="text-sm sm:text-base font-bold text-teal-600 mt-0.5" x-text="selectedWeather?.wind?.speed?.display || '--'"></p>
                </div>
                <div class="bg-white px-3 sm:px-4 py-3 text-center">
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">UV</p>
                    <p class="text-sm sm:text-base font-bold text-amber-600 mt-0.5" x-text="selectedWeather?.uv_index != null ? selectedWeather.uv_index : '--'"></p>
                </div>
                <div class="bg-white px-3 sm:px-4 py-3 text-center">
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Clouds</p>
                    <p class="text-sm sm:text-base font-bold text-gray-600 mt-0.5" x-text="selectedWeather?.cloud_cover_percent != null ? selectedWeather.cloud_cover_percent + '%' : '--'"></p>
                </div>
                <div class="bg-white px-3 sm:px-4 py-3 text-center">
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Thunder</p>
                    <p class="text-sm sm:text-base font-bold text-purple-600 mt-0.5" x-text="selectedWeather?.thunderstorm_probability_percent != null ? selectedWeather.thunderstorm_probability_percent + '%' : '--'"></p>
                </div>
            </div>

            <!-- Timestamp + close -->
            <div class="px-5 py-2 flex items-center justify-between border-t border-gray-100">
                <span class="text-[11px] text-gray-400" x-text="selectedWeather?.observed_at ? 'Updated ' + new Date(selectedWeather.observed_at).toLocaleString() : ''"></span>
                <button @click="selectedWeather = null; selectedMunicipality = null" class="text-xs text-gray-400 hover:text-gray-600 transition flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    Close
                </button>
            </div>
        </div>

        <!-- Municipality Weather Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3">
            <template x-for="m in municipalities" :key="m">
                <div @click="selectMunicipality(m)"
                     class="weather-card bg-white rounded-xl shadow-sm p-4 cursor-pointer border-2 transition-all"
                     :class="selectedMunicipality === m ? 'border-sky-500 ring-2 ring-sky-100 bg-sky-50/30' : 'border-gray-100 hover:border-sky-300 hover:shadow-md'">>
                    
                    <!-- Loading State -->
                    <template x-if="!weatherData[m] && loadingMunicipalities[m]">
                        <div class="text-center py-4">
                            <div class="w-8 h-8 border-2 border-sky-300 border-t-sky-600 rounded-full animate-spin mx-auto mb-2"></div>
                            <p class="text-sm text-gray-400">Loading...</p>
                        </div>
                    </template>

                    <!-- Error State -->
                    <template x-if="weatherErrors[m]">
                        <div class="text-center py-4">
                            <svg class="w-8 h-8 mx-auto text-red-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                            </svg>
                            <p class="text-xs text-red-400" x-text="weatherErrors[m]"></p>
                        </div>
                    </template>

                    <!-- Data State -->
                    <template x-if="weatherData[m] && !weatherErrors[m]">
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <h3 class="font-bold text-gray-800 text-sm" x-text="m"></h3>
                                <span class="text-[10px] px-2 py-0.5 rounded-full font-semibold"
                                      :class="weatherData[m].is_daytime ? 'bg-amber-100 text-amber-700' : 'bg-indigo-100 text-indigo-700'"
                                      x-text="weatherData[m].is_daytime ? '☀ Day' : '🌙 Night'"></span>
                            </div>
                            <div class="flex items-end justify-between">
                                <div>
                                    <p class="text-2xl font-extrabold text-gray-900 leading-none" x-text="weatherData[m].temperature?.display || '--'"></p>
                                    <p class="text-[11px] text-gray-500 mt-1.5 font-medium" x-text="weatherData[m].description || 'N/A'"></p>
                                </div>
                                <div class="text-right space-y-0.5">
                                    <p class="text-[11px] text-gray-500 font-medium">
                                        <span class="text-blue-500">💧</span>
                                        <span x-text="weatherData[m].humidity_percent != null ? weatherData[m].humidity_percent + '%' : '--'"></span>
                                    </p>
                                    <p class="text-[11px] text-gray-500 font-medium">
                                        <span class="text-sky-500">🌧</span>
                                        <span x-text="weatherData[m].precipitation_probability_percent != null ? weatherData[m].precipitation_probability_percent + '%' : '--'"></span>
                                    </p>
                                    <p class="text-[11px] text-gray-500 font-medium">
                                        <span class="text-teal-500">💨</span>
                                        <span x-text="weatherData[m].wind?.speed?.display || '--'"></span>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- Initial/Empty State -->
                    <template x-if="!weatherData[m] && !loadingMunicipalities[m] && !weatherErrors[m]">
                        <div class="text-center py-4">
                            <svg class="w-8 h-8 mx-auto text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z"/>
                            </svg>
                            <p class="text-sm font-medium text-gray-700" x-text="m"></p>
                            <p class="text-xs text-gray-400 mt-1">Click to load weather</p>
                        </div>
                    </template>
                </div>
            </template>
        </div>

        <!-- Agricultural Advisory -->
        <div x-show="Object.keys(weatherData).length > 0" x-cloak x-transition class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <h3 class="text-base font-bold text-gray-800 mb-3 flex items-center gap-2">
                <span class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                    <svg class="w-4.5 h-4.5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M11 3a1 1 0 10-2 0v1a1 1 0 102 0V3zM15.657 5.757a1 1 0 00-1.414-1.414l-.707.707a1 1 0 001.414 1.414l.707-.707zM18 10a1 1 0 01-1 1h-1a1 1 0 110-2h1a1 1 0 011 1zM5.05 6.464A1 1 0 106.464 5.05l-.707-.707a1 1 0 00-1.414 1.414l.707.707zM5 10a1 1 0 01-1 1H3a1 1 0 110-2h1a1 1 0 011 1zM8 16v-1h4v1a2 2 0 11-4 0zM12 14c.015-.34.208-.646.477-.859a4 4 0 10-4.954 0c.27.213.462.519.476.859h4.002z"/>
                    </svg>
                </span>
                Agricultural Weather Advisory
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                <template x-for="advisory in getAdvisories()" :key="advisory.title">
                    <div class="rounded-xl p-4 border" :class="advisory.colorClass">
                        <h4 class="font-semibold text-sm mb-1" x-text="advisory.title"></h4>
                        <p class="text-xs leading-relaxed" x-text="advisory.message"></p>
                    </div>
                </template>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function weatherDashboard() {
            return {
                municipalities: @json($municipalities),
                weatherData: {},
                weatherErrors: {},
                loadingMunicipalities: {},
                loadingAll: false,
                selectedMunicipality: null,
                selectedWeather: null,

                init() {
                    // Auto-load first 4 municipalities
                    this.municipalities.slice(0, 4).forEach((m, i) => {
                        setTimeout(() => this.loadWeather(m), i * 300);
                    });
                },

                async loadWeather(municipality) {
                    this.loadingMunicipalities[municipality] = true;
                    delete this.weatherErrors[municipality];

                    try {
                        const params = new URLSearchParams({ municipality });
                        const response = await fetch(`/api/weather/current?${params}`);
                        const data = await response.json();

                        if (!response.ok || !data.success) {
                            throw new Error(data.message || 'Failed to load weather');
                        }

                        this.weatherData[municipality] = data.weather;

                        if (this.selectedMunicipality === municipality) {
                            this.selectedWeather = data.weather;
                        }
                    } catch (error) {
                        this.weatherErrors[municipality] = error.message || 'Unable to load weather';
                    } finally {
                        this.loadingMunicipalities[municipality] = false;
                    }
                },

                selectMunicipality(municipality) {
                    this.selectedMunicipality = municipality;

                    if (this.weatherData[municipality]) {
                        this.selectedWeather = this.weatherData[municipality];
                    } else {
                        this.selectedWeather = null;
                        this.loadWeather(municipality);
                    }
                },

                async refreshAll() {
                    this.loadingAll = true;
                    const promises = this.municipalities.map((m, i) =>
                        new Promise(resolve => setTimeout(async () => {
                            await this.loadWeather(m);
                            resolve();
                        }, i * 200))
                    );
                    await Promise.all(promises);
                    this.loadingAll = false;
                },

                getAdvisories() {
                    const data = Object.values(this.weatherData);
                    if (data.length === 0) return [];

                    const advisories = [];
                    const avgRain = data.reduce((sum, w) => sum + (w.precipitation_probability_percent || 0), 0) / data.length;
                    const avgHumidity = data.reduce((sum, w) => sum + (w.humidity_percent || 0), 0) / data.length;
                    const maxWind = Math.max(...data.map(w => w.wind?.speed?.value || 0));

                    if (avgRain > 60) {
                        advisories.push({
                            title: '🌧 High Rainfall Expected',
                            message: `Average ${Math.round(avgRain)}% rain probability across monitored areas. Consider delaying field activities and ensuring drainage systems are clear.`,
                            colorClass: 'bg-blue-50 border-blue-200 text-blue-800'
                        });
                    } else if (avgRain > 30) {
                        advisories.push({
                            title: '🌦 Moderate Rain Chance',
                            message: `Average ${Math.round(avgRain)}% rain probability. Good conditions for planting, but monitor forecasts for sudden changes.`,
                            colorClass: 'bg-sky-50 border-sky-200 text-sky-800'
                        });
                    } else {
                        advisories.push({
                            title: '☀ Dry Conditions',
                            message: `Low rain probability (${Math.round(avgRain)}%). Ensure adequate irrigation for crops and monitor soil moisture levels.`,
                            colorClass: 'bg-amber-50 border-amber-200 text-amber-800'
                        });
                    }

                    if (avgHumidity > 80) {
                        advisories.push({
                            title: '💧 High Humidity Alert',
                            message: `Average humidity at ${Math.round(avgHumidity)}%. Watch for fungal diseases on crops — consider preventive fungicide application.`,
                            colorClass: 'bg-teal-50 border-teal-200 text-teal-800'
                        });
                    } else {
                        advisories.push({
                            title: '💧 Humidity Normal',
                            message: `Average humidity at ${Math.round(avgHumidity)}%. Good conditions for most agricultural activities.`,
                            colorClass: 'bg-green-50 border-green-200 text-green-800'
                        });
                    }

                    if (maxWind > 30) {
                        advisories.push({
                            title: '💨 Strong Winds',
                            message: `Wind speeds up to ${maxWind} km/h detected. Secure greenhouse structures and avoid spraying pesticides.`,
                            colorClass: 'bg-red-50 border-red-200 text-red-800'
                        });
                    } else {
                        advisories.push({
                            title: '💨 Calm Winds',
                            message: `Wind conditions are mild (max ${maxWind} km/h). Safe for spraying and field operations.`,
                            colorClass: 'bg-green-50 border-green-200 text-green-800'
                        });
                    }

                    return advisories;
                }
            };
        }
    </script>
    @endpush
</x-admin-layout>
