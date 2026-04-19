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
        
        // Cache predictions per month to avoid duplicate API calls
        $predictionCache = [];
        
        foreach ($months as $month) {
            // Historical data: average yearly production from previous years
            // First SUM production per year, then AVG those yearly totals
            // This correctly handles multiple records in the same year/month
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
            
            // SUM per year first, then average across years
            $yearlySums = (clone $historicalQuery)
                ->select('year', DB::raw('SUM(production) as yearly_production'))
                ->groupBy('year')
                ->pluck('yearly_production');
            
            $historical = $yearlySums->count() > 0 ? $yearlySums->avg() : null;
            
            // Log historical data for verification
            Log::info('Historical Production Calculation', [
                'month' => $month,
                'crop' => $topCrop->crop ?? 'N/A',
                'municipality' => $topMunicipality->municipality ?? 'N/A',
                'farm_type' => $topFarmType->farm_type ?? 'N/A',
                'historical_production_mt' => $historical,
                'years_count' => $yearlySums->count(),
                'yearly_sums' => $yearlySums->toArray()
            ]);
            
            // Ensure we have a valid number, default to 0 if null or invalid
            $historicalValue = is_numeric($historical) && $historical > 0 ? round($historical, 2) : 0;
            $historicalYields[] = $historicalValue;
            
            // Use ML prediction for current year (V2 API - Productivity-First)
            if ($topCrop && $topMunicipality && $topFarmType) {
                $prediction = $this->predictionService->predictProduction([
                    'municipality' => $topMunicipality->municipality,
                    'farm_type' => $topFarmType->farm_type,
                    'month' => $month,
                    'crop' => $topCrop->crop,
                    'area_harvested' => $avgAreaHarvested
                ]);
                
                // Cache prediction for reuse in demand section
                $predictionCache[$month] = $prediction;
                
                Log::info('Crop Trends Prediction (V2)', [
                    'month' => $month,
                    'prediction' => $prediction
                ]);
                
                if (isset($prediction['success']) && $prediction['success'] && isset($prediction['prediction']['production_mt'])) {
                    // V2 API returns production_mt directly
                    $predictedProduction = round($prediction['prediction']['production_mt'], 2);
                    $predictedYields[] = $predictedProduction;
                    
                    Log::info('Predicted Production (V2)', [
                        'month' => $month,
                        'production_mt' => $predictedProduction,
                        'productivity_mt_ha' => $prediction['prediction']['productivity_mt_ha'] ?? 'N/A',
                        'confidence' => $prediction['prediction']['confidence_score'] ?? 'N/A',
                        'area_used_for_prediction' => $avgAreaHarvested
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
        
        // Summary of Demand (monthly production) - reuse cached predictions
        $demandData = [];
        $recordedData = [];
        
        foreach ($months as $month) {
            // Reuse cached predictions from the yield section
            if (isset($predictionCache[$month])) {
                $prediction = $predictionCache[$month];
                
                if (isset($prediction['success']) && $prediction['success'] && isset($prediction['prediction']['production_mt'])) {
                    $predictedProduction = round($prediction['prediction']['production_mt'], 2);
                    $demandData[] = $predictedProduction;
                } else {
                    // Fallback: SUM per year then AVG across years
                    $demandData[] = $this->getHistoricalAverage($currentYear, $month, $topCrop, $topMunicipality, $topFarmType);
                }
            } else {
                // Fallback: SUM per year then AVG across years
                $demandData[] = $this->getHistoricalAverage($currentYear, $month, $topCrop, $topMunicipality, $topFarmType);
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
            
            $recorded = $recordedQuery->sum('production');
            
            // Ensure valid number
            $recordedValue = is_numeric($recorded) && $recorded >= 0 ? round($recorded, 2) : 0;
            $recordedData[] = $recordedValue;
            
            // Log recorded data for verification
            Log::info('Recorded Data for ' . $month, [
                'production_mt' => $recordedValue,
                'record_count' => $recordedQuery->count()
            ]);
        }
        
        // Top 3 Most Productive Years with production totals
        $topYearsWithProduction = Crop::select('year', DB::raw('SUM(production) as total_production'))
            ->groupBy('year')
            ->orderByDesc('total_production')
            ->limit(3)
            ->get()
            ->map(function ($item) {
                return [
                    'year' => $item->year,
                    'production' => round($item->total_production, 0)
                ];
            })
            ->toArray();
        
        // Keep simple array for backward compatibility
        $topYears = array_column($topYearsWithProduction, 'year');
        
        // Top 3 Most Productive Crops with production totals
        $topCropsWithProduction = Crop::select('crop', DB::raw('SUM(production) as total_production'))
            ->groupBy('crop')
            ->orderByDesc('total_production')
            ->limit(3)
            ->get()
            ->map(function ($item) {
                return [
                    'crop' => ucwords(strtolower($item->crop)),
                    'production' => round($item->total_production, 0)
                ];
            })
            ->toArray();
        
        // Keep simple array for backward compatibility
        $topCrops = array_column($topCropsWithProduction, 'crop');
        
        // Monthly production data - use historical averages filtered by the same top crop/municipality/farm_type
        $monthlyProductionData = [];
        foreach ($months as $month) {
            $monthlyQuery = Crop::where('month', $month);
            if ($topCrop) {
                $monthlyQuery->where('crop', $topCrop->crop);
            }
            if ($topMunicipality) {
                $monthlyQuery->where('municipality', $topMunicipality->municipality);
            }
            if ($topFarmType) {
                $monthlyQuery->where('farm_type', $topFarmType->farm_type);
            }
            // SUM per year, then average across years for correct aggregation
            $yearlySums = $monthlyQuery
                ->select('year', DB::raw('SUM(production) as yearly_production'))
                ->groupBy('year')
                ->pluck('yearly_production');
            $monthlyProductionData[] = $yearlySums->count() > 0 ? round($yearlySums->avg(), 2) : 0;
        }
        
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
            'monthlyProductionData' => $monthlyProductionData,
            'topYears' => $topYears,
            'topCrops' => $topCrops,
            'topYearsWithProduction' => $topYearsWithProduction,
            'topCropsWithProduction' => $topCropsWithProduction,
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
        // Large month/year ranges can trigger many API calls; allow more processing time.
        if (function_exists('set_time_limit')) {
            @set_time_limit(180);
        }

        $predictionStart = microtime(true);

        $request->validate([
            'municipality' => 'required|string',
            'farm_type' => 'required|string|in:Rainfed,Irrigated,RAINFED,IRRIGATED',
            'month_from' => 'required|string',
            'month_to' => 'required|string',
            'year_from' => 'required|integer|min:2000|max:2050',
            'year_to' => 'required|integer|min:2000|max:2050',
            'crop' => 'required|string'
        ]);

        $normalizedMunicipality = strtoupper(trim($request->municipality));
        $normalizedFarmType = $this->normalizeFarmType($request->farm_type);
        $normalizedCrop = strtoupper(trim($request->crop));
        $normalizedMonthFrom = $this->normalizeMonth($request->month_from);
        $normalizedMonthTo = $this->normalizeMonth($request->month_to);
        $strictMlMode = filter_var((string) config('services.ml_api.strict_mode', true), FILTER_VALIDATE_BOOLEAN);

        // Log the request parameters for debugging
        Log::info('Prediction Request', [
            'municipality' => $request->municipality,
            'farm_type' => $request->farm_type,
            'crop' => $request->crop,
            'month_from' => $request->month_from,
            'month_to' => $request->month_to,
            'year_from' => $request->year_from,
            'year_to' => $request->year_to,
            'normalized' => [
                'municipality' => $normalizedMunicipality,
                'farm_type' => $normalizedFarmType,
                'crop' => $normalizedCrop,
                'month_from' => $normalizedMonthFrom,
                'month_to' => $normalizedMonthTo,
            ],
            'strict_ml_mode' => $strictMlMode,
        ]);

        // Define month order for range calculation
        $monthOrder = ['JAN', 'FEB', 'MAR', 'APR', 'MAY', 'JUN', 'JUL', 'AUG', 'SEP', 'OCT', 'NOV', 'DEC'];
        $monthFromIndex = array_search($normalizedMonthFrom, $monthOrder, true);
        $monthToIndex = array_search($normalizedMonthTo, $monthOrder, true);
        
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
        
        // Get default area (average) for cases where no historical data exists
        $defaultAreaHarvested = Crop::where('crop', $normalizedCrop)
            ->where('municipality', $normalizedMunicipality)
            ->where('farm_type', $normalizedFarmType)
            ->avg('area_harvested') ?? 100;

        // Get the maximum year with actual historical data for this crop/municipality/farm_type
        $maxHistoricalYear = Crop::where('crop', $normalizedCrop)
            ->where('municipality', $normalizedMunicipality)
            ->where('farm_type', $normalizedFarmType)
            ->max('year');
        
        // Get seasonal average area for each month (to use for future predictions)
        // This captures natural planting patterns - e.g., more area planted in peak seasons
        $seasonalAverageAreasRaw = Crop::where('crop', $normalizedCrop)
            ->where('municipality', $normalizedMunicipality)
            ->where('farm_type', $normalizedFarmType)
            ->selectRaw('month, AVG(area_harvested) as avg_area')
            ->groupBy('month')
            ->pluck('avg_area', 'month')
            ->toArray();

        $seasonalAverageAreas = [];
        foreach ($seasonalAverageAreasRaw as $dbMonth => $avgArea) {
            $normalizedDbMonth = $this->normalizeMonth($dbMonth);
            if ($normalizedDbMonth !== null) {
                $seasonalAverageAreas[$normalizedDbMonth] = (float) $avgArea;
            }
        }
        
        Log::info('Max Historical Year', [
            'crop' => $normalizedCrop,
            'municipality' => $normalizedMunicipality,
            'farm_type' => $normalizedFarmType,
            'max_year' => $maxHistoricalYear,
            'default_area' => $defaultAreaHarvested,
            'seasonal_areas' => $seasonalAverageAreas
        ]);

        // Preload historical rows once to avoid N+1 queries inside the loop.
        $historicalRowsByPeriod = [];
        if (!empty($years) && !empty($months)) {
            $monthVariants = [];
            foreach ($months as $month) {
                $monthVariants = array_merge($monthVariants, $this->getMonthVariants($month));
            }
            $monthVariants = array_values(array_unique($monthVariants));

            $historicalRows = Crop::where('municipality', $normalizedMunicipality)
                ->where('farm_type', $normalizedFarmType)
                ->where('crop', $normalizedCrop)
                ->whereIn('month', $monthVariants)
                ->whereIn('year', $years)
                ->get(['year', 'month', 'productivity', 'production', 'area_harvested']);

            foreach ($historicalRows as $row) {
                $normalizedRowMonth = $this->normalizeMonth((string) $row->month);
                if (!$normalizedRowMonth) {
                    continue;
                }

                // Keep first row for compatibility with previous first() behavior.
                if (!isset($historicalRowsByPeriod[$row->year][$normalizedRowMonth])) {
                    $historicalRowsByPeriod[$row->year][$normalizedRowMonth] = $row;
                }
            }
        }

        $periodContexts = [];
        $batchInputs = [];

        foreach ($years as $year) {
            foreach ($months as $month) {
                $historical = $historicalRowsByPeriod[$year][$month] ?? null;
                $historicalProductivity = $historical ? $historical->productivity : null;
                $historicalProduction = $historical ? $historical->production : null;
                $historicalArea = $historical ? $historical->area_harvested : null;

                if ($historicalArea) {
                    $areaForPrediction = $historicalArea;
                } elseif (isset($seasonalAverageAreas[$month])) {
                    $areaForPrediction = $seasonalAverageAreas[$month];
                } else {
                    $areaForPrediction = $defaultAreaHarvested;
                }

                $periodContexts[] = [
                    'month' => $month,
                    'year' => $year,
                    'historical' => $historical,
                    'historical_productivity' => $historicalProductivity,
                    'historical_production' => $historicalProduction,
                    'historical_area' => $historicalArea,
                    'prediction_area' => (float) $areaForPrediction,
                ];

                $batchInputs[] = [
                    'municipality' => $normalizedMunicipality,
                    'farm_type' => $normalizedFarmType,
                    'month' => $month,
                    'crop' => $normalizedCrop,
                    'area_harvested' => (float) $areaForPrediction,
                    'year' => $year,
                ];
            }
        }

        $batchResult = $this->predictionService->predictBatch($batchInputs);
        $batchPredictions = $this->normalizeBatchPredictionResults($batchResult, count($batchInputs));

        foreach ($periodContexts as $index => $context) {
                $month = $context['month'];
                $year = $context['year'];
                $historical = $context['historical'];
                $historicalProductivity = $context['historical_productivity'];
                $historicalProduction = $context['historical_production'];
                $historicalArea = $context['historical_area'];
                $areaForPrediction = $context['prediction_area'];

                Log::debug('Historical Data Context', [
                    'month' => $month,
                    'year' => $year,
                    'crop' => $normalizedCrop,
                    'municipality' => $normalizedMunicipality,
                    'farm_type' => $normalizedFarmType,
                    'found' => $historical ? 'Yes' : 'No',
                    'productivity_mt_ha' => $historicalProductivity,
                    'production_mt' => $historicalProduction,
                    'area_harvested' => $historicalArea
                ]);

                $confidenceScore = null;
                $prediction = $batchPredictions[$index] ?? [
                    'success' => false,
                    'error' => 'Missing batch prediction result',
                ];

                $predictedProductivity = null;
                $predictedProduction = null;
                $predictionSource = 'ml';
                $mlError = null;

                if (($prediction['success'] ?? false) === true && isset($prediction['production_mt']) && $prediction['production_mt'] !== null) {
                    $predictedProduction = round((float) $prediction['production_mt'], 2);

                    $predictedProductivity = isset($prediction['productivity_mt_ha']) && $prediction['productivity_mt_ha'] !== null
                        ? round((float) $prediction['productivity_mt_ha'], 2)
                        : ($areaForPrediction > 0 ? round($predictedProduction / $areaForPrediction, 2) : null);

                    if (isset($prediction['confidence_score']) && $prediction['confidence_score'] !== null) {
                        $confidenceScore = round((float) $prediction['confidence_score'], 2);
                    } elseif (isset($prediction['model_r2']) && $prediction['model_r2'] !== null) {
                        $confidenceScore = round(((float) $prediction['model_r2']) * 100, 2);
                    }

                    Log::debug('ML Prediction Success (Batch)', [
                        'month' => $month,
                        'year' => $year,
                        'production_mt' => $predictedProduction,
                        'productivity_mt_ha' => $predictedProductivity,
                        'confidence' => $confidenceScore,
                        'area_used' => $areaForPrediction,
                        'area_source' => $historicalArea ? 'historical' : 'default'
                    ]);
                } else {
                    // ML prediction failed - either mark unavailable (strict mode) or use fallback strategies.
                    $mlError = $prediction['error'] ?? 'Unknown error';
                    Log::warning('ML Prediction Failed', [
                        'month' => $month,
                        'year' => $year,
                        'error' => $mlError,
                        'strict_ml_mode' => $strictMlMode,
                    ]);

                    if ($strictMlMode) {
                        $predictionSource = 'ml_unavailable';
                        Log::warning('Strict ML mode active: prediction left unavailable', [
                            'month' => $month,
                            'year' => $year,
                            'crop' => $normalizedCrop,
                            'municipality' => $normalizedMunicipality,
                            'farm_type' => $normalizedFarmType,
                        ]);
                    } else {
                        // Strategy 1: If historical data exists for this exact period, use it.
                        if ($historical) {
                            $predictedProductivity = $historicalProductivity;
                            $predictedProduction = $historicalProduction;
                            $predictionSource = 'fallback_historical';

                            Log::info('Fallback: Using Exact Historical Data', [
                                'month' => $month,
                                'year' => $year,
                                'productivity_mt_ha' => $predictedProductivity,
                                'production_mt' => $predictedProduction
                            ]);
                        } else {
                            // Strategy 2: Compute trend-adjusted average from recent years.
                            $recentYears = Crop::where('crop', $normalizedCrop)
                                ->where('municipality', $normalizedMunicipality)
                                ->where('farm_type', $normalizedFarmType)
                                ->whereIn('month', $this->getMonthVariants($month))
                                ->where('year', '>=', $year - 5)
                                ->where('year', '<', $year)
                                ->orderBy('year', 'desc')
                                ->select('year', 'productivity', DB::raw('production as production_mt'))
                                ->get();

                            if ($recentYears->count() >= 2) {
                                $avgProductivity = $recentYears->avg('productivity');
                                $avgProduction = $recentYears->avg('production_mt');

                                if ($year > now()->year) {
                                    $yearsAhead = $year - now()->year;
                                    $growthFactor = pow(1.03, $yearsAhead);
                                    $predictedProductivity = round($avgProductivity * $growthFactor, 2);
                                    $predictedProduction = round($avgProduction * $growthFactor, 2);
                                } else {
                                    $predictedProductivity = round($avgProductivity, 2);
                                    $predictedProduction = round($avgProduction, 2);
                                }
                                $predictionSource = 'fallback_trend';

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
                                // Strategy 3: Overall average (if not enough recent data).
                                $fallbackData = Crop::where('crop', $normalizedCrop)
                                    ->where('municipality', $normalizedMunicipality)
                                    ->where('farm_type', $normalizedFarmType)
                                    ->whereIn('month', $this->getMonthVariants($month))
                                    ->select(
                                        DB::raw('AVG(productivity) as avg_productivity'),
                                        DB::raw('AVG(production) as avg_production_mt')
                                    )
                                    ->first();

                                if ($fallbackData && $fallbackData->avg_productivity) {
                                    $predictedProductivity = round($fallbackData->avg_productivity, 2);
                                    $predictedProduction = round($fallbackData->avg_production_mt, 2);
                                    $predictionSource = 'fallback_average';

                                    Log::info('Fallback: Using Overall Average', [
                                        'month' => $month,
                                        'year' => $year,
                                        'productivity_mt_ha' => $predictedProductivity,
                                        'production_mt' => $predictedProduction,
                                        'total_records' => Crop::where('crop', $normalizedCrop)
                                            ->where('municipality', $normalizedMunicipality)
                                            ->where('farm_type', $normalizedFarmType)
                                            ->whereIn('month', $this->getMonthVariants($month))
                                            ->count()
                                    ]);
                                } else {
                                    $predictionSource = 'fallback_none';
                                    Log::warning('No Historical Data Available', [
                                        'month' => $month,
                                        'year' => $year,
                                        'crop' => $normalizedCrop,
                                        'municipality' => $normalizedMunicipality,
                                        'farm_type' => $normalizedFarmType
                                    ]);
                                }
                            }
                        }
                    }
                }

                // Use actual historical production from the database instead of calculating
                // This ensures we display the exact production values from the source data
                // Previously: normalizedHistoricalProduction = historicalProductivity * avgAreaHarvested (incorrect)
                // Now: Use the actual production value from the database directly

                $predictions[] = [
                    'month' => $month,
                    'year' => $year,
                    'historical_productivity' => $historicalProductivity,
                    'predicted_productivity' => $predictedProductivity,
                    'historical_production' => $historicalProduction,
                    'predicted_production' => $predictedProduction,
                    'normalized_historical_production' => $historicalProduction, // Use actual production, not calculated
                    'historical_area' => $historicalArea,
                    'prediction_area' => round($areaForPrediction, 2),
                    'confidence_score' => $confidenceScore ?? null,
                    'prediction_source' => $predictionSource,
                    'ml_error' => $mlError,
                ];
        }

        // Prepare chart data - using Production (MT) instead of Productivity
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
            // Use normalized historical production for fair comparison with predicted production
            $historicalData[] = $pred['normalized_historical_production'];
            $predictedData[] = $pred['predicted_production'];
        }

        $sourceCounts = collect($predictions)
            ->pluck('prediction_source')
            ->filter()
            ->countBy()
            ->toArray();

        $mlBackedPredictions = collect($predictions)
            ->where('prediction_source', 'ml')
            ->count();

        // Log summary of predictions
        Log::info('Prediction Results Summary', [
            'total_predictions' => count($predictions),
            'predictions_with_historical' => collect($predictions)->whereNotNull('normalized_historical_production')->count(),
            'predictions_with_ml' => $mlBackedPredictions,
            'predictions_with_confidence' => collect($predictions)->whereNotNull('confidence_score')->count(),
            'avg_confidence' => collect($predictions)->whereNotNull('confidence_score')->avg('confidence_score'),
            'historical_data_points' => collect($historicalData)->filter()->count(),
            'predicted_data_points' => collect($predictedData)->filter()->count(),
            'source_breakdown' => $sourceCounts,
            'duration_ms' => round((microtime(true) - $predictionStart) * 1000, 2),
        ]);

        // Check ML API health
        $mlApiHealthy = $this->predictionService->checkHealth();

        // Get all municipalities and crops for the form
        $municipalities = Crop::distinct()->pluck('municipality')->sort()->values();
        $crops = Crop::distinct()->pluck('crop')->sort()->values();

        // Log summary of predictions
        Log::info('Prediction Results Generated', [
            'total_predictions' => count($predictions),
            'predictions_with_historical' => collect($predictions)->whereNotNull('normalized_historical_production')->count(),
            'predictions_with_ml' => $mlBackedPredictions,
            'source_breakdown' => $sourceCounts
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
            'strictMlMode' => $strictMlMode,
            'sourceCounts' => $sourceCounts,
            'mlBackedPredictions' => $mlBackedPredictions,
            'municipalities' => $municipalities,
            'crops' => $crops,
            'avgAreaHarvested' => round($defaultAreaHarvested, 2)
        ]);
    }

    /**
     * Normalize batch prediction responses into a fixed indexed structure.
     */
    private function normalizeBatchPredictionResults(array $batchResult, int $expectedCount): array
    {
        $items = [];

        if ($this->isSequentialArray($batchResult)) {
            $items = $batchResult;
        } else {
            foreach (['predictions', 'results', 'data', 'items'] as $key) {
                if (isset($batchResult[$key]) && is_array($batchResult[$key])) {
                    $items = $batchResult[$key];
                    break;
                }
            }

            if ($items === [] && isset($batchResult['prediction']) && is_array($batchResult['prediction'])) {
                $items = [$batchResult];
            }
        }

        if (!is_array($items)) {
            $items = [];
        }

        if (!$this->isSequentialArray($items)) {
            $items = array_values($items);
        }

        $normalized = [];

        for ($index = 0; $index < $expectedCount; $index++) {
            $item = $items[$index] ?? null;

            if (!is_array($item)) {
                $normalized[] = [
                    'success' => false,
                    'error' => 'Missing batch prediction result',
                ];
                continue;
            }

            $predictionNode = isset($item['prediction']) && is_array($item['prediction'])
                ? $item['prediction']
                : $item;

            $production = $predictionNode['production_mt'] ?? null;
            $productivity = $predictionNode['productivity_mt_ha'] ?? null;
            $confidence = $predictionNode['confidence_score'] ?? ($item['confidence_score'] ?? null);
            $modelR2 = data_get($item, 'model_info.r2_score');

            $isSuccess = (bool) ($item['success'] ?? (is_numeric($production) || is_numeric($productivity)));

            if ($isSuccess && (is_numeric($production) || is_numeric($productivity))) {
                $normalized[] = [
                    'success' => true,
                    'production_mt' => is_numeric($production) ? (float) $production : null,
                    'productivity_mt_ha' => is_numeric($productivity) ? (float) $productivity : null,
                    'confidence_score' => is_numeric($confidence) ? (float) $confidence : null,
                    'model_r2' => is_numeric($modelR2) ? (float) $modelR2 : null,
                ];
                continue;
            }

            $normalized[] = [
                'success' => false,
                'error' => $item['error'] ?? data_get($item, 'message', 'Invalid batch prediction response'),
            ];
        }

        return $normalized;
    }

    private function isSequentialArray(array $array): bool
    {
        return $array === [] || array_keys($array) === range(0, count($array) - 1);
    }

    private function normalizeFarmType(string $farmType): string
    {
        $normalized = strtoupper(trim($farmType));
        return str_replace(' ', '', $normalized);
    }

    private function normalizeMonth(string $month): ?string
    {
        $normalized = strtoupper(trim($month));

        $monthMap = [
            'JAN' => 'JAN',
            'JANUARY' => 'JAN',
            'FEB' => 'FEB',
            'FEBRUARY' => 'FEB',
            'MAR' => 'MAR',
            'MARCH' => 'MAR',
            'APR' => 'APR',
            'APRIL' => 'APR',
            'MAY' => 'MAY',
            'JUN' => 'JUN',
            'JUNE' => 'JUN',
            'JUL' => 'JUL',
            'JULY' => 'JUL',
            'AUG' => 'AUG',
            'AUGUST' => 'AUG',
            'SEP' => 'SEP',
            'SEPT' => 'SEP',
            'SEPTEMBER' => 'SEP',
            'OCT' => 'OCT',
            'OCTOBER' => 'OCT',
            'NOV' => 'NOV',
            'NOVEMBER' => 'NOV',
            'DEC' => 'DEC',
            'DECEMBER' => 'DEC',
        ];

        return $monthMap[$normalized] ?? null;
    }

    private function getMonthVariants(string $month): array
    {
        $normalized = $this->normalizeMonth($month);

        if ($normalized === null) {
            return [strtoupper(trim($month))];
        }

        $fullMonthNames = [
            'JAN' => 'JANUARY',
            'FEB' => 'FEBRUARY',
            'MAR' => 'MARCH',
            'APR' => 'APRIL',
            'MAY' => 'MAY',
            'JUN' => 'JUNE',
            'JUL' => 'JULY',
            'AUG' => 'AUGUST',
            'SEP' => 'SEPTEMBER',
            'OCT' => 'OCTOBER',
            'NOV' => 'NOVEMBER',
            'DEC' => 'DECEMBER',
        ];

        return array_values(array_unique([$normalized, $fullMonthNames[$normalized]]));
    }

    /**
     * Get historical average production for a given month.
     */
    private function getHistoricalAverage(int $currentYear, string $month, $topCrop, $topMunicipality, $topFarmType): float
    {
        $query = Crop::where('year', '<', $currentYear)
            ->where('month', $month);

        if ($topCrop) {
            $query->where('crop', $topCrop->crop);
        }
        if ($topMunicipality) {
            $query->where('municipality', $topMunicipality->municipality);
        }
        if ($topFarmType) {
            $query->where('farm_type', $topFarmType->farm_type);
        }

        $yearlySums = $query
            ->select('year', DB::raw('SUM(production) as yearly_production'))
            ->groupBy('year')
            ->pluck('yearly_production');

        return $yearlySums->count() > 0 ? round($yearlySums->avg(), 2) : 0;
    }
}
