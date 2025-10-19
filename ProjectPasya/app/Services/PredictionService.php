<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Crop Production Prediction Service
 * 
 * This service communicates with the Python Flask API to make crop production predictions
 * 
 * Usage in Laravel Controller:
 * 
 * use App\Services\PredictionService;
 * 
 * $predictionService = new PredictionService();
 * $result = $predictionService->predictProduction([
 *     'municipality' => 'ATOK',
 *     'farm_type' => 'IRRIGATED',
 *     'month' => 'JAN',
 *     'crop' => 'CABBAGE',
 *     'area_harvested' => 100.5
 * ]);
 */
class PredictionService
{
    protected $apiUrl;
    protected $timeout;

    public function __construct()
    {
        // Set API URL from .env or use default
        $this->apiUrl = env('PREDICTION_API_URL', 'http://localhost:5000');
        $this->timeout = env('PREDICTION_API_TIMEOUT', 30);
    }

    /**
     * Make a crop production prediction
     * 
     * @param array $data Input data containing:
     *                    - municipality: string
     *                    - farm_type: string
     *                    - month: string
     *                    - crop: string
     *                    - area_harvested: float
     * @return array|null Prediction result or null on failure
     */
    public function predictProduction(array $data)
    {
        try {
            $response = Http::timeout($this->timeout)
                ->post("{$this->apiUrl}/api/predict", $data);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Prediction API Error', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return [
                'success' => false,
                'error' => $response->json()['error'] ?? 'Unknown error occurred'
            ];

        } catch (\Exception $e) {
            Log::error('Prediction Service Exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'error' => 'Failed to connect to prediction service: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get valid categorical values from the API
     * 
     * @return array|null
     */
    public function getValidValues()
    {
        try {
            $response = Http::timeout($this->timeout)
                ->get("{$this->apiUrl}/api/valid-values");

            if ($response->successful()) {
                return $response->json();
            }

            return null;

        } catch (\Exception $e) {
            Log::error('Failed to fetch valid values', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Check if the prediction API is healthy
     * 
     * @return bool
     */
    public function checkHealth()
    {
        try {
            $response = Http::timeout(5)
                ->get("{$this->apiUrl}/api/health");

            return $response->successful() && 
                   isset($response->json()['success']) && 
                   $response->json()['success'] === true;

        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Predict production for multiple inputs
     * 
     * @param array $batchData Array of input data arrays
     * @return array Array of predictions
     */
    public function predictBatch(array $batchData)
    {
        $results = [];
        
        foreach ($batchData as $data) {
            $results[] = $this->predictProduction($data);
        }
        
        return $results;
    }
}