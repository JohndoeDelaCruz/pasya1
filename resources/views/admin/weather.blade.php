<x-admin-layout>
    <x-slot name="title">Weather Monitoring</x-slot>

    @push('styles')
    <style>
        [x-cloak] { display: none !important; }
        .weather-card { transition: all 0.3s ease; }
        .weather-card:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(0,0,0,0.1); }
        .weather-icon { animation: fadeIn 0.5s ease; }
        @keyframes pulse-slow { 0%, 100% { opacity: 1; } 50% { opacity: 0.6; } }
        .pulse-slow { animation: pulse-slow 2s ease-in-out infinite; }
    </style>
    @endpush

    <div class="space-y-6" x-data="weatherDashboard()" x-init="init()">
        <!-- Page Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Weather Monitoring</h1>
                <p class="text-sm text-gray-500 mt-1">Real-time weather conditions across Benguet municipalities</p>
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
        <div x-show="selectedWeather" x-cloak x-transition class="bg-white rounded-xl shadow-lg overflow-hidden">
            <!-- Header gradient -->
            <div class="bg-gradient-to-r from-sky-500 to-blue-600 px-6 py-5 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-xl font-bold" x-text="selectedMunicipality"></h2>
                        <p class="text-sky-100 text-sm mt-1">
                            <span x-text="selectedWeather?.description || 'Loading...'"></span>
                            <span class="mx-2">&bull;</span>
                            <span x-text="selectedWeather?.is_daytime ? 'Daytime' : 'Nighttime'"></span>
                        </p>
                    </div>
                    <div class="text-right">
                        <div class="text-4xl font-bold" x-text="selectedWeather?.temperature?.display || '--'"></div>
                        <p class="text-sky-100 text-sm">Feels like <span x-text="selectedWeather?.feels_like?.display || '--'"></span></p>
                    </div>
                </div>
            </div>

            <!-- Weather Details Grid -->
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4 p-6">
                <!-- Humidity -->
                <div class="bg-blue-50 rounded-lg p-4 text-center">
                    <svg class="w-7 h-7 mx-auto text-blue-500 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3c-4 5-7 8-7 11a7 7 0 1014 0c0-3-3-6-7-11z"/>
                    </svg>
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Humidity</p>
                    <p class="text-lg font-bold text-blue-700" x-text="selectedWeather?.humidity_percent != null ? selectedWeather.humidity_percent + '%' : '--'"></p>
                </div>
                <!-- Rain Chance -->
                <div class="bg-sky-50 rounded-lg p-4 text-center">
                    <svg class="w-7 h-7 mx-auto text-sky-500 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z"/>
                    </svg>
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Rain Chance</p>
                    <p class="text-lg font-bold text-sky-700" x-text="selectedWeather?.precipitation_probability_percent != null ? selectedWeather.precipitation_probability_percent + '%' : '--'"></p>
                </div>
                <!-- Wind -->
                <div class="bg-teal-50 rounded-lg p-4 text-center">
                    <svg class="w-7 h-7 mx-auto text-teal-500 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5a2 2 0 012 2H3m14 4a2 2 0 01-2 2H3m16 4a2 2 0 00-2-2H3"/>
                    </svg>
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Wind</p>
                    <p class="text-lg font-bold text-teal-700" x-text="selectedWeather?.wind?.speed?.display || '--'"></p>
                    <p class="text-xs text-gray-500" x-text="selectedWeather?.wind?.direction ? selectedWeather.wind.direction.replace(/_/g, ' ') : ''"></p>
                </div>
                <!-- UV Index -->
                <div class="bg-amber-50 rounded-lg p-4 text-center">
                    <svg class="w-7 h-7 mx-auto text-amber-500 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                    <p class="text-xs text-gray-500 uppercase tracking-wide">UV Index</p>
                    <p class="text-lg font-bold text-amber-700" x-text="selectedWeather?.uv_index != null ? selectedWeather.uv_index : '--'"></p>
                </div>
                <!-- Cloud Cover -->
                <div class="bg-gray-50 rounded-lg p-4 text-center">
                    <svg class="w-7 h-7 mx-auto text-gray-500 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z"/>
                    </svg>
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Cloud Cover</p>
                    <p class="text-lg font-bold text-gray-700" x-text="selectedWeather?.cloud_cover_percent != null ? selectedWeather.cloud_cover_percent + '%' : '--'"></p>
                </div>
                <!-- Thunderstorm -->
                <div class="bg-purple-50 rounded-lg p-4 text-center">
                    <svg class="w-7 h-7 mx-auto text-purple-500 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Thunderstorm</p>
                    <p class="text-lg font-bold text-purple-700" x-text="selectedWeather?.thunderstorm_probability_percent != null ? selectedWeather.thunderstorm_probability_percent + '%' : '--'"></p>
                </div>
            </div>

            <div class="px-6 pb-4 flex items-center justify-between text-xs text-gray-400">
                <span x-text="selectedWeather?.observed_at ? 'Updated ' + new Date(selectedWeather.observed_at).toLocaleString() : ''"></span>
                <button @click="selectedWeather = null; selectedMunicipality = null" class="text-gray-400 hover:text-gray-600 transition">
                    <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    Close
                </button>
            </div>
        </div>

        <!-- Municipality Weather Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
            <template x-for="m in municipalities" :key="m">
                <div @click="selectMunicipality(m)"
                     class="weather-card bg-white rounded-xl shadow-md p-5 cursor-pointer border-2 transition-all"
                     :class="selectedMunicipality === m ? 'border-sky-500 ring-2 ring-sky-200' : 'border-transparent hover:border-sky-300'">
                    
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
                            <div class="flex items-center justify-between mb-3">
                                <h3 class="font-semibold text-gray-800 text-sm" x-text="m"></h3>
                                <span class="text-xs px-2 py-0.5 rounded-full"
                                      :class="weatherData[m].is_daytime ? 'bg-amber-100 text-amber-700' : 'bg-indigo-100 text-indigo-700'"
                                      x-text="weatherData[m].is_daytime ? '☀ Day' : '🌙 Night'"></span>
                            </div>
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-2xl font-bold text-gray-900" x-text="weatherData[m].temperature?.display || '--'"></p>
                                    <p class="text-xs text-gray-500 mt-1" x-text="weatherData[m].description || 'N/A'"></p>
                                </div>
                                <div class="text-right space-y-1">
                                    <p class="text-xs text-gray-500">
                                        <span class="inline-block w-3">💧</span>
                                        <span x-text="weatherData[m].humidity_percent != null ? weatherData[m].humidity_percent + '%' : '--'"></span>
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        <span class="inline-block w-3">🌧</span>
                                        <span x-text="weatherData[m].precipitation_probability_percent != null ? weatherData[m].precipitation_probability_percent + '%' : '--'"></span>
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        <span class="inline-block w-3">💨</span>
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
        <div x-show="Object.keys(weatherData).length > 0" x-cloak x-transition class="bg-white rounded-xl shadow-md p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M11 3a1 1 0 10-2 0v1a1 1 0 102 0V3zM15.657 5.757a1 1 0 00-1.414-1.414l-.707.707a1 1 0 001.414 1.414l.707-.707zM18 10a1 1 0 01-1 1h-1a1 1 0 110-2h1a1 1 0 011 1zM5.05 6.464A1 1 0 106.464 5.05l-.707-.707a1 1 0 00-1.414 1.414l.707.707zM5 10a1 1 0 01-1 1H3a1 1 0 110-2h1a1 1 0 011 1zM8 16v-1h4v1a2 2 0 11-4 0zM12 14c.015-.34.208-.646.477-.859a4 4 0 10-4.954 0c.27.213.462.519.476.859h4.002z"/>
                </svg>
                Agricultural Weather Advisory
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <template x-for="advisory in getAdvisories()" :key="advisory.title">
                    <div class="rounded-lg p-4 border" :class="advisory.colorClass">
                        <h4 class="font-semibold text-sm mb-1" x-text="advisory.title"></h4>
                        <p class="text-xs" x-text="advisory.message"></p>
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
