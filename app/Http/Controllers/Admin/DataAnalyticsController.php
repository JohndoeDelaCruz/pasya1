<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Crop;
use App\Models\CropPlan;
use App\Models\CropType;
use App\Models\Municipality;
use App\Services\PredictionService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DataAnalyticsController extends Controller
{
    private const PLANTING_REPORT_PDF_MAX_ROWS = 300;

    protected $predictionService;

    /**
     * Fixed color mapping for each municipality to ensure consistent colors across all charts
     */
    protected const MUNICIPALITY_COLORS = [
        'ATOK' => 'rgb(16, 185, 129)',       // Emerald
        'BAKUN' => 'rgb(59, 130, 246)',      // Blue
        'BOKOD' => 'rgb(245, 158, 11)',      // Amber
        'BUGUIAS' => 'rgb(239, 68, 68)',     // Red
        'ITOGON' => 'rgb(139, 92, 246)',     // Violet
        'KABAYAN' => 'rgb(236, 72, 153)',    // Pink
        'KAPANGAN' => 'rgb(6, 182, 212)',    // Cyan
        'KIBUNGAN' => 'rgb(249, 115, 22)',   // Orange
        'LATRINIDAD' => 'rgb(34, 197, 94)', // Green
        'MANKAYAN' => 'rgb(99, 102, 241)',   // Indigo
        'SABLAN' => 'rgb(168, 85, 247)',     // Purple
        'TUBA' => 'rgb(14, 165, 233)',       // Sky
        'TUBLAY' => 'rgb(251, 191, 36)',     // Yellow
    ];

    /**
     * Cross-database month ordering expression (works on MySQL and PostgreSQL).
     */
    protected const MONTH_ORDER_SQL = "CASE month WHEN 'JAN' THEN 1 WHEN 'FEB' THEN 2 WHEN 'MAR' THEN 3 WHEN 'APR' THEN 4 WHEN 'MAY' THEN 5 WHEN 'JUN' THEN 6 WHEN 'JUL' THEN 7 WHEN 'AUG' THEN 8 WHEN 'SEP' THEN 9 WHEN 'OCT' THEN 10 WHEN 'NOV' THEN 11 WHEN 'DEC' THEN 12 ELSE 13 END";

    /**
     * Get the color for a municipality (case-insensitive)
     */
    protected function getMunicipalityColor(string $municipality): string
    {
        $upperMunicipality = strtoupper(trim($municipality));
        return self::MUNICIPALITY_COLORS[$upperMunicipality] ?? 'rgb(107, 114, 128)'; // Gray fallback
    }

    public function __construct(PredictionService $predictionService)
    {
        $this->predictionService = $predictionService;
    }

    public function index(Request $request)
    {
        $request->validate([
            'crop' => 'nullable|string|max:255',
            'municipality' => 'nullable|string|max:255',
            'month' => 'nullable|string|max:20',
            'year' => 'nullable|integer|min:1990|max:2100',
            'farm_type' => 'nullable|string|max:255',
        ]);

        // Get filters from request
        $latestDataYear = Crop::max('year') ?? date('Y');
        $filters = [
            'crop' => $request->input('crop'),
            'municipality' => $request->input('municipality'),
            'month' => $request->input('month'),
            'year' => $request->input('year') ?? $latestDataYear,
            'farm_type' => $request->input('farm_type')
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
        if ($filters['farm_type']) {
            $query->where('farm_type', $filters['farm_type']);
        }

        // Get data for charts - Group by municipality and year for trend analysis
        $trendData = (clone $query)
            ->select(
                'municipality',
                'year',
                DB::raw('SUM(production) as total_production') // Production is already in metric tons
            )
            ->groupBy('municipality', 'year')
            ->orderBy('year')
            ->get();

        // Prepare chart data
        $municipalities = $trendData->pluck('municipality')->unique()->values();
        $years = $trendData->pluck('year')->unique()->sort()->values();

        // If filtering by municipality, year, AND month, show crop breakdown for that specific combination
        if ($filters['municipality'] && $filters['year'] && $filters['month']) {
            // Get crop data for this specific municipality, year, and month
            $cropData = (clone $query)
                ->select(
                    'crop',
                    DB::raw('SUM(production) as total_production')
                )
                ->groupBy('crop')
                ->orderByDesc('total_production')
                ->limit(10)
                ->get();

            $cropLabels = [];
            $cropProduction = [];
            // Modern vibrant color palette for charts
            $colors = [
                'rgb(16, 185, 129)',   // Emerald
                'rgb(59, 130, 246)',   // Blue
                'rgb(245, 158, 11)',   // Amber
                'rgb(239, 68, 68)',    // Red
                'rgb(139, 92, 246)',   // Violet
                'rgb(236, 72, 153)',   // Pink
                'rgb(6, 182, 212)',    // Cyan
                'rgb(249, 115, 22)',   // Orange
                'rgb(34, 197, 94)',    // Green
                'rgb(99, 102, 241)'    // Indigo
            ];
            
            foreach ($cropData as $data) {
                $cropLabels[] = ucwords(strtolower($data->crop));
                $cropProduction[] = round($data->total_production, 2);
            }

            $chartData = [
                'labels' => $cropLabels,
                'datasets' => [[
                    'label' => 'Production by Crop',
                    'data' => $cropProduction,
                    'backgroundColor' => array_slice($colors, 0, count($cropLabels)),
                    'borderColor' => array_slice($colors, 0, count($cropLabels)),
                    'borderWidth' => 1,
                    'borderRadius' => 6,
                ]]
            ];
        }
        // If filtering by month only, show municipality ranking for that month
        elseif ($filters['month'] && !$filters['municipality'] && !$filters['crop']) {
            $municipalityRanking = (clone $query)
                ->select('municipality', DB::raw('SUM(production) as total_production'))
                ->groupBy('municipality')
                ->orderByDesc('total_production')
                ->get();

            $chartData = [
                'labels' => $municipalityRanking->map(fn($m) => ucwords(strtolower($m->municipality)))->toArray(),
                'datasets' => [[
                    'label' => 'Production',
                    'data' => $municipalityRanking->map(fn($m) => round($m->total_production, 2))->toArray(),
                    'backgroundColor' => $municipalityRanking->map(fn($m) => $this->getMunicipalityColor($m->municipality))->toArray(),
                    'borderColor' => $municipalityRanking->map(fn($m) => $this->getMunicipalityColor($m->municipality))->toArray(),
                    'borderWidth' => 1,
                    'borderRadius' => 6,
                ]]
            ];
        }
        // If filtering by year only (no municipality), show municipality ranking for that year
        elseif ($filters['year'] && !$filters['municipality']) {
            $yearQuery = Crop::query()->where('year', $filters['year']);
            if ($filters['crop']) { $yearQuery->where('crop', $filters['crop']); }
            if ($filters['farm_type']) { $yearQuery->where('farm_type', $filters['farm_type']); }

            $municipalityRanking = $yearQuery
                ->select('municipality', DB::raw('SUM(production) as total_production'))
                ->groupBy('municipality')
                ->orderByDesc('total_production')
                ->get();

            $chartData = [
                'labels' => $municipalityRanking->map(fn($m) => ucwords(strtolower($m->municipality)))->toArray(),
                'datasets' => [[
                    'label' => 'Production ' . $filters['year'],
                    'data' => $municipalityRanking->map(fn($m) => round($m->total_production, 2))->toArray(),
                    'backgroundColor' => $municipalityRanking->map(fn($m) => $this->getMunicipalityColor($m->municipality))->toArray(),
                    'borderColor' => $municipalityRanking->map(fn($m) => $this->getMunicipalityColor($m->municipality))->toArray(),
                    'borderWidth' => 1,
                    'borderRadius' => 6,
                ]]
            ];
        }
        // If filtering by crop + municipality + year, show monthly data for that specific crop
        elseif ($filters['crop'] && $filters['municipality'] && $filters['year']) {
            // Create a fresh query without the month filter to show all 12 months
            $monthlyQuery = Crop::query()
                ->where('crop', $filters['crop'])
                ->where('municipality', $filters['municipality'])
                ->where('year', $filters['year']);
            
            // Apply farm_type filter if present
            if ($filters['farm_type']) {
                $monthlyQuery->where('farm_type', $filters['farm_type']);
            }
            
            // Get monthly data for this specific crop, municipality and year
            $monthlyTrendData = $monthlyQuery
                ->select(
                    'month',
                    DB::raw('SUM(production) as total_production')
                )
                ->groupBy('month')
                ->orderByRaw(self::MONTH_ORDER_SQL)
                ->get();

            $monthNames = ['JAN' => 'Jan', 'FEB' => 'Feb', 'MAR' => 'Mar', 'APR' => 'Apr', 
                           'MAY' => 'May', 'JUN' => 'Jun', 'JUL' => 'Jul', 'AUG' => 'Aug',
                           'SEP' => 'Sep', 'OCT' => 'Oct', 'NOV' => 'Nov', 'DEC' => 'Dec'];
            
            $monthLabels = [];
            $monthData = [];
            
            foreach ($monthlyTrendData as $data) {
                if (isset($monthNames[$data->month])) {
                    $monthLabels[] = $monthNames[$data->month];
                    $monthData[] = round($data->total_production, 2);
                }
            }

            $chartData = [
                'labels' => $monthLabels,
                'datasets' => [[
                    'label' => ucwords(strtolower($filters['crop'])) . ' - ' . $filters['municipality'] . ' (' . $filters['year'] . ')',
                    'data' => $monthData,
                    'backgroundColor' => 'rgba(16, 185, 129, 0.85)',
                    'borderColor' => 'rgb(16, 185, 129)',
                    'borderWidth' => 1,
                    'borderRadius' => 6,
                ]]
            ];
        }
        // If filtering by both municipality and year, show monthly data instead
        elseif ($filters['municipality'] && $filters['year']) {
            // Create a fresh query without the month filter to show all 12 months
            $monthlyQuery = Crop::query()
                ->where('municipality', $filters['municipality'])
                ->where('year', $filters['year']);
            
            // Apply crop filter if present
            if ($filters['crop']) {
                $monthlyQuery->where('crop', $filters['crop']);
            }
            
            // Apply farm_type filter if present
            if ($filters['farm_type']) {
                $monthlyQuery->where('farm_type', $filters['farm_type']);
            }
            
            // Get monthly data for this specific municipality and year
            $monthlyTrendData = $monthlyQuery
                ->select(
                    'month',
                    DB::raw('SUM(production) as total_production')
                )
                ->groupBy('month')
                ->orderByRaw(self::MONTH_ORDER_SQL)
                ->get();

            $monthNames = ['JAN' => 'Jan', 'FEB' => 'Feb', 'MAR' => 'Mar', 'APR' => 'Apr', 
                           'MAY' => 'May', 'JUN' => 'Jun', 'JUL' => 'Jul', 'AUG' => 'Aug',
                           'SEP' => 'Sep', 'OCT' => 'Oct', 'NOV' => 'Nov', 'DEC' => 'Dec'];
            
            $monthLabels = [];
            $monthData = [];
            
            foreach ($monthlyTrendData as $data) {
                if (isset($monthNames[$data->month])) {
                    $monthLabels[] = $monthNames[$data->month];
                    $monthData[] = round($data->total_production, 2);
                }
            }

            $chartData = [
                'labels' => $monthLabels,
                'datasets' => [[
                    'label' => $filters['municipality'] . ' (' . $filters['year'] . ')',
                    'data' => $monthData,
                    'backgroundColor' => 'rgba(59, 130, 246, 0.85)',
                    'borderColor' => 'rgb(59, 130, 246)',
                    'borderWidth' => 1,
                    'borderRadius' => 6,
                ]]
            ];
        } else {
            // Default: Municipality ranking for latest year
            $latestYear = $years->isNotEmpty() ? $years->last() : date('Y');

            $latestYearData = $trendData->where('year', $latestYear)
                ->sortByDesc('total_production')
                ->values();

            $chartData = [
                'labels' => $latestYearData->map(fn($m) => ucwords(strtolower($m->municipality)))->toArray(),
                'datasets' => [[
                    'label' => 'Production ' . $latestYear,
                    'data' => $latestYearData->map(fn($m) => round($m->total_production, 2))->toArray(),
                    'backgroundColor' => $latestYearData->map(fn($m) => $this->getMunicipalityColor($m->municipality))->toArray(),
                    'borderColor' => $latestYearData->map(fn($m) => $this->getMunicipalityColor($m->municipality))->toArray(),
                    'borderWidth' => 1,
                    'borderRadius' => 6,
                ]]
            ];
        }

        // Determine chart mode based on filters
        if ($filters['crop'] && $filters['municipality'] && $filters['year']) {
            $chartMode = 'monthly_crop';
        } elseif ($filters['municipality'] && $filters['year'] && $filters['month'] && !$filters['crop']) {
            $chartMode = 'crop_breakdown';
        } elseif ($filters['municipality'] && $filters['year']) {
            $chartMode = 'monthly';
        } else {
            $chartMode = 'yearly'; // municipality ranking
        }

        // Debug: Log chart data structure
        \Log::info('Chart Data Debug:', [
            'chart_mode' => $chartMode,
            'labels_count' => count($chartData['labels']),
            'datasets_count' => count($chartData['datasets']),
            'has_data' => !empty($chartData['datasets'])
        ]);

        // Get monthly production data
        $monthlyData = (clone $query)
            ->select(
                'month',
                DB::raw('SUM(production) as total_production') // Production is already in metric tons
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

        // Calculate trend percentage based on chart data and filters
        $trendPercentage = $this->calculateTrendFromChartData($chartData, $chartMode, $filters);

        // Generate ML Predictions (optimized to prevent timeout)
        $mlPredictions = $this->generatePredictions($filters);

        // Get distinct crops and municipalities for filters
        $crops = Crop::distinct()->orderBy('crop')->pluck('crop');
        $allMunicipalities = Crop::distinct()->orderBy('municipality')->pluck('municipality');
        $allYears = Crop::select('year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');

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
            'filterYear' => $filters['year'] ?? $latestDataYear,
            'filterFarmType' => $filters['farm_type'],
            'selectedYear' => $filters['year'] ?? $latestDataYear,
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
            'lastUpdate' => now(),
            // Announcements for quick management
            'recentAnnouncements' => Announcement::with('creator')
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get(),
            'activeAnnouncementsCount' => Announcement::active()->count(),
            'totalAnnouncementsCount' => Announcement::count(),
        ]);
    }

    private function calculateMetrics($query)
    {
        // Total farmers from farmers table
        $totalFarmers = \App\Models\Farmer::count();

        // Total area harvested (in hectares)
        $totalAreaHarvested = (clone $query)->sum('area_harvested');

        // Average yield (productivity in mt/ha)
        $averageYield = (clone $query)->avg('productivity');

        // Top 3 crops by production
        $topCrops = (clone $query)
            ->select('crop', DB::raw('SUM(production) as total_production')) // Production is already in mt
            ->groupBy('crop')
            ->orderByDesc('total_production')
            ->limit(3)
            ->get();

        // Most productive municipality
        $mostProductiveMunicipality = (clone $query)
            ->select('municipality', DB::raw('SUM(production) as total_production')) // Production is already in mt
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
            ->where('year', $currentYear)
            ->sum('production');

        $previousYearProduction = (clone $query)
            ->where('year', $previousYear)
            ->sum('production');

        if ($previousYearProduction > 0) {
            $percentage = (($currentYearProduction - $previousYearProduction) / $previousYearProduction) * 100;
            return round($percentage, 1);
        }

        // If no previous year data, return 0
        return 0;
    }

    private function calculateTrendFromChartData($chartData, $chartMode, $filters = [])
    {
        if (!isset($chartData['datasets']) || empty($chartData['datasets'])) {
            return 0;
        }

        // For crop breakdown mode, show top vs second comparison
        if ($chartMode === 'crop_breakdown' || $chartMode === 'crops') {
            $datasets = $chartData['datasets'];
            if (isset($datasets[0]['data']) && count($datasets[0]['data']) >= 2) {
                $data = $datasets[0]['data'];
                // Compare highest vs second highest
                rsort($data);
                if (isset($data[1]) && $data[1] > 0) {
                    return round((($data[0] - $data[1]) / $data[1]) * 100, 1);
                }
            }
            return 0;
        }
        
        // For monthly mode, compare last available month with previous month
        if ($chartMode === 'monthly' || $chartMode === 'monthly_crop') {
            $datasets = $chartData['datasets'];
            if (isset($datasets[0]['data']) && count($datasets[0]['data']) >= 2) {
                $data = $datasets[0]['data'];
                $nonZeroData = array_filter($data, function($val) { return $val > 0; });
                
                if (count($nonZeroData) >= 2) {
                    $values = array_values($nonZeroData);
                    $lastValue = end($values);
                    $previousValue = prev($values);
                    
                    if ($previousValue > 0) {
                        return round((($lastValue - $previousValue) / $previousValue) * 100, 1);
                    }
                }
            }
            return 0;
        }

        // For yearly mode (municipality ranking), compare top vs second municipality
        if ($chartMode === 'yearly') {
            $datasets = $chartData['datasets'];
            if (isset($datasets[0]['data']) && count($datasets[0]['data']) >= 2) {
                $data = $datasets[0]['data'];
                $sorted = $data;
                rsort($sorted);
                if ($sorted[1] > 0) {
                    return round((($sorted[0] - $sorted[1]) / $sorted[1]) * 100, 1);
                }
            }
        }

        return 0;
    }

    private function generatePredictions($filters)
    {
        try {
            // Skip predictions if service not available
            if (!$this->predictionService) {
                return [];
            }

            $predictions = [];
            $maxPredictions = env('MAX_PREDICTIONS', 12); // Configurable via .env

            // Get valid values for predictions
            $validValues = $this->predictionService->getValidValues();
            
            // Scalable approach: adapt based on filters
            if ($filters['municipality'] && $filters['crop']) {
                // Most specific: single municipality and crop - predict all months
                $municipalities = [$filters['municipality']];
                $cropTypes = [$filters['crop']];
                $predictMonths = true;
            } elseif ($filters['municipality']) {
                // Single municipality, multiple crops
                $municipalities = [$filters['municipality']];
                $cropTypes = Crop::where('municipality', $filters['municipality'])
                    ->select('crop')
                    ->groupBy('crop')
                    ->orderByDesc(DB::raw('SUM(production)'))
                    ->limit(5) // Top 5 crops
                    ->pluck('crop')
                    ->toArray();
                $predictMonths = false;
            } elseif ($filters['crop']) {
                // Single crop, multiple municipalities
                $municipalities = Crop::where('crop', $filters['crop'])
                    ->select('municipality')
                    ->groupBy('municipality')
                    ->orderByDesc(DB::raw('SUM(production)'))
                    ->limit(5) // Top 5 municipalities
                    ->pluck('municipality')
                    ->toArray();
                $cropTypes = [$filters['crop']];
                $predictMonths = false;
            } else {
                // No filter: show top combinations
                $municipalities = Crop::select('municipality')
                    ->groupBy('municipality')
                    ->orderByDesc(DB::raw('SUM(production)'))
                    ->limit(2)
                    ->pluck('municipality')
                    ->toArray();
                    
                $cropTypes = Crop::select('crop')
                    ->groupBy('crop')
                    ->orderByDesc(DB::raw('SUM(production)'))
                    ->limit(2)
                    ->pluck('crop')
                    ->toArray();
                $predictMonths = false;
            }

            // Use the latest year from database + 1, not current year + 1
            $latestYear = Crop::max('year') ?? now()->year;
            $year = $filters['year'] ? $filters['year'] + 1 : $latestYear + 1;
            $predictionCount = 0;

            foreach ($municipalities as $municipality) {
                foreach ($cropTypes as $cropType) {
                    if ($predictionCount >= $maxPredictions) {
                        break 2;
                    }

                    // Get historical data
                    $historicalData = Crop::where('municipality', $municipality)
                        ->where('crop', $cropType)
                        ->selectRaw('
                            AVG(area_harvested) as avg_area,
                            farm_type,
                            COUNT(*) as records
                        ')
                        ->groupBy('farm_type')
                        ->orderByDesc('records')
                        ->first();

                    if (!$historicalData) continue;

                    $avgArea = $historicalData->avg_area ?? 100;
                    $farmType = $historicalData->farm_type ?? 'Rainfed';

                    // Determine months
                    if ($predictMonths) {
                        $months = ['JAN', 'FEB', 'MAR', 'APR', 'MAY', 'JUN', 'JUL', 'AUG', 'SEP', 'OCT', 'NOV', 'DEC'];
                    } else {
                        $months = [$filters['month'] ?? strtoupper(date('M'))];
                    }

                    foreach ($months as $month) {
                        if ($predictionCount >= $maxPredictions) {
                            break 3;
                        }

                        $prediction = $this->predictionService->predictProduction([
                            'municipality' => $municipality,
                            'farm_type' => $farmType,
                            'month' => $month,
                            'crop' => $cropType,
                            'area_harvested' => round($avgArea, 2)
                        ]);

                        if ($prediction && isset($prediction['success']) && $prediction['success']) {
                            // ML API returns production_mt which is already in metric tons (MT)
                            $productionMT = $prediction['prediction']['production_mt'] ?? 0;
                            
                            // ML API V2 returns confidence_score (0-100)
                            $confidenceScore = $prediction['prediction']['confidence_score'] ?? null;
                            
                            $predictions[] = [
                                'year' => $year,
                                'month' => $month,
                                'municipality' => $municipality,
                                'crop_type' => $cropType,
                                'farm_type' => $farmType,
                                'area_harvested' => round($avgArea, 2),
                                'predicted_production' => round($productionMT, 2),
                                'confidence' => $confidenceScore ? round($confidenceScore, 1) . '%' : null
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

    public function plantingReport(Request $request)
    {
        $validated = $this->validatePlantingReportFilters($request);
        $plantingRecordsQuery = $this->getPlantingReportQuery($validated);
        $summary = $this->getPlantingReportSummary($plantingRecordsQuery);
        $plantingRecords = $plantingRecordsQuery->paginate(15)->withQueryString();
        $municipalities = $this->getPlantingReportMunicipalities();
        $statuses = $this->getPlantingReportStatuses();

        return view('admin.planting-report', [
            'plantingRecords' => $plantingRecords,
            'summary' => $summary,
            'municipalities' => $municipalities,
            'statuses' => $statuses,
            'filters' => $validated,
        ]);
    }

    public function exportPlantingReportCsv(Request $request)
    {
        $validated = $this->validatePlantingReportFilters($request);
        $plantingRecordsQuery = $this->getPlantingReportQuery($validated);

        if ((clone $plantingRecordsQuery)->count() === 0) {
            return redirect()
                ->route('admin.planting-report', $this->getFilledPlantingReportFilters($validated))
                ->with('error', 'No planting records match the current filters.');
        }

        return response()->streamDownload(function () use ($plantingRecordsQuery) {
            $handle = fopen('php://output', 'w');

            if ($handle === false) {
                return;
            }

            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($handle, [
                'Farmer Name',
                'Farmer ID',
                'Municipality',
                'Cooperative',
                'Archived Farmer',
                'Mobile Number',
                'Email',
                'Crop Name',
                'Notes',
                'Planting Date',
                'Expected Harvest Date',
                'Area (ha)',
                'Predicted Production (MT)',
                'Farm Type',
                'Planting Material',
                'Status',
                'Recorded At',
            ]);

            $plantingRecordsQuery->chunk(200, function ($records) use ($handle) {
                foreach ($records as $record) {
                    $farmer = $record->farmer;

                    fputcsv($handle, [
                        $farmer?->full_name ?? 'Farmer record unavailable',
                        $farmer?->farmer_id ?? 'N/A',
                        $record->municipality ?? $farmer?->municipality ?? 'N/A',
                        $farmer?->cooperative_display ?? 'N/A',
                        $farmer?->trashed() ? 'Yes' : 'No',
                        $farmer?->mobile_number ?? 'No mobile number',
                        $farmer?->email ?? 'No email address',
                        $record->crop_name,
                        $record->notes ?? '',
                        optional($record->planting_date)->format('Y-m-d'),
                        optional($record->expected_harvest_date)->format('Y-m-d'),
                        number_format((float) $record->area_hectares, 2, '.', ''),
                        number_format((float) $record->predicted_production, 2, '.', ''),
                        ucfirst(strtolower((string) $record->farm_type)),
                        $record->planting_material_label ?? 'Not set',
                        $record->status,
                        optional($record->created_at)->format('Y-m-d H:i:s'),
                    ]);
                }
            });

            fclose($handle);
        }, $this->getPlantingReportExportFilename('csv'), [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function exportPlantingReportPdf(Request $request)
    {
        $validated = $this->validatePlantingReportFilters($request);
        $plantingRecordsQuery = $this->getPlantingReportQuery($validated);
        $totalRecords = (clone $plantingRecordsQuery)->count();

        if ($totalRecords === 0) {
            return redirect()
                ->route('admin.planting-report', $this->getFilledPlantingReportFilters($validated))
                ->with('error', 'No planting records match the current filters.');
        }

        if ($totalRecords > self::PLANTING_REPORT_PDF_MAX_ROWS) {
            return redirect()
                ->route('admin.planting-report', $this->getFilledPlantingReportFilters($validated))
                ->with('error', 'PDF export is limited to ' . self::PLANTING_REPORT_PDF_MAX_ROWS . ' records. Narrow the filters or use CSV export instead.');
        }

        $pdf = app('dompdf.wrapper');
        $pdf->loadView('admin.planting-report-pdf', [
            'plantingRecords' => $plantingRecordsQuery->get(),
            'summary' => $this->getPlantingReportSummary($this->getPlantingReportQuery($validated)),
            'filters' => $validated,
            'generatedAt' => now(),
        ])->setPaper('a4', 'landscape');

        return $pdf->download($this->getPlantingReportExportFilename('pdf'));
    }

    private function validatePlantingReportFilters(Request $request): array
    {
        return $request->validate([
            'search' => 'nullable|string|max:255',
            'status' => 'nullable|in:' . implode(',', $this->getPlantingReportStatuses()),
            'municipality' => 'nullable|string|max:255',
        ]);
    }

    private function getPlantingReportQuery(array $filters): Builder
    {
        return CropPlan::query()
            ->with([
                'farmer' => fn ($query) => $query->withTrashed()->select([
                    'id',
                    'farmer_id',
                    'first_name',
                    'middle_name',
                    'last_name',
                    'suffix',
                    'municipality',
                    'cooperative',
                    'mobile_number',
                    'email',
                    'deleted_at',
                ]),
            ])
            ->when($filters['search'] ?? null, function ($query, $search) {
                $searchTerm = '%' . trim($search) . '%';

                $query->where(function ($nestedQuery) use ($searchTerm) {
                    $nestedQuery->where('crop_name', 'like', $searchTerm)
                        ->orWhere('municipality', 'like', $searchTerm)
                        ->orWhere('status', 'like', $searchTerm)
                        ->orWhereHas('farmer', function ($farmerQuery) use ($searchTerm) {
                            $farmerQuery->withTrashed()
                                ->where('farmer_id', 'like', $searchTerm)
                                ->orWhere('first_name', 'like', $searchTerm)
                                ->orWhere('middle_name', 'like', $searchTerm)
                                ->orWhere('last_name', 'like', $searchTerm)
                                ->orWhere('cooperative', 'like', $searchTerm)
                                ->orWhere('mobile_number', 'like', $searchTerm)
                                ->orWhere('email', 'like', $searchTerm);
                        });
                });
            })
            ->when($filters['status'] ?? null, function ($query, $status) {
                $query->where('status', $status);
            })
            ->when($filters['municipality'] ?? null, function ($query, $municipality) {
                $query->where('municipality', $municipality);
            })
            ->latest('planting_date')
            ->latest('created_at');
    }

    private function getPlantingReportSummary(Builder $plantingRecordsQuery): array
    {
        return [
            'total_records' => (clone $plantingRecordsQuery)->count(),
            'total_area' => (float) (clone $plantingRecordsQuery)->sum('area_hectares'),
            'total_predicted_production' => (float) (clone $plantingRecordsQuery)->sum('predicted_production'),
            'planned_records' => (clone $plantingRecordsQuery)->where('status', 'planned')->count(),
        ];
    }

    private function getPlantingReportMunicipalities()
    {
        return CropPlan::query()
            ->whereNotNull('municipality')
            ->distinct()
            ->orderBy('municipality')
            ->pluck('municipality');
    }

    private function getPlantingReportStatuses(): array
    {
        return ['planned', 'planted', 'growing', 'harvested', 'cancelled'];
    }

    private function getFilledPlantingReportFilters(array $filters): array
    {
        return array_filter($filters, static fn ($value) => filled($value));
    }

    private function getPlantingReportExportFilename(string $extension): string
    {
        return 'planting-report-' . now()->format('Ymd_His') . '.' . $extension;
    }
}

