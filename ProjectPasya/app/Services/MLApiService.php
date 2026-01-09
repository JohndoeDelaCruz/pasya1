<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * ML API Service - Clean interface for scalable ML API
 * 
 * Features:
 * - Automatic caching for performance
 * - Error handling and retries
 * - Request logging
 * - Support for all ML API endpoints
 * - Database-backed predictions
 */
class MLApiService
{
    private string $baseUrl;
    private int $timeout;
    private bool $cacheEnabled;
    private int $cacheTtl;

    public function __construct()
    {
        $this->baseUrl = rtrim(env('ML_API_URL', 'http://127.0.0.1:5000'), '/');
        $this->timeout = (int) env('ML_API_TIMEOUT', 30);
        $this->cacheEnabled = (bool) env('ML_API_CACHE_ENABLED', true);
        $this->cacheTtl = (int) env('ML_API_CACHE_TTL', 300); // 5 minutes default
    }

    /**
     * Get available crops from ML API
     */
    public function getAvailableCrops(): array
    {
        return $this->cachedRequest('ml_crops', 3600, function () {
            return $this->get('/crops');
        });
    }

    /**
     * Get available municipalities from ML API
     */
    public function getAvailableMunicipalities(): array
    {
        return $this->cachedRequest('ml_municipalities', 3600, function () {
            return $this->get('/municipalities');
        });
    }

    /**
     * Get all available options (crops, municipalities, years, months, farm types)
     */
    public function getAvailableOptions(): array
    {
        return $this->cachedRequest('ml_available_options', 3600, function () {
            return $this->get('/available-options');
        });
    }

    /**
     * Get forecast for specific crop and municipality
     */
    public function getForecast(string $crop, string $municipality): array
    {
        $cacheKey = "ml_forecast_{$crop}_{$municipality}";
        
        return $this->cachedRequest($cacheKey, 3600, function () use ($crop, $municipality) {
            return $this->get("/forecast/{$crop}/{$municipality}");
        });
    }

    /**
     * Generate predictions based on input data
     * 
     * @param array $data Prediction input data
     * @return array Prediction results with confidence scores
     */
    public function predict(array $data): array
    {
        // Don't cache predictions - they should be real-time
        try {
            $response = Http::timeout($this->timeout)
                ->post("{$this->baseUrl}/predict", $data);

            if ($response->successful()) {
                return $response->json();
            }

            Log::warning('ML API prediction failed', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return [
                'success' => false,
                'error' => 'Prediction failed: ' . $response->status()
            ];
        } catch (\Exception $e) {
            Log::error('ML API prediction error', [
                'message' => $e->getMessage(),
                'data' => $data
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Batch predict multiple scenarios
     * 
     * @param array $predictions Array of prediction inputs
     * @return array Array of prediction results
     */
    public function batchPredict(array $predictions): array
    {
        try {
            $response = Http::timeout($this->timeout * 2) // Double timeout for batch
                ->post("{$this->baseUrl}/batch-predict", [
                    'predictions' => $predictions
                ]);

            if ($response->successful()) {
                return $response->json();
            }

            Log::warning('ML API batch prediction failed', [
                'status' => $response->status(),
                'count' => count($predictions)
            ]);

            return [
                'success' => false,
                'error' => 'Batch prediction failed: ' . $response->status()
            ];
        } catch (\Exception $e) {
            Log::error('ML API batch prediction error', [
                'message' => $e->getMessage(),
                'count' => count($predictions)
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get production history with filters
     * NEW: Uses database for fast queries
     * 
     * @param array $filters Filter parameters (municipality, crop, year, month, farm_type)
     * @param int $page Page number for pagination
     * @param int $perPage Items per page
     */
    public function getProductionHistory(array $filters = [], int $page = 1, int $perPage = 100): array
    {
        $queryParams = array_merge($filters, [
            'page' => $page,
            'per_page' => $perPage
        ]);

        try {
            $response = Http::timeout($this->timeout)
                ->get("{$this->baseUrl}/production/history", $queryParams);

            if ($response->successful()) {
                return $response->json();
            }

            return [
                'success' => false,
                'error' => 'Failed to fetch history: ' . $response->status()
            ];
        } catch (\Exception $e) {
            Log::error('ML API history error', [
                'message' => $e->getMessage(),
                'filters' => $filters
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get ML API statistics and analytics
     * NEW: Database statistics
     */
    public function getStatistics(): array
    {
        return $this->cachedRequest('ml_statistics', 600, function () {
            return $this->get('/statistics');
        });
    }

    /**
     * Clear ML API cache
     * NEW: Cache management endpoint
     */
    public function clearMLCache(): array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->post("{$this->baseUrl}/cache/clear");

            // Also clear Laravel cache
            Cache::tags(['ml_api'])->flush();

            return $response->successful() 
                ? $response->json() 
                : ['success' => false, 'error' => 'Failed to clear cache'];
        } catch (\Exception $e) {
            Log::error('ML API cache clear error', ['message' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Check ML API health status
     * Uses root endpoint which returns API info and status
     */
    public function checkHealth(): array
    {
        try {
            $response = Http::timeout(5)->get("{$this->baseUrl}/");
            
            return [
                'success' => $response->successful(),
                'status' => $response->status(),
                'data' => $response->successful() ? $response->json() : null
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Generic GET request with error handling
     */
    private function get(string $endpoint): array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->get("{$this->baseUrl}{$endpoint}");

            if ($response->successful()) {
                return $response->json();
            }

            Log::warning('ML API GET failed', [
                'endpoint' => $endpoint,
                'status' => $response->status()
            ]);

            return [
                'success' => false,
                'error' => 'Request failed: ' . $response->status()
            ];
        } catch (\Exception $e) {
            Log::error('ML API GET error', [
                'endpoint' => $endpoint,
                'message' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Cached request wrapper
     */
    private function cachedRequest(string $cacheKey, int $ttl, callable $callback): array
    {
        if (!$this->cacheEnabled) {
            return $callback();
        }

        return Cache::tags(['ml_api'])->remember($cacheKey, $ttl, $callback);
    }

    /**
     * Clear all Laravel ML API cache
     */
    public function clearLaravelCache(): void
    {
        Cache::tags(['ml_api'])->flush();
    }
}
