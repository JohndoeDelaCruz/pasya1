<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\PredictionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * ML Monitoring Controller
 * 
 * Provides monitoring and analytics for the scalable ML API:
 * - View ML API statistics and database metrics
 * - Query production history with filters
 * - Monitor prediction performance
 * - Manage cache
 */
class MLMonitoringController extends Controller
{
    protected $predictionService;

    public function __construct(PredictionService $predictionService)
    {
        $this->predictionService = $predictionService;
    }

    /**
     * Display ML API dashboard
     */
    public function index()
    {
        // Get ML API statistics
        $stats = $this->predictionService->getStatistics();
        
        // Check ML API health
        $health = $this->predictionService->checkHealth();
        
        return view('admin.ml-monitoring', compact('stats', 'health'));
    }

    /**
     * Get production history with filters
     */
    public function history(Request $request)
    {
        $filters = $request->only(['municipality', 'crop', 'year', 'month', 'farm_type']);
        $page = $request->get('page', 1);
        $perPage = $request->get('per_page', 50);
        
        $history = $this->predictionService->getProductionHistory($filters, $page, $perPage);
        
        if ($request->wantsJson()) {
            return response()->json($history);
        }
        
        return view('admin.ml-history', compact('history', 'filters'));
    }

    /**
     * Get ML API statistics (AJAX)
     */
    public function statistics()
    {
        $stats = $this->predictionService->getStatistics();
        return response()->json($stats);
    }

    /**
     * Clear ML API cache
     */
    public function clearCache()
    {
        try {
            $result = $this->predictionService->clearCache();
            
            return response()->json([
                'success' => true,
                'message' => 'Cache cleared successfully',
                'details' => $result
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to clear ML cache', ['error' => $e->getMessage()]);
            
            return response()->json([
                'success' => false,
                'error' => 'Failed to clear cache: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get ML API health status (AJAX)
     */
    public function health()
    {
        $health = $this->predictionService->checkHealth();
        
        return response()->json([
            'success' => $health,
            'status' => $health ? 'healthy' : 'unavailable',
            'timestamp' => now()->toIso8601String()
        ]);
    }
}
