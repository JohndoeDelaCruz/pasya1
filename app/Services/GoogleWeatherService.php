<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GoogleWeatherService
{
    private int $timeout;
    private int $cacheTtl;
    private string $geoJsonPath;

    public function __construct()
    {
        $this->timeout = (int) config('services.google_weather.timeout', 15);
        $this->cacheTtl = 3600; // 1 hour — Open-Meteo is free/unlimited
        $this->geoJsonPath = public_path('data/benguet.geojson');
    }

    public function getCurrentConditions(string $municipality): array
    {
        $coordinates = $this->getMunicipalityCoordinates($municipality);
        if (!$coordinates) {
            return [
                'success' => false,
                'error_code' => 'missing_coordinates',
                'message' => 'No coordinates found for municipality: ' . $municipality,
            ];
        }

        $cacheKey = 'openmeteo_weather_' . strtolower($this->normalizeMunicipalityName($municipality));
        $cached = Cache::get($cacheKey);
        if (is_array($cached) && ($cached['success'] ?? false) === true) {
            return $cached;
        }

        return $this->getFromOpenMeteo($coordinates, $cacheKey);
    }

    private function getFromOpenMeteo(array $coordinates, string $cacheKey): array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->acceptJson()
                ->get('https://api.open-meteo.com/v1/forecast', [
                    'latitude'        => $coordinates['latitude'],
                    'longitude'       => $coordinates['longitude'],
                    'current'         => 'temperature_2m,relative_humidity_2m,apparent_temperature,weather_code,wind_speed_10m,wind_direction_10m,precipitation_probability,uv_index,cloud_cover,is_day',
                    'wind_speed_unit' => 'kmh',
                    'timezone'        => 'Asia/Manila',
                ]);

            if (!$response->successful()) {
                Log::error('Open-Meteo fallback also failed', [
                    'status' => $response->status(),
                    'municipality' => $coordinates['municipality'],
                ]);
                return [
                    'success' => false,
                    'error_code' => 'weather_api_error',
                    'message' => 'Weather data is temporarily unavailable.',
                ];
            }

            $payload = $response->json();
            $current = $payload['current'] ?? [];

            $tempValue = $current['temperature_2m'] ?? null;
            $feelsValue = $current['apparent_temperature'] ?? null;
            $windSpeed = $current['wind_speed_10m'] ?? null;
            $windDir = $current['wind_direction_10m'] ?? null;
            $wmoCode = (int) ($current['weather_code'] ?? 0);

            $result = [
                'success'  => true,
                'source'   => 'open-meteo',
                'location' => [
                    'municipality' => $coordinates['municipality'],
                    'latitude'     => $coordinates['latitude'],
                    'longitude'    => $coordinates['longitude'],
                ],
                'weather' => [
                    'observed_at'                        => $current['time'] ?? null,
                    'timezone'                           => $payload['timezone'] ?? 'Asia/Manila',
                    'is_daytime'                         => (bool) ($current['is_day'] ?? true),
                    'description'                        => $this->wmoCodeToDescription($wmoCode),
                    'condition_type'                     => (string) $wmoCode,
                    'icon_base_uri'                      => null,
                    'temperature'                        => $tempValue !== null ? [
                        'value'   => round((float) $tempValue, 1),
                        'unit'    => 'CELSIUS',
                        'display' => round((float) $tempValue, 1) . ' °C',
                    ] : null,
                    'feels_like'                         => $feelsValue !== null ? [
                        'value'   => round((float) $feelsValue, 1),
                        'unit'    => 'CELSIUS',
                        'display' => round((float) $feelsValue, 1) . ' °C',
                    ] : null,
                    'humidity_percent'                   => $current['relative_humidity_2m'] ?? null,
                    'precipitation_probability_percent'  => $current['precipitation_probability'] ?? null,
                    'thunderstorm_probability_percent'   => ($wmoCode >= 95) ? 80 : ($wmoCode >= 80 ? 20 : null),
                    'uv_index'                           => $current['uv_index'] ?? null,
                    'cloud_cover_percent'                => $current['cloud_cover'] ?? null,
                    'wind'                               => [
                        'speed'            => $windSpeed !== null ? [
                            'value'   => round((float) $windSpeed, 1),
                            'unit'    => 'KILOMETERS_PER_HOUR',
                            'display' => round((float) $windSpeed, 1) . ' km/h',
                        ] : null,
                        'gust'             => null,
                        'direction'        => $windDir !== null ? $this->degreesToCardinal((float) $windDir) : null,
                        'direction_degrees' => $windDir !== null ? (float) $windDir : null,
                    ],
                ],
            ];

            Cache::put($cacheKey, $result, $this->cacheTtl);

            return $result;
        } catch (\Throwable $e) {
            Log::error('Open-Meteo fetch exception', [
                'municipality' => $coordinates['municipality'],
                'message'      => $e->getMessage(),
            ]);
            return [
                'success'    => false,
                'error_code' => 'weather_request_exception',
                'message'    => 'Unable to fetch weather data at this time.',
            ];
        }
    }

    private function wmoCodeToDescription(int $code): string
    {
        return match (true) {
            $code === 0        => 'Clear sky',
            $code <= 2         => 'Mostly clear',
            $code === 3        => 'Overcast',
            $code <= 48        => 'Foggy',
            $code <= 55        => 'Drizzle',
            $code <= 65        => 'Rain',
            $code <= 77        => 'Snow',
            $code <= 82        => 'Rain showers',
            $code <= 94        => 'Hail',
            $code <= 99        => 'Thunderstorm',
            default            => 'Unknown',
        };
    }

    private function degreesToCardinal(float $degrees): string
    {
        $dirs = ['N', 'NE', 'E', 'SE', 'S', 'SW', 'W', 'NW', 'N'];
        return $dirs[(int) round($degrees / 45) % 8];
    }


    private function getMunicipalityCoordinates(string $municipality): ?array
    {
        $coordinates = $this->loadCoordinateIndex();
        $normalized = $this->normalizeMunicipalityName($municipality);

        return $coordinates[$normalized] ?? null;
    }

    private function loadCoordinateIndex(): array
    {
        $cacheVersion = file_exists($this->geoJsonPath) ? filemtime($this->geoJsonPath) : 'v1';
        $cacheKey = 'municipality_coordinate_index_' . $cacheVersion;

        return Cache::remember($cacheKey, 86400, function () {
            if (!file_exists($this->geoJsonPath)) {
                Log::warning('Benguet geojson file not found for weather coordinates', [
                    'path' => $this->geoJsonPath,
                ]);
                return [];
            }

            $decoded = json_decode((string) file_get_contents($this->geoJsonPath), true);
            if (!is_array($decoded)) {
                Log::warning('Invalid benguet geojson content for weather coordinates');
                return [];
            }

            $features = data_get($decoded, 'features', []);
            $index = [];

            foreach ($features as $feature) {
                $name = (string) data_get($feature, 'properties.name', '');
                if ($name === '') {
                    continue;
                }

                $center = $this->extractCoordinateCenter((array) data_get($feature, 'geometry.coordinates', []));
                if (!$center) {
                    continue;
                }

                $index[$this->normalizeMunicipalityName($name)] = [
                    'municipality' => $name,
                    'latitude' => $center['latitude'],
                    'longitude' => $center['longitude'],
                ];
            }

            return $index;
        });
    }

    private function extractCoordinateCenter(array $coordinates): ?array
    {
        $pairs = $this->flattenCoordinatePairs($coordinates);
        if ($pairs === []) {
            return null;
        }

        $sumLat = 0.0;
        $sumLng = 0.0;

        foreach ($pairs as $pair) {
            $sumLat += $pair[0];
            $sumLng += $pair[1];
        }

        $count = count($pairs);

        return [
            'latitude' => round($sumLat / $count, 6),
            'longitude' => round($sumLng / $count, 6),
        ];
    }

    private function flattenCoordinatePairs(array $coordinates): array
    {
        $pairs = [];

        $walker = function (mixed $node) use (&$pairs, &$walker): void {
            if (!is_array($node)) {
                return;
            }

            if (
                count($node) >= 2
                && is_numeric($node[0])
                && is_numeric($node[1])
            ) {
                // GeoJSON stores [longitude, latitude].
                $pairs[] = [(float) $node[1], (float) $node[0]];
                return;
            }

            foreach ($node as $child) {
                $walker($child);
            }
        };

        $walker($coordinates);

        return $pairs;
    }

    private function normalizeMunicipalityName(string $municipality): string
    {
        $normalized = strtoupper(trim($municipality));
        $normalized = str_replace([' ', '.', ',', '-'], '', $normalized);

        return $normalized;
    }
}