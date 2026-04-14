<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GoogleWeatherService
{
    private string $baseUrl;
    private ?string $apiKey;
    private string $unitsSystem;
    private int $timeout;
    private int $cacheTtl;
    private string $geoJsonPath;

    public function __construct()
    {
        $this->baseUrl = rtrim((string) config('services.google_weather.base_url', 'https://weather.googleapis.com/v1'), '/');
        $this->apiKey = config('services.google_weather.api_key')
            ? (string) config('services.google_weather.api_key')
            : null;
        $this->unitsSystem = strtoupper((string) config('services.google_weather.units', 'METRIC'));
        $this->timeout = (int) config('services.google_weather.timeout', 15);
        $this->cacheTtl = (int) config('services.google_weather.cache_ttl', 900);
        $this->geoJsonPath = public_path('data/benguet.geojson');
    }

    /**
     * Get current weather conditions for a municipality.
     */
    public function getCurrentConditions(string $municipality): array
    {
        if (blank($this->apiKey)) {
            return [
                'success' => false,
                'error_code' => 'not_configured',
                'message' => 'Google Weather API key is not configured.',
            ];
        }

        $coordinates = $this->getMunicipalityCoordinates($municipality);
        if (!$coordinates) {
            return [
                'success' => false,
                'error_code' => 'missing_coordinates',
                'message' => 'No coordinates found for municipality: ' . $municipality,
            ];
        }

        $cacheKey = sprintf(
            'google_weather_current_%s_%s',
            strtolower($this->normalizeMunicipalityName($municipality)),
            strtolower($this->unitsSystem)
        );

        $cached = Cache::get($cacheKey);
        if (is_array($cached) && ($cached['success'] ?? false) === true) {
            return $cached;
        }

        try {
            $response = Http::timeout($this->timeout)
                ->acceptJson()
                ->get($this->baseUrl . '/currentConditions:lookup', [
                    'key' => $this->apiKey,
                    'location.latitude' => $coordinates['latitude'],
                    'location.longitude' => $coordinates['longitude'],
                    'unitsSystem' => $this->unitsSystem,
                ]);

            if (!$response->successful()) {
                Log::warning('Google Weather API request failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'municipality' => $coordinates['municipality'],
                ]);

                return [
                    'success' => false,
                    'error_code' => 'weather_api_error',
                    'message' => 'Google Weather API request failed.',
                ];
            }

            $payload = $response->json();

            $result = [
                'success' => true,
                'source' => 'google-weather-api',
                'location' => [
                    'municipality' => $coordinates['municipality'],
                    'latitude' => $coordinates['latitude'],
                    'longitude' => $coordinates['longitude'],
                ],
                'weather' => $this->transformCurrentConditions($payload),
            ];

            Cache::put($cacheKey, $result, $this->cacheTtl);

            return $result;
        } catch (\Throwable $e) {
            Log::error('Google Weather API exception', [
                'municipality' => $municipality,
                'message' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error_code' => 'weather_request_exception',
                'message' => 'Unable to fetch weather data at this time.',
            ];
        }
    }

    private function transformCurrentConditions(array $payload): array
    {
        return [
            'observed_at' => data_get($payload, 'currentTime'),
            'timezone' => data_get($payload, 'timeZone.id'),
            'is_daytime' => (bool) data_get($payload, 'isDaytime', false),
            'description' => data_get($payload, 'weatherCondition.description.text'),
            'condition_type' => data_get($payload, 'weatherCondition.type'),
            'icon_base_uri' => data_get($payload, 'weatherCondition.iconBaseUri'),
            'temperature' => $this->formatMeasurement(
                data_get($payload, 'temperature.degrees'),
                data_get($payload, 'temperature.unit'),
                true
            ),
            'feels_like' => $this->formatMeasurement(
                data_get($payload, 'feelsLikeTemperature.degrees'),
                data_get($payload, 'feelsLikeTemperature.unit'),
                true
            ),
            'humidity_percent' => data_get($payload, 'relativeHumidity'),
            'precipitation_probability_percent' => data_get($payload, 'precipitation.probability.percent'),
            'thunderstorm_probability_percent' => data_get($payload, 'thunderstormProbability'),
            'uv_index' => data_get($payload, 'uvIndex'),
            'cloud_cover_percent' => data_get($payload, 'cloudCover'),
            'wind' => [
                'speed' => $this->formatMeasurement(
                    data_get($payload, 'wind.speed.value'),
                    data_get($payload, 'wind.speed.unit')
                ),
                'gust' => $this->formatMeasurement(
                    data_get($payload, 'wind.gust.value'),
                    data_get($payload, 'wind.gust.unit')
                ),
                'direction' => data_get($payload, 'wind.direction.cardinal'),
                'direction_degrees' => data_get($payload, 'wind.direction.degrees'),
            ],
        ];
    }

    private function formatMeasurement(mixed $value, ?string $unit, bool $isTemperature = false): ?array
    {
        if ($value === null || !is_numeric($value)) {
            return null;
        }

        $roundedValue = round((float) $value, 1);
        $displayUnit = $this->formatUnit($unit, $isTemperature);

        return [
            'value' => $roundedValue,
            'unit' => $unit,
            'display' => trim($roundedValue . ' ' . $displayUnit),
        ];
    }

    private function formatUnit(?string $unit, bool $isTemperature = false): string
    {
        if (!$unit) {
            return '';
        }

        return match ($unit) {
            'CELSIUS' => $isTemperature ? '°C' : 'C',
            'FAHRENHEIT' => $isTemperature ? '°F' : 'F',
            'KILOMETERS_PER_HOUR' => 'km/h',
            'MILES_PER_HOUR' => 'mph',
            default => str_replace('_', ' ', strtolower($unit)),
        };
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