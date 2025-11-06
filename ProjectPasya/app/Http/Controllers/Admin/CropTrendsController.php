<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Crop;
use App\Services\PredictionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CropTrendsController extends Controller
{
    protected $predictionService;

    public function __construct(PredictionService $predictionService)
    {
        $this->predictionService = $predictionService;
    }

    public function index()
    {
        // Get crop yield forecasting data (6 months)
        $currentYear = date('Y');
        $months = ['JAN', 'FEB', 'MAR', 'APR', 'MAY', 'JUN'];
        
        // Historical yields (previous years average)
        $historicalYields = [];
        $predictedYields = [];
        
        // Get top crops and municipalities for predictions
        $topCrop = Crop::select('crop', DB::raw('COUNT(*) as count'))
            ->groupBy('crop')
            ->orderByDesc('count')
            ->first();
            
        $topMunicipality = Crop::select('municipality', DB::raw('COUNT(*) as count'))
            ->groupBy('municipality')
            ->orderByDesc('count')
            ->first();
        
        $topFarmType = Crop::select('farm_type', DB::raw('COUNT(*) as count'))
            ->groupBy('farm_type')
            ->orderByDesc('count')
            ->first();
        
        // Get average area harvested for predictions
        $avgAreaHarvested = Crop::avg('area_harvested') ?? 100;
        
        foreach ($months as $month) {
            // Historical data (average productivity from previous years)
            $historical = Crop::where('year', '<', $currentYear)
                ->where('month', $month)
                ->avg('productivity');
            
            $historicalYields[] = $historical ? round($historical, 2) : 0;
            
            // Use ML prediction for current year
            if ($topCrop && $topMunicipality && $topFarmType) {
                $prediction = $this->predictionService->predictProduction([
                    'municipality' => $topMunicipality->municipality,
                    'farm_type' => $topFarmType->farm_type,
                    'month' => $month,
                    'crop' => $topCrop->crop,
                    'area_harvested' => $avgAreaHarvested
                ]);
                
                Log::info('Crop Trends Prediction', [
                    'month' => $month,
                    'prediction' => $prediction
                ]);
                
                if (isset($prediction['success']) && $prediction['success'] && isset($prediction['prediction']['production_mt'])) {
                    // Prediction returns production in MT, convert to kg then to productivity (kg/ha)
                    $productionKg = $prediction['prediction']['production_mt'] * 1000;
                    $productivity = $productionKg / $avgAreaHarvested;
                    $predictedYields[] = round($productivity, 2);
                } else {
                    // Fallback to historical average if prediction fails
                    Log::warning('Prediction failed, using historical data', [
                        'month' => $month,
                        'error' => $prediction['error'] ?? 'Unknown'
                    ]);
                    $predictedYields[] = $historical ? round($historical, 2) : 0;
                }
            } else {
                // No data for prediction, use historical
                $predictedYields[] = $historical ? round($historical, 2) : 0;
            }
        }
        
        // Summary of Demand (monthly production)
        $demandData = [];
        $recordedData = [];
        
        foreach ($months as $month) {
            // Use ML predictions for demand forecasting
            if ($topCrop && $topMunicipality && $topFarmType) {
                $prediction = $this->predictionService->predictProduction([
                    'municipality' => $topMunicipality->municipality,
                    'farm_type' => $topFarmType->farm_type,
                    'month' => $month,
                    'crop' => $topCrop->crop,
                    'area_harvested' => $avgAreaHarvested
                ]);
                
                if (isset($prediction['success']) && $prediction['success'] && isset($prediction['prediction']['production_mt'])) {
                    // Prediction returns production in MT
                    $demandData[] = round($prediction['prediction']['production_mt'], 2);
                } else {
                    // Fallback: use historical average
                    $avgProduction = Crop::where('month', $month)
                        ->avg(DB::raw('production / 1000'));
                    $demandData[] = $avgProduction ? round($avgProduction, 2) : 0;
                }
            } else {
                // Fallback: use historical average
                $avgProduction = Crop::where('month', $month)
                    ->avg(DB::raw('production / 1000'));
                $demandData[] = $avgProduction ? round($avgProduction, 2) : 0;
            }
            
            // Recorded/actual data for current year
            $recorded = Crop::where('year', $currentYear)
                ->where('month', $month)
                ->sum(DB::raw('production / 1000'));
            
            $recordedData[] = round($recorded, 2);
        }
        
        // Top 3 Most Productive Years
        $topYears = Crop::select('year', DB::raw('SUM(production) / 1000 as total_production'))
            ->groupBy('year')
            ->orderByDesc('total_production')
            ->limit(3)
            ->get()
            ->pluck('year')
            ->toArray();
        
        // Top 3 Most Productive Crops
        $topCrops = Crop::select('crop', DB::raw('SUM(production) / 1000 as total_production'))
            ->groupBy('crop')
            ->orderByDesc('total_production')
            ->limit(3)
            ->get()
            ->map(function ($crop) {
                return ucwords(strtolower($crop->crop));
            })
            ->toArray();
        
        // Check ML API health
        $mlApiHealthy = $this->predictionService->checkHealth();
        
        // Get all municipalities and crops for the prediction form
        $municipalities = Crop::distinct()->pluck('municipality')->sort()->values();
        $crops = Crop::distinct()->pluck('crop')->sort()->values();
        
        return view('admin.crop-trends', [
            'months' => $months,
            'historicalYields' => $historicalYields,
            'predictedYields' => $predictedYields,
            'demandData' => $demandData,
            'recordedData' => $recordedData,
            'topYears' => $topYears,
            'topCrops' => $topCrops,
            'currentYear' => $currentYear,
            'mlApiHealthy' => $mlApiHealthy,
            'municipalities' => $municipalities,
            'crops' => $crops
        ]);
    }

    public function predict(Request $request)
    {
        $request->validate([
            'municipality' => 'required|string',
            'farm_type' => 'required|in:Rainfed,Irrigated',
            'month_from' => 'required|string',
            'month_to' => 'required|string',
            'year_from' => 'required|integer|min:2000|max:2050',
            'year_to' => 'required|integer|min:2000|max:2050',
            'crop' => 'required|string'
        ]);

        // Log the request parameters for debugging
        Log::info('Prediction Request', [
            'municipality' => $request->municipality,
            'farm_type' => $request->farm_type,
            'crop' => $request->crop,
            'month_from' => $request->month_from,
            'month_to' => $request->month_to,
            'year_from' => $request->year_from,
            'year_to' => $request->year_to
        ]);

        // Define month order for range calculation
        $monthOrder = ['JAN', 'FEB', 'MAR', 'APR', 'MAY', 'JUN', 'JUL', 'AUG', 'SEP', 'OCT', 'NOV', 'DEC'];
        $monthFromIndex = array_search($request->month_from, $monthOrder);
        $monthToIndex = array_search($request->month_to, $monthOrder);
        
        // Generate months in range
        $months = [];
        if ($monthFromIndex !== false && $monthToIndex !== false) {
            if ($monthFromIndex <= $monthToIndex) {
                $months = array_slice($monthOrder, $monthFromIndex, $monthToIndex - $monthFromIndex + 1);
            } else {
                // Handle wrap around (e.g., NOV to FEB)
                $months = array_merge(
                    array_slice($monthOrder, $monthFromIndex),
                    array_slice($monthOrder, 0, $monthToIndex + 1)
                );
            }
        }

        // Generate years in range
        $years = range($request->year_from, $request->year_to);

        // Get predictions for each month-year combination
        $predictions = [];
        $avgAreaHarvested = Crop::where('crop', $request->crop)
            ->where('municipality', $request->municipality)
            ->where('farm_type', $request->farm_type)
            ->avg('area_harvested') ?? 100;

        foreach ($years as $year) {
            foreach ($months as $month) {
                // Get historical data for comparison
                $historical = Crop::where('municipality', $request->municipality)
                    ->where('farm_type', $request->farm_type)
                    ->where('crop', $request->crop)
                    ->where('month', $month)
                    ->where('year', $year)
                    ->first();

                $historicalProductivity = $historical ? $historical->productivity : null;
                $historicalProduction = $historical ? $historical->production / 1000 : null; // Convert to MT

                // Get ML prediction with area_harvested parameter
                $prediction = $this->predictionService->predictProduction([
                    'municipality' => $request->municipality,
                    'farm_type' => $request->farm_type,
                    'month' => $month,
                    'crop' => $request->crop,
                    'area_harvested' => $avgAreaHarvested
                ]);

                $predictedProductivity = null;
                $predictedProduction = null;

                if (isset($prediction['success']) && $prediction['success'] && isset($prediction['prediction']['production_mt'])) {
                    // Prediction returns production in MT
                    $predictedProduction = round($prediction['prediction']['production_mt'], 2);
                    // Calculate productivity: convert MT to kg, then divide by area
                    $predictedProductivity = round(($predictedProduction * 1000) / $avgAreaHarvested, 2);
                } else {
                    // Fallback to historical average if ML prediction fails
                    if ($historical) {
                        $predictedProductivity = $historicalProductivity;
                        $predictedProduction = $historicalProduction;
                    } else {
                        // Use overall average for this crop if no specific historical data
                        $fallbackData = Crop::where('crop', $request->crop)
                            ->where('municipality', $request->municipality)
                            ->where('farm_type', $request->farm_type)
                            ->where('month', $month)
                            ->select(DB::raw('AVG(productivity) as avg_productivity'), DB::raw('AVG(production) as avg_production'))
                            ->first();
                        
                        if ($fallbackData) {
                            $predictedProductivity = $fallbackData->avg_productivity;
                            $predictedProduction = $fallbackData->avg_production ? $fallbackData->avg_production / 1000 : null;
                        }
                    }
                }

                $predictions[] = [
                    'month' => $month,
                    'year' => $year,
                    'historical_productivity' => $historicalProductivity,
                    'predicted_productivity' => $predictedProductivity,
                    'historical_production' => $historicalProduction,
                    'predicted_production' => $predictedProduction,
                ];
            }
        }

        // Prepare chart data
        $chartLabels = [];
        $historicalData = [];
        $predictedData = [];
        
        foreach ($predictions as $pred) {
            $monthMap = [
                'JAN' => 'Jan', 'FEB' => 'Feb', 'MAR' => 'Mar', 'APR' => 'Apr',
                'MAY' => 'May', 'JUN' => 'Jun', 'JUL' => 'Jul', 'AUG' => 'Aug',
                'SEP' => 'Sep', 'OCT' => 'Oct', 'NOV' => 'Nov', 'DEC' => 'Dec'
            ];
            $chartLabels[] = ($monthMap[$pred['month']] ?? $pred['month']) . ' ' . $pred['year'];
            $historicalData[] = $pred['historical_productivity'];
            $predictedData[] = $pred['predicted_productivity'];
        }

        // Check ML API health
        $mlApiHealthy = $this->predictionService->checkHealth();

        // Get all municipalities and crops for the form
        $municipalities = Crop::distinct()->pluck('municipality')->sort()->values();
        $crops = Crop::distinct()->pluck('crop')->sort()->values();

        // Log summary of predictions
        Log::info('Prediction Results Generated', [
            'total_predictions' => count($predictions),
            'predictions_with_historical' => collect($predictions)->whereNotNull('historical_productivity')->count(),
            'predictions_with_ml' => collect($predictions)->whereNotNull('predicted_productivity')->count()
        ]);

        // Add debug log to verify view is being returned
        Log::info('Returning crop-trends-results view', [
            'predictions_count' => count($predictions),
            'chart_labels_count' => count($chartLabels)
        ]);

        return view('admin.crop-trends-results', [
            'predictions' => $predictions,
            'chartLabels' => $chartLabels,
            'historicalData' => $historicalData,
            'predictedData' => $predictedData,
            'filters' => $request->all(),
            'mlApiHealthy' => $mlApiHealthy,
            'municipalities' => $municipalities,
            'crops' => $crops
        ]);
    }
}
