<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * ML API Service - Clean interface for ML API V2 (Productivity-First)
 * 
 * Features:
 * - Automatic caching for performance
 * - Error handling and retries
 * - Request logging
 * - Support for ML API V2 endpoints
 * - Returns productivity (MT/HA) as primary prediction target
 * 
 * Available Endpoints (V2):
 * - GET  /                 - API info and status
 * - POST /predict          - Single prediction (returns productivity_mt_ha, production_mt)
 * - POST /batch-predict    - Batch predictions
 * - GET  /crops            - Available crops
 * - GET  /municipalities   - Available municipalities
 * - GET  /model-info       - Model information and performance metrics
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
     * Get all available options (crops and municipalities from API, static values for others)
     * Note: /available-options endpoint removed in V2, combining crops and municipalities
     */
    public function getAvailableOptions(): array
    {
        return $this->cachedRequest('ml_available_options', 3600, function () {
            $crops = $this->get('/crops');
            $municipalities = $this->get('/municipalities');
            
            return [
                'success' => true,
                'values' => [
                    'crops' => $crops['crops'] ?? [],
                    'municipalities' => $municipalities['municipalities'] ?? [],
                    'months' => ['JAN', 'FEB', 'MAR', 'APR', 'MAY', 'JUN', 'JUL', 'AUG', 'SEP', 'OCT', 'NOV', 'DEC'],
                    'farm_types' => ['IRRIGATED', 'RAINFED'],
                    'years' => range(date('Y') - 10, date('Y') + 5)
                ]
            ];
        });
    }

    /**
     * Get forecast for specific crop and municipality
     * Note: /forecast endpoint removed in V2 - now uses predict with multiple months
     * 
     * @deprecated Use predict() with specific months for forecasting
     */
    public function getForecast(string $crop, string $municipality): array
    {
        // V2 doesn't have dedicated forecast endpoint
        // Generate forecast using predictions for next 12 months
        $forecasts = [];
        $months = ['JAN', 'FEB', 'MAR', 'APR', 'MAY', 'JUN', 'JUL', 'AUG', 'SEP', 'OCT', 'NOV', 'DEC'];
        $currentYear = (int) date('Y');
        
        foreach ($months as $month) {
            $prediction = $this->predict([
                'crop' => $crop,
                'municipality' => $municipality,
                'month' => $month,
                'year' => $currentYear,
                'farm_type' => 'IRRIGATED',
                'area_planted' => 1.0,
                'area_harvested' => 1.0
            ]);
            
            if (isset($prediction['success']) && $prediction['success']) {
                $forecasts[] = [
                    'month' => $month,
                    'productivity_mt_ha' => $prediction['prediction']['productivity_mt_ha'] ?? null,
                    'production_mt' => $prediction['prediction']['production_mt'] ?? null
                ];
            }
        }
        
        return [
            'success' => true,
            'crop' => $crop,
            'municipality' => $municipality,
            'forecasts' => $forecasts
        ];
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
     * Note: /production/history endpoint removed in V2 - use local Crop model instead
     * 
     * @deprecated Use App\Models\Crop for production history queries
     */
    public function getProductionHistory(array $filters = [], int $page = 1, int $perPage = 100): array
    {
        // V2 doesn't have this endpoint - return guidance to use local data
        Log::info('getProductionHistory: V2 API does not have this endpoint, use local Crop model');
        
        return [
            'success' => false,
            'error' => 'Production history endpoint not available in ML API V2. Use local database instead.',
            'suggestion' => 'Query App\\Models\\Crop directly for production history'
        ];
    }

    /**
     * Get ML API statistics and model info
     * Note: /statistics endpoint replaced with /model-info in V2
     */
    public function getStatistics(): array
    {
        return $this->cachedRequest('ml_statistics', 600, function () {
            return $this->get('/model-info');
        });
    }
    
    /**
     * Get detailed model information
     * NEW: V2 endpoint for model performance metrics
     */
    public function getModelInfo(): array
    {
        return $this->cachedRequest('ml_model_info', 600, function () {
            return $this->get('/model-info');
        });
    }

    /**
     * Clear ML API cache
     * Clear cache
     * Note: /cache/clear endpoint removed in V2 - only clears Laravel cache now
     */
    public function clearMLCache(): array
    {
        try {
            // V2 doesn't have cache clear endpoint - just clear Laravel cache
            $this->clearLaravelCache();

            return ['success' => true, 'message' => 'Laravel ML cache cleared'];
        } catch (\Exception $e) {
            Log::error('ML cache clear error', ['message' => $e->getMessage()]);
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
     * Uses prefixed keys for cache drivers that don't support tagging
     */
    private function cachedRequest(string $cacheKey, int $ttl, callable $callback): array
    {
        if (!$this->cacheEnabled) {
            return $callback();
        }

        // Use prefixed key instead of tags for compatibility with file/database cache
        $prefixedKey = 'ml_api_' . $cacheKey;
        
        return Cache::remember($prefixedKey, $ttl, $callback);
    }

    /**
     * Clear all Laravel ML API cache
     * Clears known cache keys since we can't use tags with file cache
     */
    public function clearLaravelCache(): void
    {
        $cacheKeys = [
            'ml_api_ml_crops',
            'ml_api_ml_municipalities',
            'ml_api_ml_available_options',
            'ml_api_ml_valid_values',
        ];
        
        foreach ($cacheKeys as $key) {
            Cache::forget($key);
        }
    }
}
