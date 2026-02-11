<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
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
        // Get filters from request
        $filters = [
            'crop' => $request->input('crop'),
            'municipality' => $request->input('municipality'),
            'month' => $request->input('month'),
            'year' => $request->input('year'),
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
                    'borderWidth' => 3,
                    'tension' => 0.4,
                    'fill' => false,
                    'pointRadius' => 6,
                    'pointHoverRadius' => 9
                ]]
            ];
        }
        // If filtering by month (with or without year), show municipalities as separate lines for each crop
        elseif ($filters['month'] && !$filters['municipality'] && !$filters['crop']) {
            // Get all municipalities for this month/year
            $municipalitiesInPeriod = (clone $query)
                ->select('municipality')
                ->distinct()
                ->orderBy('municipality')
                ->pluck('municipality');

            // Get top 10 crops for this period to use as X-axis labels
            $topCrops = (clone $query)
                ->select('crop', DB::raw('SUM(production) as total_production'))
                ->groupBy('crop')
                ->orderByDesc('total_production')
                ->limit(10)
                ->pluck('crop');

            // Get data grouped by municipality and crop
            $dataByCropAndMunicipality = (clone $query)
                ->whereIn('crop', $topCrops)
                ->select(
                    'municipality',
                    'crop',
                    DB::raw('SUM(production) as total_production')
                )
                ->groupBy('municipality', 'crop')
                ->get();

            // Prepare crop labels (X-axis)
            $cropLabels = $topCrops->map(function($crop) {
                return ucwords(strtolower($crop));
            })->toArray();

            // Create datasets - one line per municipality
            $datasets = [];
            foreach ($municipalitiesInPeriod as $index => $municipality) {
                $data = [];
                
                // For each crop, get this municipality's production
                foreach ($topCrops as $crop) {
                    $municipalityData = $dataByCropAndMunicipality->where('municipality', $municipality)
                        ->where('crop', $crop)
                        ->first();
                    $data[] = $municipalityData ? round($municipalityData->total_production, 2) : 0;
                }

                $datasets[] = [
                    'label' => ucwords(strtolower($municipality)),
                    'data' => $data,
                    'borderColor' => $this->getMunicipalityColor($municipality),
                    'backgroundColor' => 'transparent',
                    'borderWidth' => 2,
                    'tension' => 0.4,
                    'fill' => false,
                    'pointRadius' => 4,
                    'pointHoverRadius' => 7
                ];
            }

            $chartData = [
                'labels' => $cropLabels,
                'datasets' => $datasets
            ];
        }
        // If filtering by year only (no municipality), show monthly data for each municipality
        elseif ($filters['year'] && !$filters['municipality']) {
            // Create a fresh query without the month filter to show all 12 months
            $yearQuery = Crop::query()
                ->where('year', $filters['year']);
            
            // Apply crop filter if present
            if ($filters['crop']) {
                $yearQuery->where('crop', $filters['crop']);
            }
            
            // Apply farm_type filter if present
            if ($filters['farm_type']) {
                $yearQuery->where('farm_type', $filters['farm_type']);
            }
            
            // Get all municipalities for this year
            $municipalitiesInYear = (clone $yearQuery)
                ->select('municipality')
                ->distinct()
                ->orderBy('municipality')
                ->pluck('municipality');

            // Get monthly data grouped by municipality
            $monthlyByMunicipality = (clone $yearQuery)
                ->select(
                    'municipality',
                    'month',
                    DB::raw('SUM(production) as total_production')
                )
                ->groupBy('municipality', 'month')
                ->orderByRaw("FIELD(month, 'JAN', 'FEB', 'MAR', 'APR', 'MAY', 'JUN', 'JUL', 'AUG', 'SEP', 'OCT', 'NOV', 'DEC')")
                ->get();

            $monthNames = ['JAN' => 'Jan', 'FEB' => 'Feb', 'MAR' => 'Mar', 'APR' => 'Apr', 
                           'MAY' => 'May', 'JUN' => 'Jun', 'JUL' => 'Jul', 'AUG' => 'Aug',
                           'SEP' => 'Sep', 'OCT' => 'Oct', 'NOV' => 'Nov', 'DEC' => 'Dec'];
            
            // Create month labels
            $monthLabels = array_values($monthNames);

            $datasets = [];
            foreach ($municipalitiesInYear as $index => $municipality) {
                $municipalityData = $monthlyByMunicipality->where('municipality', $municipality);
                $data = [];
                
                // Fill in data for each month
                foreach (array_keys($monthNames) as $monthCode) {
                    $monthRecord = $municipalityData->where('month', $monthCode)->first();
                    $data[] = $monthRecord ? round($monthRecord->total_production, 2) : 0;
                }

                $datasets[] = [
                    'label' => $municipality,
                    'data' => $data,
                    'borderColor' => $this->getMunicipalityColor($municipality),
                    'backgroundColor' => 'transparent',
                    'borderWidth' => 2,
                    'tension' => 0.4,
                    'fill' => false,
                    'pointRadius' => 4,
                    'pointHoverRadius' => 7
                ];
            }

            $chartData = [
                'labels' => $monthLabels,
                'datasets' => $datasets
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
                ->orderByRaw("FIELD(month, 'JAN', 'FEB', 'MAR', 'APR', 'MAY', 'JUN', 'JUL', 'AUG', 'SEP', 'OCT', 'NOV', 'DEC')")
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
                    'borderColor' => 'rgb(16, 185, 129)',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.15)',
                    'borderWidth' => 3,
                    'tension' => 0.4,
                    'fill' => true,
                    'pointRadius' => 6,
                    'pointHoverRadius' => 10,
                    'pointBackgroundColor' => 'rgb(16, 185, 129)',
                    'pointBorderColor' => '#fff',
                    'pointBorderWidth' => 2
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
                ->orderByRaw("FIELD(month, 'JAN', 'FEB', 'MAR', 'APR', 'MAY', 'JUN', 'JUL', 'AUG', 'SEP', 'OCT', 'NOV', 'DEC')")
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
                    'borderColor' => 'rgb(59, 130, 246)',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.15)',
                    'borderWidth' => 3,
                    'tension' => 0.4,
                    'fill' => true,
                    'pointRadius' => 6,
                    'pointHoverRadius' => 10,
                    'pointBackgroundColor' => 'rgb(59, 130, 246)',
                    'pointBorderColor' => '#fff',
                    'pointBorderWidth' => 2
                ]]
            ];
        } else {
            // Regular year-over-year trend chart
            $datasets = [];

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
                    'borderColor' => $this->getMunicipalityColor($municipality),
                    'backgroundColor' => 'transparent',
                    'borderWidth' => 2,
                    'tension' => 0.4,
                    'fill' => false,
                    'pointRadius' => 4,
                    'pointHoverRadius' => 7
                ];
            }

            $chartData = [
                'labels' => $years->toArray(),
                'datasets' => $datasets
            ];
        }

        // Debug: Log chart data structure
        \Log::info('Chart Data Debug:', [
            'chart_mode' => $filters['municipality'] && $filters['year'] ? 'monthly' : 'yearly',
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

        // Determine chart mode based on filters
        if ($filters['crop'] && $filters['municipality'] && $filters['year']) {
            // If crop is selected with municipality and year, show monthly breakdown for that crop
            $chartMode = 'monthly_crop';
        } elseif ($filters['municipality'] && $filters['year'] && $filters['month'] && !$filters['crop']) {
            $chartMode = 'crop_breakdown';
        } elseif ($filters['month'] && !$filters['municipality'] && !$filters['crop']) {
            $chartMode = 'municipalities'; // Show municipalities for month/year
        } elseif ($filters['year'] && !$filters['municipality']) {
            $chartMode = 'monthly_year';
        } elseif ($filters['municipality'] && $filters['year']) {
            $chartMode = 'monthly';
        } else {
            $chartMode = 'yearly';
        }

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
            'filterYear' => $filters['year'] ?? date('Y'),
            'filterFarmType' => $filters['farm_type'],
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
        
        // For municipalities mode (multiple lines), compare top municipality vs average
        if ($chartMode === 'municipalities') {
            $datasets = $chartData['datasets'];
            if (count($datasets) >= 2) {
                // Calculate total production for each municipality
                $municipalityTotals = [];
                foreach ($datasets as $dataset) {
                    $municipalityTotals[] = array_sum($dataset['data']);
                }
                
                rsort($municipalityTotals);
                
                // Compare top municipality with second
                if (isset($municipalityTotals[1]) && $municipalityTotals[1] > 0) {
                    return round((($municipalityTotals[0] - $municipalityTotals[1]) / $municipalityTotals[1]) * 100, 1);
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

        // For monthly_year mode (year-only filter with multiple municipalities), aggregate all municipalities
        if ($chartMode === 'monthly_year') {
            $datasets = $chartData['datasets'];
            
            // Sum all municipalities' data for each month
            $monthlyTotals = [];
            foreach ($datasets as $dataset) {
                if (isset($dataset['data'])) {
                    foreach ($dataset['data'] as $index => $value) {
                        if (!isset($monthlyTotals[$index])) {
                            $monthlyTotals[$index] = 0;
                        }
                        $monthlyTotals[$index] += $value;
                    }
                }
            }
            
            // Compare last two months with data
            $nonZeroData = array_filter($monthlyTotals, function($val) { return $val > 0; });
            
            if (count($nonZeroData) >= 2) {
                $values = array_values($nonZeroData);
                $lastValue = end($values);
                $previousValue = prev($values);
                
                if ($previousValue > 0) {
                    return round((($lastValue - $previousValue) / $previousValue) * 100, 1);
                }
            }
            return 0;
        }

        // For yearly mode, compare last two years
        if ($chartMode === 'yearly') {
            $labels = $chartData['labels'];
            $datasets = $chartData['datasets'];
            
            if (count($labels) >= 2 && !empty($datasets)) {
                // If filtering by specific municipality or crop with single dataset, compare just that
                if ((isset($filters['municipality']) || isset($filters['crop'])) && count($datasets) === 1) {
                    $data = $datasets[0]['data'];
                    if (count($data) >= 2) {
                        // Get non-zero values
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
                } else {
                    // Sum all municipalities' production for last two years based on year labels
                    $yearCount = count($labels);
                    $lastYearIndex = $yearCount - 1;
                    $previousYearIndex = $yearCount - 2;
                    
                    $lastYearTotal = 0;
                    $previousYearTotal = 0;
                    
                    foreach ($datasets as $dataset) {
                        if (isset($dataset['data'])) {
                            $data = $dataset['data'];
                            // Use the actual year indices instead of filtering non-zero
                            if (isset($data[$lastYearIndex])) {
                                $lastYearTotal += $data[$lastYearIndex];
                            }
                            if (isset($data[$previousYearIndex])) {
                                $previousYearTotal += $data[$previousYearIndex];
                            }
                        }
                    }
                    
                    if ($previousYearTotal > 0) {
                        return round((($lastYearTotal - $previousYearTotal) / $previousYearTotal) * 100, 1);
                    }
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
                            
                            $predictions[] = [
                                'year' => $year,
                                'month' => $month,
                                'municipality' => $municipality,
                                'crop_type' => $cropType,
                                'farm_type' => $farmType,
                                'area_harvested' => round($avgArea, 2),
                                'predicted_production' => round($productionMT, 2),
                                'confidence' => $prediction['prediction']['confidence'] ?? null
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

