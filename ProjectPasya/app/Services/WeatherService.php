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
        
        $this->apiProvider = env('WEATHER_API_PROVIDER', 'openweather'); // 'openweather' or 'weatherapi'
        
        if ($this->apiProvider === 'weatherapi') {
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
                if ($this->apiProvider === 'weatherapi') {
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
                if ($this->apiProvider === 'weatherapi') {
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
     */
    private function getCoordinates($municipality)
    {
        // Coordinates for Benguet municipalities
        $coordinates = [
            'Atok' => ['lat' => 16.5833, 'lon' => 120.7167],
            'Bakun' => ['lat' => 16.8000, 'lon' => 120.6667],
            'Bokod' => ['lat' => 16.4667, 'lon' => 120.8333],
            'Buguias' => ['lat' => 16.7333, 'lon' => 120.8333],
            'Itogon' => ['lat' => 16.3667, 'lon' => 120.7000],
            'Kabayan' => ['lat' => 16.7167, 'lon' => 120.8500],
            'Kapangan' => ['lat' => 16.5667, 'lon' => 120.6000],
            'Kibungan' => ['lat' => 16.6833, 'lon' => 120.6333],
            'La Trinidad' => ['lat' => 16.4603, 'lon' => 120.5900],
            'Mankayan' => ['lat' => 16.8667, 'lon' => 120.7833],
            'Sablan' => ['lat' => 16.4333, 'lon' => 120.5500],
            'Tuba' => ['lat' => 16.3167, 'lon' => 120.5500],
            'Tublay' => ['lat' => 16.5333, 'lon' => 120.6333],
        ];

        return $coordinates[$municipality] ?? null;
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
            $tempMin = round($dayData['mintemp_c']);
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
                'temp' => "{$tempMin}-{$tempMax}°C",
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
                $tempMin = round($item['main']['temp_min']);
                $tempMax = round($item['main']['temp_max']);
                $weatherMain = $item['weather'][0]['main'];
                $aqi = rand(60, 75); // Mock AQI data (OpenWeather requires separate API call)

                $forecast[] = [
                    'day' => $days[$dayIndex],
                    'date' => date('M j', strtotime($item['dt_txt'])),
                    'icon' => $this->getWeatherIcon($weatherMain),
                    'condition' => $weatherMain,
                    'temp' => "{$tempMin}-{$tempMax}°C",
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
                'temp' => '15-20°C',
                'aqi' => 67
            ],
            [
                'day' => 'Mon',
                'date' => 'Mar 7',
                'icon' => '⛅',
                'condition' => 'Cloudy',
                'temp' => '16-22°C',
                'aqi' => 71
            ],
            [
                'day' => 'Tue',
                'date' => 'Mar 8',
                'icon' => '⛈️',
                'condition' => 'Lightning',
                'temp' => '17-20°C',
                'aqi' => 65
            ],
            [
                'day' => 'Wed',
                'date' => 'Mar 9',
                'icon' => '🌧️',
                'condition' => 'Heavy rain',
                'temp' => '16-21°C',
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
