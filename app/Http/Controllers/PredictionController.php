<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\PredictionService;
use Illuminate\Http\JsonResponse;

/**
 * Example Laravel Controller for Crop Production Predictions
 * 
 * Installation:
 * 1. Copy PredictionService.php to app/Services/
 * 2. Copy this file to app/Http/Controllers/
 * 3. Add routes to routes/web.php or routes/api.php
 */
class PredictionController extends Controller
{
    protected $predictionService;

    public function __construct(PredictionService $predictionService)
    {
        $this->predictionService = $predictionService;
    }

    /**
     * Show prediction form (for web routes)
     */
    public function index()
    {
        // Get valid values for dropdowns
        $validValues = $this->predictionService->getValidValues();
        
        return view('prediction.index', [
            'validValues' => $validValues['values'] ?? []
        ]);
    }

    /**
     * Make a prediction
     * POST /api/predictions
     */
    public function predict(Request $request): JsonResponse
    {
        // Validate input
        $validated = $request->validate([
            'municipality' => 'required|string',
            'farm_type' => 'required|string',
            'month' => 'required|string',
            'crop' => 'required|string',
            'area_harvested' => 'required|numeric|min:0'
        ]);

        // Make prediction
        $result = $this->predictionService->predictProduction($validated);

        if ($result['success'] ?? false) {
            return response()->json($result);
        }

        return response()->json($result, 400);
    }

    /**
     * Get valid categorical values
     * GET /api/predictions/valid-values
     */
    public function getValidValues(): JsonResponse
    {
        $result = $this->predictionService->getValidValues();
        
        if ($result) {
            return response()->json($result);
        }

        return response()->json([
            'success' => false,
            'error' => 'Failed to fetch valid values'
        ], 500);
    }

    /**
     * Check API health
     * GET /api/predictions/health
     */
    public function healthCheck(): JsonResponse
    {
        $isHealthy = $this->predictionService->checkHealth();
        
        return response()->json([
            'success' => $isHealthy,
            'message' => $isHealthy 
                ? 'Prediction API is running' 
                : 'Prediction API is not available'
        ]);
    }

    /**
     * Batch predictions
     * POST /api/predictions/batch
     */
    public function predictBatch(Request $request): JsonResponse
    {
        // Validate batch input
        $validated = $request->validate([
            'predictions' => 'required|array',
            'predictions.*.municipality' => 'required|string',
            'predictions.*.farm_type' => 'required|string',
            'predictions.*.month' => 'required|string',
            'predictions.*.crop' => 'required|string',
            'predictions.*.area_harvested' => 'required|numeric|min:0'
        ]);

        $results = $this->predictionService->predictBatch($validated['predictions']);

        return response()->json([
            'success' => true,
            'predictions' => $results
        ]);
    }
}
