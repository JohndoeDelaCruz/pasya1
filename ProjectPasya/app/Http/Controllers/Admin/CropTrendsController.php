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
        
        // Get top crops and municipalities for predictions based on total production
        $topCrop = Crop::select('crop', DB::raw('SUM(production) as total_production'))
            ->groupBy('crop')
            ->orderByDesc('total_production')
            ->first();
            
        $topMunicipality = Crop::select('municipality', DB::raw('SUM(production) as total_production'))
            ->groupBy('municipality')
            ->orderByDesc('total_production')
            ->first();
        
        $topFarmType = Crop::select('farm_type', DB::raw('SUM(production) as total_production'))
            ->groupBy('farm_type')
            ->orderByDesc('total_production')
            ->first();
        
        // Get average area harvested for the specific crop/municipality/farm type combination
        $avgAreaHarvestedQuery = Crop::query();
        if ($topCrop) {
            $avgAreaHarvestedQuery->where('crop', $topCrop->crop);
        }
        if ($topMunicipality) {
            $avgAreaHarvestedQuery->where('municipality', $topMunicipality->municipality);
        }
        if ($topFarmType) {
            $avgAreaHarvestedQuery->where('farm_type', $topFarmType->farm_type);
        }
        $avgAreaHarvested = $avgAreaHarvestedQuery->avg('area_harvested') ?? 100;
        
        // Log selected criteria for debugging
        Log::info('Crop Trends Analysis Criteria', [
            'crop' => $topCrop->crop ?? 'N/A',
            'municipality' => $topMunicipality->municipality ?? 'N/A',
            'farm_type' => $topFarmType->farm_type ?? 'N/A',
            'avg_area_harvested' => $avgAreaHarvested,
            'current_year' => $currentYear
        ]);
        
        foreach ($months as $month) {
            // Historical data (average productivity from previous years)
            // Filter by same crop, municipality, and farm type for consistency
            $historicalQuery = Crop::where('year', '<', $currentYear)
                ->where('month', $month);
            
            if ($topCrop) {
                $historicalQuery->where('crop', $topCrop->crop);
            }
            if ($topMunicipality) {
                $historicalQuery->where('municipality', $topMunicipality->municipality);
            }
            if ($topFarmType) {
                $historicalQuery->where('farm_type', $topFarmType->farm_type);
            }
            
            $historical = $historicalQuery->avg('productivity');
            
            // Log historical data for verification
            Log::info('Historical Data Calculation', [
                'month' => $month,
                'crop' => $topCrop->crop ?? 'N/A',
                'municipality' => $topMunicipality->municipality ?? 'N/A',
                'farm_type' => $topFarmType->farm_type ?? 'N/A',
                'historical_productivity' => $historical,
                'record_count' => $historicalQuery->count(),
                'years_included' => $historicalQuery->pluck('year')->unique()->sort()->values()->toArray()
            ]);
            
            // Ensure we have a valid number, default to 0 if null or invalid
            $historicalValue = is_numeric($historical) && $historical > 0 ? round($historical, 2) : 0;
            $historicalYields[] = $historicalValue;
            
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
                    // Prediction returns production in MT
                    // Calculate productivity: production (MT) / area (ha) = productivity (MT/ha)
                    // This matches the database where productivity is stored in MT/ha
                    $productivity = $prediction['prediction']['production_mt'] / $avgAreaHarvested;
                    $predictedYields[] = round($productivity, 2);
                    
                    Log::info('Predicted Productivity Calculated', [
                        'month' => $month,
                        'production_mt' => $prediction['prediction']['production_mt'],
                        'area_harvested' => $avgAreaHarvested,
                        'productivity_mt_ha' => $productivity
                    ]);
                } else {
                    // Fallback to historical average if prediction fails
                    Log::warning('Prediction failed, using historical data', [
                        'month' => $month,
                        'error' => $prediction['error'] ?? 'Unknown'
                    ]);
                    $predictedYields[] = $historicalValue;
                }
            } else {
                // No data for prediction, use historical
                $predictedYields[] = $historicalValue;
            }
        }
        
        // Summary of Demand (monthly production)
        $demandData = [];
        $recordedData = [];
        
        Log::info('Starting Summary of Demand calculation', [
            'crop' => $topCrop->crop ?? 'N/A',
            'municipality' => $topMunicipality->municipality ?? 'N/A',
            'farm_type' => $topFarmType->farm_type ?? 'N/A',
            'avg_area_harvested' => $avgAreaHarvested
        ]);
        
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
                    $predictedProduction = round($prediction['prediction']['production_mt'], 2);
                    $demandData[] = $predictedProduction;
                    
                    Log::info('Demand Prediction for ' . $month, [
                        'production_mt' => $predictedProduction,
                        'source' => 'ML'
                    ]);
                    Log::info('Demand Prediction for ' . $month, [
                        'production_mt' => $predictedProduction,
                        'source' => 'ML'
                    ]);
                } else {
                    // Fallback: use historical average for same crop/municipality/farm_type
                    $avgProductionQuery = Crop::where('year', '<', $currentYear)
                        ->where('month', $month);
                    
                    if ($topCrop) {
                        $avgProductionQuery->where('crop', $topCrop->crop);
                    }
                    if ($topMunicipality) {
                        $avgProductionQuery->where('municipality', $topMunicipality->municipality);
                    }
                    if ($topFarmType) {
                        $avgProductionQuery->where('farm_type', $topFarmType->farm_type);
                    }
                    
                    $avgProduction = $avgProductionQuery->avg(DB::raw('production / 1000'));
                    $fallbackValue = $avgProduction ? round($avgProduction, 2) : 0;
                    $demandData[] = $fallbackValue;
                    
                    Log::info('Demand Fallback for ' . $month, [
                        'production_mt' => $fallbackValue,
                        'source' => 'Historical Average',
                        'record_count' => $avgProductionQuery->count()
                    ]);
                    Log::info('Demand Fallback for ' . $month, [
                        'production_mt' => $fallbackValue,
                        'source' => 'Historical Average',
                        'record_count' => $avgProductionQuery->count()
                    ]);
                }
            } else {
                // Fallback: use historical average for same crop/municipality/farm_type
                $avgProductionQuery = Crop::where('year', '<', $currentYear)
                    ->where('month', $month);
                
                if ($topCrop) {
                    $avgProductionQuery->where('crop', $topCrop->crop);
                }
                if ($topMunicipality) {
                    $avgProductionQuery->where('municipality', $topMunicipality->municipality);
                }
                if ($topFarmType) {
                    $avgProductionQuery->where('farm_type', $topFarmType->farm_type);
                }
                
                $avgProduction = $avgProductionQuery->avg(DB::raw('production / 1000'));
                $fallbackValue = $avgProduction ? round($avgProduction, 2) : 0;
                $demandData[] = $fallbackValue;
                
                Log::info('Demand No ML Data for ' . $month, [
                    'production_mt' => $fallbackValue,
                    'source' => 'Historical Average (No ML)',
                    'record_count' => $avgProductionQuery->count()
                ]);
            }
            
            // Recorded/actual data for current year (for same crop/municipality/farm_type)
            $recordedQuery = Crop::where('year', $currentYear)
                ->where('month', $month);
            
            if ($topCrop) {
                $recordedQuery->where('crop', $topCrop->crop);
            }
            if ($topMunicipality) {
                $recordedQuery->where('municipality', $topMunicipality->municipality);
            }
            if ($topFarmType) {
                $recordedQuery->where('farm_type', $topFarmType->farm_type);
            }
            
            $recorded = $recordedQuery->sum(DB::raw('production / 1000'));
            
            // Ensure valid number
            $recordedValue = is_numeric($recorded) && $recorded >= 0 ? round($recorded, 2) : 0;
            $recordedData[] = $recordedValue;
            
            // Log recorded data for verification
            Log::info('Recorded Data for ' . $month, [
                'production_mt' => $recordedValue,
                'record_count' => $recordedQuery->count()
            ]);
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
            'crops' => $crops,
            'selectedCrop' => $topCrop ? ucwords(strtolower($topCrop->crop)) : 'N/A',
            'selectedMunicipality' => $topMunicipality ? ucwords(strtolower($topMunicipality->municipality)) : 'N/A',
            'selectedFarmType' => $topFarmType ? ucwords(strtolower($topFarmType->farm_type)) : 'N/A',
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
                $historicalProduction = $historical ? $historical->production / 1000 : null; // Convert kg to MT
                
                // Log historical data found
                Log::info('Historical Data Query', [
                    'month' => $month,
                    'year' => $year,
                    'crop' => $request->crop,
                    'municipality' => $request->municipality,
                    'farm_type' => $request->farm_type,
                    'found' => $historical ? 'Yes' : 'No',
                    'productivity_mt_ha' => $historicalProductivity,
                    'production_mt' => $historicalProduction
                ]);

                // Get ML prediction with area_harvested parameter
                $confidenceScore = null;
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
                    // Calculate productivity in MT/ha (production_mt / area_ha)
                    $predictedProductivity = round($prediction['prediction']['production_mt'] / $avgAreaHarvested, 2);
                    // Capture confidence score from model_quality.r2_score (ML API returns R² as confidence)
                    $confidenceScore = null;
                    if (isset($prediction['model_quality']['r2_score'])) {
                        $confidenceScore = round($prediction['model_quality']['r2_score'], 4);
                    } elseif (isset($prediction['prediction']['confidence_score'])) {
                        $confidenceScore = round($prediction['prediction']['confidence_score'], 4);
                    } elseif (isset($prediction['prediction']['confidence'])) {
                        $confidenceScore = round($prediction['prediction']['confidence'], 4);
                    }
                    
                    Log::info('ML Prediction Success', [
                        'month' => $month,
                        'year' => $year,
                        'production_mt' => $predictedProduction,
                        'productivity_mt_ha' => $predictedProductivity,
                        'confidence' => $confidenceScore,
                        'area_used' => $avgAreaHarvested
                    ]);
                    Log::info('ML Prediction Success', [
                        'month' => $month,
                        'year' => $year,
                        'production_mt' => $predictedProduction,
                        'productivity_mt_ha' => $predictedProductivity,
                        'confidence' => $confidenceScore,
                        'area_used' => $avgAreaHarvested
                    ]);
                } else {
                    // ML prediction failed - use intelligent fallback
                    Log::warning('ML Prediction Failed - Using Intelligent Fallback', [
                        'month' => $month,
                        'year' => $year,
                        'error' => $prediction['error'] ?? 'Unknown error'
                    ]);
                    
                    // Strategy 1: If historical data exists for this exact period, use it
                    if ($historical) {
                        $predictedProductivity = $historicalProductivity;
                        $predictedProduction = $historicalProduction;
                        
                        Log::info('Fallback: Using Exact Historical Data', [
                            'month' => $month,
                            'year' => $year,
                            'productivity_mt_ha' => $predictedProductivity,
                            'production_mt' => $predictedProduction
                        ]);
                    } else {
                        // Strategy 2: Compute trend-adjusted average from recent years
                        $recentYears = Crop::where('crop', $request->crop)
                            ->where('municipality', $request->municipality)
                            ->where('farm_type', $request->farm_type)
                            ->where('month', $month)
                            ->where('year', '>=', $year - 5) // Last 5 years
                            ->where('year', '<', $year)
                            ->orderBy('year', 'desc')
                            ->select('year', 'productivity', DB::raw('production / 1000 as production_mt'))
                            ->get();
                        
                        if ($recentYears->count() >= 2) {
                            // Calculate trend (simple linear regression)
                            $avgProductivity = $recentYears->avg('productivity');
                            $avgProduction = $recentYears->avg('production_mt');
                            
                            // Apply 3% growth factor for future predictions (industry average)
                            if ($year > now()->year) {
                                $yearsAhead = $year - now()->year;
                                $growthFactor = pow(1.03, $yearsAhead);
                                $predictedProductivity = round($avgProductivity * $growthFactor, 2);
                                $predictedProduction = round($avgProduction * $growthFactor, 2);
                            } else {
                                $predictedProductivity = round($avgProductivity, 2);
                                $predictedProduction = round($avgProduction, 2);
                            }
                            
                            Log::info('Fallback: Using Trend-Adjusted Average', [
                                'month' => $month,
                                'year' => $year,
                                'productivity_mt_ha' => $predictedProductivity,
                                'production_mt' => $predictedProduction,
                                'records_used' => $recentYears->count(),
                                'years_used' => $recentYears->pluck('year')->toArray(),
                                'growth_applied' => $year > now()->year
                            ]);
                        } else {
                            // Strategy 3: Overall average (if not enough recent data)
                            $fallbackData = Crop::where('crop', $request->crop)
                                ->where('municipality', $request->municipality)
                                ->where('farm_type', $request->farm_type)
                                ->where('month', $month)
                                ->select(
                                    DB::raw('AVG(productivity) as avg_productivity'),
                                    DB::raw('AVG(production / 1000) as avg_production_mt')
                                )
                                ->first();
                            
                            if ($fallbackData && $fallbackData->avg_productivity) {
                                $predictedProductivity = round($fallbackData->avg_productivity, 2);
                                $predictedProduction = round($fallbackData->avg_production_mt, 2);
                                
                                Log::info('Fallback: Using Overall Average', [
                                    'month' => $month,
                                    'year' => $year,
                                    'productivity_mt_ha' => $predictedProductivity,
                                    'production_mt' => $predictedProduction,
                                    'total_records' => Crop::where('crop', $request->crop)
                                        ->where('municipality', $request->municipality)
                                        ->where('farm_type', $request->farm_type)
                                        ->where('month', $month)
                                        ->count()
                                ]);
                            } else {
                                Log::warning('No Historical Data Available', [
                                    'month' => $month,
                                    'year' => $year,
                                    'crop' => $request->crop,
                                    'municipality' => $request->municipality,
                                    'farm_type' => $request->farm_type
                                ]);
                            }
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
                    'confidence_score' => $confidenceScore ?? null,
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

        // Log summary of predictions
        Log::info('Prediction Results Summary', [
            'total_predictions' => count($predictions),
            'predictions_with_historical' => collect($predictions)->whereNotNull('historical_productivity')->count(),
            'predictions_with_ml' => collect($predictions)->whereNotNull('predicted_productivity')->count(),
            'predictions_with_confidence' => collect($predictions)->whereNotNull('confidence_score')->count(),
            'avg_confidence' => collect($predictions)->whereNotNull('confidence_score')->avg('confidence_score'),
            'historical_data_points' => collect($historicalData)->filter()->count(),
            'predicted_data_points' => collect($predictedData)->filter()->count()
        ]);

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
