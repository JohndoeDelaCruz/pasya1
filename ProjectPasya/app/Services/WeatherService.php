<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class WeatherService
{
    private $apiKey;
    private $apiProvider;
    private $baseUrl;

    public function __construct()
    {
        // Support multiple weather API providers
        // Option 1: OpenWeatherMap (https://openweathermap.org/api)
        // Option 2: WeatherAPI.com (https://www.weatherapi.com/)
        // Option 3: Google Weather API (https://weather.googleapis.com/)
        
        $this->apiProvider = env('WEATHER_API_PROVIDER', 'google'); // 'openweather', 'weatherapi', or 'google'
        
        if ($this->apiProvider === 'google') {
            $this->apiKey = env('GOOGLE_WEATHER_API_KEY', 'AIzaSyApL1FMpz-YmofnouGJStne7oPv09Ah7jM');
            $this->baseUrl = 'https://weather.googleapis.com/v1';
        } elseif ($this->apiProvider === 'weatherapi') {
            $this->apiKey = env('WEATHERAPI_KEY', 'demo');
            $this->baseUrl = 'https://api.weatherapi.com/v1';
        } else {
            $this->apiKey = env('OPENWEATHER_API_KEY', 'demo');
            $this->baseUrl = 'https://api.openweathermap.org/data/2.5';
        }
    }

    /**
     * Get weather forecast for a municipality
     */
    public function getForecast($municipality, $days = 4)
    {
        // Cache the weather data for 1 hour to avoid excessive API calls
        $cacheKey = "weather_forecast_{$municipality}_{$days}_{$this->apiProvider}";
        
        return Cache::remember($cacheKey, 3600, function () use ($municipality, $days) {
            try {
                if ($this->apiProvider === 'google') {
                    return $this->getGoogleWeatherForecast($municipality, $days);
                } elseif ($this->apiProvider === 'weatherapi') {
                    return $this->getWeatherAPIForecast($municipality, $days);
                } else {
                    return $this->getOpenWeatherForecast($municipality, $days);
                }
            } catch (\Exception $e) {
                Log::warning("Weather API error for {$municipality}: " . $e->getMessage());
                return $this->getMockForecast($municipality, $days);
            }
        });
    }

    /**
     * Get hourly weather forecast
     */
    public function getHourlyForecast($municipality, $hours = 6)
    {
        $cacheKey = "weather_hourly_{$municipality}_{$hours}_{$this->apiProvider}";
        
        return Cache::remember($cacheKey, 3600, function () use ($municipality, $hours) {
            try {
                if ($this->apiProvider === 'google') {
                    return $this->getGoogleWeatherHourly($municipality, $hours);
                } elseif ($this->apiProvider === 'weatherapi') {
                    return $this->getWeatherAPIHourly($municipality, $hours);
                } else {
                    return $this->getOpenWeatherHourly($municipality, $hours);
                }
            } catch (\Exception $e) {
                Log::warning("Weather API error: " . $e->getMessage());
                return $this->getMockHourlyForecast($hours);
            }
        });
    }

    /**
     * Get forecast from WeatherAPI.com
     */
    private function getWeatherAPIForecast($municipality, $days)
    {
        $coordinates = $this->getCoordinates($municipality);
        
        if (!$coordinates || $this->apiKey === 'demo') {
            return $this->getMockForecast($municipality, $days);
        }

        $location = "{$coordinates['lat']},{$coordinates['lon']}";

        $response = Http::timeout(10)->get("{$this->baseUrl}/forecast.json", [
            'key' => $this->apiKey,
            'q' => $location,
            'days' => $days,
            'aqi' => 'yes', // Include Air Quality Index
            'alerts' => 'no'
        ]);

        if ($response->successful()) {
            return $this->formatWeatherAPIForecast($response->json(), $municipality);
        }

        return $this->getMockForecast($municipality, $days);
    }

    /**
     * Get forecast from OpenWeatherMap
     */
    private function getOpenWeatherForecast($municipality, $days)
    {
        $coordinates = $this->getCoordinates($municipality);
        
        if (!$coordinates || $this->apiKey === 'demo') {
            return $this->getMockForecast($municipality, $days);
        }

        // Fetch 5-day forecast
        $response = Http::timeout(10)->get("{$this->baseUrl}/forecast", [
            'lat' => $coordinates['lat'],
            'lon' => $coordinates['lon'],
            'appid' => $this->apiKey,
            'units' => 'metric',
            'cnt' => $days * 8 // 8 forecasts per day (3-hour intervals)
        ]);

        if ($response->successful()) {
            return $this->formatOpenWeatherForecast($response->json(), $municipality);
        }

        return $this->getMockForecast($municipality, $days);
    }

    /**
     * Get hourly forecast from WeatherAPI.com
     */
    private function getWeatherAPIHourly($municipality, $hours)
    {
        $coordinates = $this->getCoordinates($municipality);
        
        if (!$coordinates || $this->apiKey === 'demo') {
            return $this->getMockHourlyForecast($hours);
        }

        $location = "{$coordinates['lat']},{$coordinates['lon']}";

        $response = Http::timeout(10)->get("{$this->baseUrl}/forecast.json", [
            'key' => $this->apiKey,
            'q' => $location,
            'days' => 1,
            'aqi' => 'no'
        ]);

        if ($response->successful()) {
            $data = $response->json();
            return $this->formatWeatherAPIHourly($data);
        }

        return $this->getMockHourlyForecast($hours);
    }

    /**
     * Get hourly forecast from OpenWeatherMap
     */
    private function getOpenWeatherHourly($municipality, $hours)
    {
        $coordinates = $this->getCoordinates($municipality);
        
        if (!$coordinates || $this->apiKey === 'demo') {
            return $this->getMockHourlyForecast($hours);
        }

        $response = Http::timeout(10)->get("{$this->baseUrl}/forecast", [
            'lat' => $coordinates['lat'],
            'lon' => $coordinates['lon'],
            'appid' => $this->apiKey,
            'units' => 'metric',
            'cnt' => $hours
        ]);

        if ($response->successful()) {
            return $this->formatOpenWeatherHourly($response->json());
        }

        return $this->getMockHourlyForecast($hours);
    }

    /**
     * Get coordinates for Benguet municipalities
     * Using exact coordinates from Google Weather API
     */
    private function getCoordinates($municipality)
    {
        // Coordinates for Benguet municipalities (from Google Weather API)
        $coordinates = [
            'Atok' => ['lat' => 16.6274093, 'lon' => 120.7675527],
            'Bakun' => ['lat' => 16.8300411, 'lon' => 120.6830301],
            'Bokod' => ['lat' => 16.4908605, 'lon' => 120.8302587],
            'Buguias' => ['lat' => 16.7192014, 'lon' => 120.826902],
            'Itogon' => ['lat' => 16.3657698, 'lon' => 120.633172],
            'Kabayan' => ['lat' => 16.6239201, 'lon' => 120.8381884],
            'Kapangan' => ['lat' => 16.5761774, 'lon' => 120.6030069],
            'Kibungan' => ['lat' => 16.6937271, 'lon' => 120.6533943],
            'La Trinidad' => ['lat' => 16.4586825, 'lon' => 120.5812456],
            'Mankayan' => ['lat' => 16.8572602, 'lon' => 120.7933631],
            'Sablan' => ['lat' => 16.4966909, 'lon' => 120.4875959],
            'Tuba' => ['lat' => 16.3926636, 'lon' => 120.5612911],
            'Tublay' => ['lat' => 16.5145931, 'lon' => 120.6322972],
        ];

        return $coordinates[$municipality] ?? null;
    }

    /**
     * Get current weather conditions from Google Weather API
     */
    public function getCurrentConditions($municipality)
    {
        $cacheKey = "weather_current_{$municipality}_google";
        
        return Cache::remember($cacheKey, 1800, function () use ($municipality) {
            try {
                $coordinates = $this->getCoordinates($municipality);
                
                if (!$coordinates) {
                    return $this->getMockCurrentConditions($municipality);
                }

                $response = Http::timeout(10)->get("{$this->baseUrl}/currentConditions:lookup", [
                    'key' => $this->apiKey,
                    'location.latitude' => $coordinates['lat'],
                    'location.longitude' => $coordinates['lon']
                ]);

                if ($response->successful()) {
                    return $this->formatGoogleCurrentConditions($response->json(), $municipality);
                }

                Log::warning("Google Weather API error for {$municipality}: " . $response->body());
                return $this->getMockCurrentConditions($municipality);
            } catch (\Exception $e) {
                Log::warning("Google Weather API error for {$municipality}: " . $e->getMessage());
                return $this->getMockCurrentConditions($municipality);
            }
        });
    }

    /**
     * Get forecast from Google Weather API
     */
    private function getGoogleWeatherForecast($municipality, $days)
    {
        $coordinates = $this->getCoordinates($municipality);
        
        if (!$coordinates) {
            return $this->getMockForecast($municipality, $days);
        }

        // First get current conditions
        $currentResponse = Http::timeout(10)->get("{$this->baseUrl}/currentConditions:lookup", [
            'key' => $this->apiKey,
            'location.latitude' => $coordinates['lat'],
            'location.longitude' => $coordinates['lon']
        ]);

        // Try to get forecast data
        $forecastResponse = Http::timeout(10)->get("{$this->baseUrl}/forecast/days:lookup", [
            'key' => $this->apiKey,
            'location.latitude' => $coordinates['lat'],
            'location.longitude' => $coordinates['lon'],
            'days' => $days
        ]);

        if ($forecastResponse->successful()) {
            // Pass current conditions to use current temp for "Today"
            $currentData = $currentResponse->successful() ? $currentResponse->json() : null;
            return $this->formatGoogleWeatherForecast($forecastResponse->json(), $municipality, $days, $currentData);
        }

        // If forecast endpoint fails, use current conditions to build a partial forecast
        if ($currentResponse->successful()) {
            return $this->buildForecastFromCurrent($currentResponse->json(), $municipality, $days);
        }

        return $this->getMockForecast($municipality, $days);
    }

    /**
     * Get hourly forecast from Google Weather API
     */
    private function getGoogleWeatherHourly($municipality, $hours)
    {
        $coordinates = $this->getCoordinates($municipality);
        
        if (!$coordinates) {
            return $this->getMockHourlyForecast($hours);
        }

        // Try to get hourly forecast
        $response = Http::timeout(10)->get("{$this->baseUrl}/forecast/hours:lookup", [
            'key' => $this->apiKey,
            'location.latitude' => $coordinates['lat'],
            'location.longitude' => $coordinates['lon'],
            'hours' => $hours
        ]);

        if ($response->successful()) {
            return $this->formatGoogleWeatherHourly($response->json(), $hours);
        }

        // Fallback to current conditions
        $currentResponse = Http::timeout(10)->get("{$this->baseUrl}/currentConditions:lookup", [
            'key' => $this->apiKey,
            'location.latitude' => $coordinates['lat'],
            'location.longitude' => $coordinates['lon']
        ]);

        if ($currentResponse->successful()) {
            return $this->buildHourlyFromCurrent($currentResponse->json(), $hours);
        }

        return $this->getMockHourlyForecast($hours);
    }

    /**
     * Format Google Weather API current conditions
     */
    private function formatGoogleCurrentConditions($data, $municipality)
    {
        $temperature = isset($data['temperature']['degrees']) ? round($data['temperature']['degrees']) : 20;
        $humidity = isset($data['relativeHumidity']) ? $data['relativeHumidity'] : 75;
        $condition = isset($data['weatherCondition']['description']['text']) 
            ? $data['weatherCondition']['description']['text'] 
            : (isset($data['weatherCondition']) ? $this->mapGoogleCondition($data['weatherCondition']) : 'Partly Cloudy');
        $windSpeed = isset($data['wind']['speed']['value']) ? round($data['wind']['speed']['value']) : 10;
        $uvIndex = isset($data['uvIndex']) ? $data['uvIndex'] : 5;
        
        return [
            'municipality' => $municipality,
            'temperature' => $temperature,
            'humidity' => $humidity,
            'condition' => $this->simplifyCondition($condition),
            'icon' => $this->getWeatherIcon($condition),
            'wind_speed' => $windSpeed,
            'uv_index' => $uvIndex,
            'feels_like' => isset($data['feelsLikeTemperature']['degrees']) ? round($data['feelsLikeTemperature']['degrees']) : $temperature,
            'timestamp' => now()->toIso8601String()
        ];
    }

    /**
     * Format Google Weather API forecast
     */
    private function formatGoogleWeatherForecast($data, $municipality, $days, $currentData = null)
    {
        $forecast = [];
        $dayNames = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
        
        // Get current temperature from currentConditions API
        $currentTemp = null;
        $currentCondition = null;
        if ($currentData) {
            $currentTemp = isset($currentData['temperature']['degrees']) ? round($currentData['temperature']['degrees']) : null;
            $currentCondition = isset($currentData['weatherCondition']['description']['text']) 
                ? $currentData['weatherCondition']['description']['text'] 
                : null;
        }
        
        if (isset($data['forecastDays'])) {
            foreach ($data['forecastDays'] as $index => $day) {
                if ($index >= $days) break;

                // Parse displayDate from API response
                if (isset($day['displayDate'])) {
                    $year = $day['displayDate']['year'];
                    $month = $day['displayDate']['month'];
                    $dayNum = $day['displayDate']['day'];
                    $date = strtotime("{$year}-{$month}-{$dayNum}");
                } else {
                    $date = strtotime("+{$index} days");
                }
                
                $dayOfWeek = date('w', $date);
                $dayName = $index === 0 ? "Tod" : $dayNames[$dayOfWeek];
                
                // For today (index 0), use current temperature from currentConditions API
                // For future days, use max temperature from forecast
                if ($index === 0 && $currentTemp !== null) {
                    $temp = $currentTemp;
                    $condition = $currentCondition ?? 'Partly Cloudy';
                } else {
                    $temp = isset($day['maxTemperature']['degrees']) ? round($day['maxTemperature']['degrees']) : 25;
                    // Get weather condition from daytime forecast
                    $condition = 'Partly Cloudy';
                    if (isset($day['daytimeForecast']['weatherCondition']['description']['text'])) {
                        $condition = $day['daytimeForecast']['weatherCondition']['description']['text'];
                    } elseif (isset($day['daytimeForecast']['weatherCondition']['type'])) {
                        $condition = $this->mapGoogleCondition($day['daytimeForecast']['weatherCondition']['type']);
                    }
                }

                $forecast[] = [
                    'day' => $dayName,
                    'date' => date('M j', $date),
                    'icon' => $this->getWeatherIcon($condition),
                    'condition' => $this->simplifyCondition($condition),
                    'temp' => "{$temp}°C",
                    'aqi' => rand(60, 80) // AQI - would need separate API call for real data
                ];
            }
        }

        if (empty($forecast)) {
            return $this->getMockForecast($municipality, $days);
        }

        return [
            'municipality' => $municipality,
            'forecast' => $forecast
        ];
    }

    /**
     * Format Google Weather API hourly forecast
     */
    private function formatGoogleWeatherHourly($data, $hours)
    {
        $hourly = [];
        
        if (isset($data['forecastHours'])) {
            foreach ($data['forecastHours'] as $index => $hour) {
                if ($index >= $hours) break;

                // Parse time from displayDateTime
                if ($index === 0) {
                    $time = 'Now';
                } elseif (isset($hour['displayDateTime']['hours'])) {
                    $hourNum = $hour['displayDateTime']['hours'];
                    $ampm = $hourNum >= 12 ? 'PM' : 'AM';
                    $displayHour = $hourNum % 12;
                    if ($displayHour === 0) $displayHour = 12;
                    $time = $displayHour . $ampm;
                } else {
                    $time = date('gA', strtotime("+{$index} hours"));
                }
                
                // Get temperature
                $temp = isset($hour['temperature']['degrees']) ? round($hour['temperature']['degrees']) : 18;
                
                // Get condition from weatherCondition.description.text
                $condition = 'Partly Cloudy';
                if (isset($hour['weatherCondition']['description']['text'])) {
                    $condition = $hour['weatherCondition']['description']['text'];
                } elseif (isset($hour['weatherCondition']['type'])) {
                    $condition = $this->mapGoogleCondition($hour['weatherCondition']['type']);
                }

                $hourly[] = [
                    'time' => $time,
                    'icon' => $this->getWeatherIcon($condition),
                    'temp' => "{$temp}°",
                    'condition' => $condition
                ];
            }
        }

        if (empty($hourly)) {
            return $this->getMockHourlyForecast($hours);
        }

        return $hourly;
    }

    /**
     * Build forecast from current conditions when forecast endpoint unavailable
     */
    private function buildForecastFromCurrent($currentData, $municipality, $days)
    {
        $forecast = [];
        $dayNames = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
        
        $baseTemp = isset($currentData['temperature']['degrees']) ? round($currentData['temperature']['degrees']) : 20;
        $condition = isset($currentData['weatherCondition']['description']['text']) 
            ? $currentData['weatherCondition']['description']['text'] 
            : $this->mapGoogleCondition($currentData['weatherCondition'] ?? 'PARTLY_CLOUDY');

        for ($i = 0; $i < $days; $i++) {
            $date = strtotime("+{$i} days");
            $dayOfWeek = date('w', $date);
            $dayName = $i === 0 ? "Today({$dayNames[$dayOfWeek]})" : $dayNames[$dayOfWeek];
            
            // Add some variation for future days
            $tempMax = $baseTemp + rand(0, 3);
            
            $forecast[] = [
                'day' => $dayName,
                'date' => date('M j', $date),
                'icon' => $this->getWeatherIcon($condition),
                'condition' => $this->simplifyCondition($condition),
                'temp' => "{$tempMax}°C",
                'aqi' => rand(60, 75)
            ];
        }

        return [
            'municipality' => $municipality,
            'forecast' => $forecast
        ];
    }

    /**
     * Build hourly forecast from current conditions
     */
    private function buildHourlyFromCurrent($currentData, $hours)
    {
        $hourly = [];
        
        $baseTemp = isset($currentData['temperature']['degrees']) ? round($currentData['temperature']['degrees']) : 20;
        $condition = isset($currentData['weatherCondition']['description']['text']) 
            ? $currentData['weatherCondition']['description']['text'] 
            : $this->mapGoogleCondition($currentData['weatherCondition'] ?? 'PARTLY_CLOUDY');

        for ($i = 0; $i < $hours; $i++) {
            $time = $i === 0 ? 'Now' : date('gA', strtotime("+{$i} hours"));
            $temp = $baseTemp + rand(-2, 3); // Small temperature variation

            $hourly[] = [
                'time' => $time,
                'icon' => $this->getWeatherIcon($condition),
                'temp' => "{$temp}°"
            ];
        }

        return $hourly;
    }

    /**
     * Map Google Weather condition codes to readable text
     */
    private function mapGoogleCondition($condition)
    {
        if (is_array($condition)) {
            $condition = $condition['type'] ?? 'PARTLY_CLOUDY';
        }
        
        $conditions = [
            'CLEAR' => 'Clear',
            'MOSTLY_CLEAR' => 'Mostly Clear',
            'PARTLY_CLOUDY' => 'Partly Cloudy',
            'MOSTLY_CLOUDY' => 'Mostly Cloudy',
            'CLOUDY' => 'Cloudy',
            'OVERCAST' => 'Overcast',
            'LIGHT_RAIN' => 'Light Rain',
            'RAIN' => 'Rain',
            'HEAVY_RAIN' => 'Heavy Rain',
            'SHOWERS' => 'Showers',
            'THUNDERSTORM' => 'Thunderstorm',
            'LIGHT_SNOW' => 'Light Snow',
            'SNOW' => 'Snow',
            'HEAVY_SNOW' => 'Heavy Snow',
            'FOG' => 'Fog',
            'MIST' => 'Mist',
            'HAZE' => 'Haze',
            'WINDY' => 'Windy',
            'SUNNY' => 'Sunny',
        ];

        return $conditions[strtoupper($condition)] ?? ucfirst(strtolower(str_replace('_', ' ', $condition)));
    }

    /**
     * Get mock current conditions (fallback)
     */
    private function getMockCurrentConditions($municipality)
    {
        return [
            'municipality' => $municipality,
            'temperature' => rand(15, 25),
            'humidity' => rand(65, 85),
            'condition' => 'Partly Cloudy',
            'icon' => '⛅',
            'wind_speed' => rand(5, 15),
            'uv_index' => rand(3, 7),
            'feels_like' => rand(15, 25),
            'timestamp' => now()->toIso8601String()
        ];
    }

    /**
     * Format forecast data from WeatherAPI.com
     */
    private function formatWeatherAPIForecast($data, $municipality)
    {
        $forecast = [];
        $days = ['Today(Sun)', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
        
        foreach ($data['forecast']['forecastday'] as $index => $day) {
            $dayData = $day['day'];
            $tempMax = round($dayData['maxtemp_c']);
            $condition = $dayData['condition']['text'];
            
            // Get AQI if available
            $aqi = isset($day['day']['air_quality']) 
                ? round($day['day']['air_quality']['pm2_5']) 
                : rand(60, 75);

            $forecast[] = [
                'day' => $days[$index] ?? date('D', strtotime($day['date'])),
                'date' => date('M j', strtotime($day['date'])),
                'icon' => $this->getWeatherIcon($condition),
                'condition' => $this->simplifyCondition($condition),
                'temp' => "{$tempMax}°C",
                'aqi' => $aqi
            ];
        }

        return [
            'municipality' => $municipality,
            'forecast' => $forecast
        ];
    }

    /**
     * Format forecast data from OpenWeatherMap
     */
    private function formatOpenWeatherForecast($data, $municipality)
    {
        $forecast = [];
        $days = ['Today(Sun)', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
        $dayIndex = 0;

        foreach ($data['list'] as $index => $item) {
            // Take midday forecast for each day
            if ($index % 8 == 4) {
                $temp = round($item['main']['temp']);
                $tempMax = round($item['main']['temp_max']);
                $weatherMain = $item['weather'][0]['main'];
                $aqi = rand(60, 75); // Mock AQI data (OpenWeather requires separate API call)

                $forecast[] = [
                    'day' => $days[$dayIndex],
                    'date' => date('M j', strtotime($item['dt_txt'])),
                    'icon' => $this->getWeatherIcon($weatherMain),
                    'condition' => $weatherMain,
                    'temp' => "{$tempMax}°C",
                    'aqi' => $aqi
                ];

                $dayIndex++;
                if ($dayIndex >= 4) break;
            }
        }

        return [
            'municipality' => $municipality,
            'forecast' => $forecast
        ];
    }

    /**
     * Format hourly forecast from WeatherAPI.com
     */
    private function formatWeatherAPIHourly($data)
    {
        $hourly = [];
        $currentHour = date('H');
        $times = ['Now', '10AM', '11AM', '12PM', '1PM', '2PM'];
        
        if (isset($data['forecast']['forecastday'][0]['hour'])) {
            $hours = $data['forecast']['forecastday'][0]['hour'];
            
            for ($i = 0; $i < 6; $i++) {
                $hourIndex = ($currentHour + $i) % 24;
                
                if (isset($hours[$hourIndex])) {
                    $hourData = $hours[$hourIndex];
                    $temp = round($hourData['temp_c']);
                    $condition = $hourData['condition']['text'];

                    $hourly[] = [
                        'time' => $times[$i],
                        'icon' => $this->getWeatherIcon($condition),
                        'temp' => "{$temp}°"
                    ];
                }
            }
        }

        if (empty($hourly)) {
            return $this->getMockHourlyForecast(6);
        }

        return $hourly;
    }

    /**
     * Format hourly forecast from OpenWeatherMap
     */
    private function formatOpenWeatherHourly($data)
    {
        $hourly = [];
        $times = ['Now', '10AM', '11AM', '12PM', '1PM', '2PM'];

        foreach ($data['list'] as $index => $item) {
            if ($index >= 6) break;

            $temp = round($item['main']['temp']);
            $weatherMain = $item['weather'][0]['main'];

            $hourly[] = [
                'time' => $times[$index],
                'icon' => $this->getWeatherIcon($weatherMain),
                'temp' => "{$temp}°"
            ];
        }

        return $hourly;
    }

    /**
     * Simplify weather condition text
     */
    private function simplifyCondition($condition)
    {
        $condition = strtolower($condition);
        
        if (strpos($condition, 'sunny') !== false || strpos($condition, 'clear') !== false) {
            return 'Sunny';
        } elseif (strpos($condition, 'cloud') !== false || strpos($condition, 'overcast') !== false) {
            return 'Cloudy';
        } elseif (strpos($condition, 'rain') !== false || strpos($condition, 'drizzle') !== false) {
            return 'Heavy rain';
        } elseif (strpos($condition, 'thunder') !== false || strpos($condition, 'lightning') !== false) {
            return 'Lightning';
        } elseif (strpos($condition, 'snow') !== false) {
            return 'Snow';
        } elseif (strpos($condition, 'mist') !== false || strpos($condition, 'fog') !== false) {
            return 'Foggy';
        }
        
        return ucfirst($condition);
    }

    /**
     * Get weather icon emoji based on condition
     */
    private function getWeatherIcon($condition)
    {
        $condition = strtolower($condition);
        
        // Sunny/Clear conditions
        if (strpos($condition, 'sunny') !== false || strpos($condition, 'clear') !== false) {
            return '☀️';
        }
        
        // Cloudy conditions
        if (strpos($condition, 'partly cloudy') !== false || strpos($condition, 'partly') !== false) {
            return '⛅';
        }
        if (strpos($condition, 'cloud') !== false || strpos($condition, 'overcast') !== false) {
            return '☁️';
        }
        
        // Rain conditions
        if (strpos($condition, 'heavy rain') !== false || strpos($condition, 'torrential') !== false) {
            return '🌧️';
        }
        if (strpos($condition, 'rain') !== false || strpos($condition, 'drizzle') !== false) {
            return '🌦️';
        }
        
        // Storm conditions
        if (strpos($condition, 'thunder') !== false || strpos($condition, 'lightning') !== false) {
            return '⛈️';
        }
        
        // Other conditions
        if (strpos($condition, 'snow') !== false) {
            return '❄️';
        }
        if (strpos($condition, 'mist') !== false || strpos($condition, 'fog') !== false || strpos($condition, 'haze') !== false) {
            return '🌫️';
        }
        
        // Default icons for specific weather types
        $icons = [
            'clear' => '☀️',
            'clouds' => '☁️',
            'rain' => '🌧️',
            'drizzle' => '🌦️',
            'thunderstorm' => '⛈️',
            'snow' => '❄️',
            'mist' => '🌫️',
            'fog' => '🌫️',
            'haze' => '🌫️'
        ];

        foreach ($icons as $key => $icon) {
            if (strpos($condition, $key) !== false) {
                return $icon;
            }
        }

        return '⛅'; // Default fallback
    }

    /**
     * Get mock forecast data (fallback)
     */
    private function getMockForecast($municipality, $days = 4)
    {
        $forecast = [
            [
                'day' => 'Today(Sun)',
                'date' => 'Mar 6',
                'icon' => '☀️',
                'condition' => 'Sunny',
                'temp' => '20°C',
                'aqi' => 67
            ],
            [
                'day' => 'Mon',
                'date' => 'Mar 7',
                'icon' => '⛅',
                'condition' => 'Cloudy',
                'temp' => '22°C',
                'aqi' => 71
            ],
            [
                'day' => 'Tue',
                'date' => 'Mar 8',
                'icon' => '⛈️',
                'condition' => 'Lightning',
                'temp' => '20°C',
                'aqi' => 65
            ],
            [
                'day' => 'Wed',
                'date' => 'Mar 9',
                'icon' => '🌧️',
                'condition' => 'Heavy rain',
                'temp' => '21°C',
                'aqi' => 70
            ]
        ];

        return [
            'municipality' => $municipality,
            'forecast' => array_slice($forecast, 0, $days)
        ];
    }

    /**
     * Get mock hourly forecast (fallback)
     */
    private function getMockHourlyForecast($hours = 6)
    {
        $hourly = [
            ['time' => 'Now', 'icon' => '☁️', 'temp' => '18°'],
            ['time' => '10AM', 'icon' => '🌫️', 'temp' => '19°'],
            ['time' => '11AM', 'icon' => '⛅', 'temp' => '22°'],
            ['time' => '12PM', 'icon' => '☀️', 'temp' => '23°'],
            ['time' => '1PM', 'icon' => '☁️', 'temp' => '24°'],
            ['time' => '2PM', 'icon' => '🌧️', 'temp' => '24°']
        ];

        return array_slice($hourly, 0, $hours);
    }

    /**
     * Calculate optimal planting window based on weather
     */
    public function getOptimalPlantingWindow($hourlyForecast)
    {
        // Find hours without rain and with moderate temperature
        $optimalHours = [];
        
        foreach ($hourlyForecast as $hour) {
            if (!in_array($hour['icon'], ['🌧️', '⛈️', '🌩️'])) {
                $optimalHours[] = $hour['time'];
            }
        }

        if (count($optimalHours) >= 2) {
            return $optimalHours[0] . ' - ' . end($optimalHours);
        }

        return '8:00 AM - 10:24 AM'; // Default
    }

    /**
     * Calculate climate risk percentage
     */
    public function getClimateRisk($forecast)
    {
        $riskScore = 0;
        
        foreach ($forecast as $day) {
            // Increase risk for rain, storms, extreme temps
            if (in_array($day['icon'], ['🌧️', '⛈️', '🌩️'])) {
                $riskScore += 15;
            } elseif ($day['icon'] == '☁️') {
                $riskScore += 5;
            }
        }

        return min($riskScore, 100); // Cap at 100%
    }
}
