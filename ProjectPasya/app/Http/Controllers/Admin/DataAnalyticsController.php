<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Crop;
use App\Models\CropType;
use App\Models\Municipality;
use App\Services\PredictionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DataAnalyticsController extends Controller
{
    protected $predictionService;

    public function __construct(PredictionService $predictionService)
    {
        $this->predictionService = $predictionService;
    }

    public function index(Request $request)
    {
        // Get filters from request
        $filters = [
            'crop' => $request->input('crop'),
            'municipality' => $request->input('municipality'),
            'month' => $request->input('month'),
            'year' => $request->input('year')
        ];

        // Base query for crops
        $query = Crop::query();

        // Apply filters
        if ($filters['crop']) {
            $query->where('crop', $filters['crop']);
        }
        if ($filters['municipality']) {
            $query->where('municipality', $filters['municipality']);
        }
        if ($filters['month']) {
            $query->where('month', $filters['month']);
        }
        if ($filters['year']) {
            $query->where('year', $filters['year']);
        }

        // Get data for charts - Group by municipality and year for trend analysis
        $trendData = (clone $query)
            ->select(
                'municipality',
                'year',
                DB::raw('SUM(production) / 1000 as total_production') // Convert to metric tons
            )
            ->groupBy('municipality', 'year')
            ->orderBy('year')
            ->get();

        // Prepare chart data
        $municipalities = $trendData->pluck('municipality')->unique()->values();
        $years = $trendData->pluck('year')->unique()->sort()->values();

        $datasets = [];
        $colors = [
            'rgb(59, 130, 246)', 'rgb(239, 68, 68)', 'rgb(34, 197, 94)', 
            'rgb(234, 179, 8)', 'rgb(168, 85, 247)', 'rgb(236, 72, 153)',
            'rgb(20, 184, 166)', 'rgb(251, 146, 60)', 'rgb(156, 163, 175)',
            'rgb(14, 165, 233)', 'rgb(124, 58, 237)', 'rgb(220, 38, 38)',
            'rgb(22, 163, 74)'
        ];

        foreach ($municipalities as $index => $municipality) {
            $municipalityData = $trendData->where('municipality', $municipality);
            $data = [];
            
            foreach ($years as $year) {
                $yearData = $municipalityData->where('year', $year)->first();
                $data[] = $yearData ? round($yearData->total_production, 2) : 0;
            }

            $datasets[] = [
                'label' => $municipality,
                'data' => $data,
                'borderColor' => $colors[$index % count($colors)],
                'backgroundColor' => 'transparent',
                'borderWidth' => 3,
                'tension' => 0.4,
                'fill' => false,
                'pointRadius' => 5,
                'pointHoverRadius' => 8
            ];
        }

        $chartData = [
            'labels' => $years->toArray(),
            'datasets' => $datasets
        ];

        // Debug: Log chart data structure
        \Log::info('Chart Data Debug:', [
            'years' => $years->toArray(),
            'municipalities_count' => $municipalities->count(),
            'datasets_count' => count($datasets),
            'trend_data_count' => $trendData->count(),
            'sample_dataset' => $datasets[0] ?? null
        ]);

        // Get monthly production data
        $monthlyData = (clone $query)
            ->select(
                'month',
                DB::raw('SUM(production) / 1000 as total_production') // Convert to metric tons
            )
            ->groupBy('month')
            ->get();

        $monthNames = ['JAN' => 'Jan', 'FEB' => 'Feb', 'MAR' => 'Mar', 'APR' => 'Apr', 
                       'MAY' => 'May', 'JUN' => 'Jun', 'JUL' => 'Jul', 'AUG' => 'Aug',
                       'SEP' => 'Sep', 'OCT' => 'Oct', 'NOV' => 'Nov', 'DEC' => 'Dec'];
        $monthlyChartData = [
            'labels' => [],
            'data' => []
        ];

        foreach ($monthlyData as $data) {
            if (isset($monthNames[$data->month])) {
                $monthlyChartData['labels'][] = $monthNames[$data->month];
                $monthlyChartData['data'][] = round($data->total_production, 2);
            }
        }

        // Calculate metrics
        $metrics = $this->calculateMetrics($query);

        // Calculate trend percentage
        $trendPercentage = $this->calculateTrendPercentage($query);

        // Generate ML Predictions (optimized to prevent timeout)
        $mlPredictions = $this->generatePredictions($filters);

        // Get distinct crops and municipalities for filters
        $crops = Crop::distinct()->orderBy('crop')->pluck('crop');
        $allMunicipalities = Crop::distinct()->orderBy('municipality')->pluck('municipality');
        $allYears = Crop::selectRaw('DISTINCT YEAR(month) as year')
            ->orderBy('year', 'desc')
            ->pluck('year');

        // Determine chart mode based on filters
        $chartMode = ($filters['year'] && !$filters['municipality']) ? 'monthly' : 'yearly';

        // Prepare monthly demand data as keyed array
        $monthlyDemand = [
            'JAN' => 0, 'FEB' => 0, 'MAR' => 0, 'APR' => 0,
            'MAY' => 0, 'JUN' => 0, 'JUL' => 0, 'AUG' => 0,
            'SEP' => 0, 'OCT' => 0, 'NOV' => 0, 'DEC' => 0
        ];
        
        foreach ($monthlyData as $data) {
            if (isset($data->month)) {
                $monthlyDemand[$data->month] = round($data->total_production, 2);
            }
        }

        // Prepare predictions in expected format
        $predictions = [
            'available' => !empty($mlPredictions),
            'predictions' => $mlPredictions,
            'count' => count($mlPredictions)
        ];

        return view('admin.data-analytics', [
            'filters' => $filters,
            'filterCrop' => $filters['crop'],
            'filterMunicipality' => $filters['municipality'],
            'filterMonth' => $filters['month'],
            'filterYear' => $filters['year'] ?? date('Y'),
            'selectedYear' => $filters['year'] ?? date('Y'),
            'chartMode' => $chartMode,
            'chartData' => $chartData,
            'trendChartData' => $chartData,
            'monthlyData' => $monthlyChartData,
            'monthlyDemand' => $monthlyDemand,
            'metrics' => $metrics,
            'mlPredictions' => $mlPredictions,
            'predictions' => $predictions,
            'crops' => $crops,
            'allMunicipalities' => $allMunicipalities,
            'allYears' => $allYears,
            'municipalities' => $municipalities,
            'years' => $years,
            'productionTrend' => $trendPercentage,
            'trendPercentage' => $trendPercentage,
            // Individual metric values
            'totalFarmers' => $metrics['totalFarmers'],
            'totalAreaHarvested' => $metrics['totalAreaHarvested'],
            'averageYield' => $metrics['averageYield'],
            'topCrops' => collect($metrics['topCrops']),
            'topMunicipality' => $metrics['mostProductiveMunicipality'],
            'lastUpdate' => now()
        ]);
    }

    private function calculateMetrics($query)
    {
        // Total farmers from farmers table
        $totalFarmers = \App\Models\Farmer::count();

        // Total area harvested (in hectares)
        $totalAreaHarvested = (clone $query)->sum('area_harvested');

        // Average yield (productivity in kg/ha)
        $averageYield = (clone $query)->avg('productivity');

        // Top 3 crops by production
        $topCrops = (clone $query)
            ->select('crop', DB::raw('SUM(production) / 1000 as total_production')) // Convert to mt
            ->groupBy('crop')
            ->orderByDesc('total_production')
            ->limit(3)
            ->get();

        // Most productive municipality
        $mostProductiveMunicipality = (clone $query)
            ->select('municipality', DB::raw('SUM(production) / 1000 as total_production')) // Convert to mt
            ->groupBy('municipality')
            ->orderByDesc('total_production')
            ->first();

        return [
            'totalFarmers' => $totalFarmers,
            'totalAreaHarvested' => round($totalAreaHarvested, 2),
            'averageYield' => round($averageYield, 2),
            'topCrops' => $topCrops,
            'mostProductiveMunicipality' => $mostProductiveMunicipality
        ];
    }

    private function calculateTrendPercentage($query)
    {
        // Get current year and previous year production
        $currentYear = now()->year;
        $previousYear = $currentYear - 1;

        $currentYearProduction = (clone $query)
            ->whereYear('month', $currentYear)
            ->sum('production');

        $previousYearProduction = (clone $query)
            ->whereYear('month', $previousYear)
            ->sum('production');

        if ($previousYearProduction > 0) {
            $percentage = (($currentYearProduction - $previousYearProduction) / $previousYearProduction) * 100;
            return round($percentage, 1);
        }

        return 0;
    }

    private function generatePredictions($filters)
    {
        try {
            // Check if ML API is available
            if (!$this->predictionService->checkHealth()) {
                Log::warning('ML API is not available');
                return [];
            }

            $predictions = [];
            $predictionCount = 0;
            $maxPredictions = 15; // Limit to prevent timeout

            // Get valid values for predictions
            $validValues = $this->predictionService->getValidValues();
            
            // Get municipalities to predict for
            $municipalities = $filters['municipality'] 
                ? [$filters['municipality']] 
                : Municipality::pluck('name')->take(5)->toArray(); // Limit to 5 municipalities

            // Get crop types to predict for
            $cropTypes = $filters['crop']
                ? [$filters['crop']]
                : CropType::pluck('name')->take(3)->toArray(); // Limit to 3 crops

            // Predict for next 1-2 years
            $startYear = $filters['year'] ?? now()->year;
            $endYear = $startYear + 1; // Only predict 2 years ahead

            foreach (range($startYear, $endYear) as $year) {
                foreach ($municipalities as $municipality) {
                    foreach ($cropTypes as $cropType) {
                        if ($predictionCount >= $maxPredictions) {
                            break 3; // Exit all loops
                        }

                        // Use actual area_harvested from database or default
                        $avgArea = Crop::where('municipality', $municipality)
                            ->where('crop', $cropType)
                            ->avg('area_harvested') ?? 100;

                        $prediction = $this->predictionService->predictProduction(
                            $municipality,
                            $cropType,
                            $year,
                            round($avgArea, 2)
                        );

                        if ($prediction && isset($prediction['predicted_production'])) {
                            $predictions[] = [
                                'year' => $year,
                                'municipality' => $municipality,
                                'crop_type' => $cropType,
                                'area_harvested' => round($avgArea, 2),
                                'predicted_production' => round($prediction['predicted_production'] / 1000, 2) // Convert to mt
                            ];
                            $predictionCount++;
                        }
                    }
                }
            }

            return $predictions;
        } catch (\Exception $e) {
            Log::error('Error generating predictions: ' . $e->getMessage());
            return [];
        }
    }

    public function exportSummary()
    {
        return response()->json(['message' => 'Export functionality coming soon']);
    }
}

