<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Crop;
use App\Models\Farmer;
use App\Models\Municipality;
use App\Models\CropType;
use App\Services\PredictionService;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DataAnalyticsController extends Controller
{
    public function index()
    {
        // Get filter parameters
        $filterCrop = request('crop');
        $filterMunicipality = request('municipality');
        $filterMonth = request('month');
        $filterYear = request('year');
        
        // Build base query with filters
        $query = Crop::query();
        
        if ($filterCrop) {
            $query->where('crop', $filterCrop);
        }
        
        if ($filterMunicipality) {
            $query->where('municipality', $filterMunicipality);
        }
        
        if ($filterMonth) {
            $query->where('month', strtoupper(substr($filterMonth, 0, 3)));
        }
        
        if ($filterYear) {
            $query->where('year', $filterYear);
        }
        
        // Get key metrics from filtered database
        $totalFarmers = Farmer::count();
        $totalAreaHarvested = $query->clone()->sum('area_harvested');
        $averageYield = $query->clone()->avg('productivity');
        
        // Get top 3 crops by total production from filtered database
        $topCrops = $query->clone()
            ->select('crop', DB::raw('SUM(production) as total_production'))
            ->groupBy('crop')
            ->orderByDesc('total_production')
            ->limit(3)
            ->get();
        
        // Get most productive municipality from filtered database
        $topMunicipality = $query->clone()
            ->select('municipality', DB::raw('SUM(production) as total_production'))
            ->groupBy('municipality')
            ->orderByDesc('total_production')
            ->first();
        
        // Get all unique years from database (unfiltered for year selector)
        $allYears = Crop::select('year')
            ->distinct()
            ->orderBy('year')
            ->pluck('year')
            ->toArray();
        
        // Get all unique municipalities from database (unfiltered for municipality selector)
        $allMunicipalities = Crop::select('municipality')
            ->distinct()
            ->pluck('municipality')
            ->toArray();
        
        // Determine chart mode: monthly (if year selected) or yearly (default)
        $chartMode = $filterYear ? 'monthly' : 'yearly';
        
        if ($chartMode === 'monthly') {
            // Monthly view for selected year
            $monthOrder = ['JAN', 'FEB', 'MAR', 'APR', 'MAY', 'JUN', 'JUL', 'AUG', 'SEP', 'OCT', 'NOV', 'DEC'];
            $labels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            
            // Get municipalities for filtered data
            $municipalities = $query->clone()
                ->select('municipality')
                ->distinct()
                ->pluck('municipality')
                ->toArray();
            
            // Get production data by municipality and month
            $productionByMunicipalityMonth = $query->clone()
                ->select(
                    'municipality',
                    'month',
                    DB::raw('SUM(production) as total_production')
                )
                ->groupBy('municipality', 'month')
                ->orderBy('municipality')
                ->get()
                ->groupBy('municipality');
            
            // Format data for Chart.js (convert kg to metric tons)
            $trendChartData = [];
            foreach ($municipalities as $municipality) {
                $municipalityData = [];
                foreach ($monthOrder as $month) {
                    $production = $productionByMunicipalityMonth[$municipality] ?? collect();
                    $monthData = $production->firstWhere('month', $month);
                    $municipalityData[] = $monthData ? round($monthData->total_production / 1000, 2) : 0;
                }
                $trendChartData[$municipality] = $municipalityData;
            }
            
            $years = $labels; // Use month labels for x-axis
        } else {
            // Yearly view (default)
            $years = $query->clone()
                ->select('year')
                ->distinct()
                ->orderBy('year')
                ->pluck('year')
                ->toArray();
            
            $labels = $years;
            
            // Get municipalities for filtered data
            $municipalities = $query->clone()
                ->select('municipality')
                ->distinct()
                ->pluck('municipality')
                ->toArray();
            
            // Get production data by municipality and year
            $productionByMunicipalityYear = $query->clone()
                ->select(
                    'municipality',
                    'year',
                    DB::raw('SUM(production) as total_production')
                )
                ->groupBy('municipality', 'year')
                ->orderBy('municipality')
                ->orderBy('year')
                ->get()
                ->groupBy('municipality');
            
            // Format data for Chart.js (convert kg to metric tons)
            $trendChartData = [];
            foreach ($municipalities as $municipality) {
                $municipalityData = [];
                foreach ($years as $year) {
                    $production = $productionByMunicipalityYear[$municipality] ?? collect();
                    $yearData = $production->firstWhere('year', $year);
                    $municipalityData[] = $yearData ? round($yearData->total_production / 1000, 2) : 0;
                }
                $trendChartData[$municipality] = $municipalityData;
            }
        }
        
        // Get monthly demand data from actual database (filtered by year or current year)
        $selectedYear = $filterYear ?? date('Y');
        $monthOrder = ['JAN', 'FEB', 'MAR', 'APR', 'MAY', 'JUN', 'JUL', 'AUG', 'SEP', 'OCT', 'NOV', 'DEC'];
        $monthlyQuery = Crop::where('year', $selectedYear);
        
        if ($filterMunicipality) {
            $monthlyQuery->where('municipality', $filterMunicipality);
        }
        
        $monthlyProduction = $monthlyQuery
            ->select('month', DB::raw('SUM(production) as total_production'))
            ->groupBy('month')
            ->get()
            ->keyBy('month');
        
        $monthlyDemand = [];
        foreach ($monthOrder as $month) {
            $monthlyDemand[$month] = $monthlyProduction->has($month) 
                ? round($monthlyProduction[$month]->total_production / 1000, 2) 
                : 0;
        }
        
        // Calculate production trend percentage from filtered data
        $productionTrend = null;
        
        if ($chartMode === 'monthly' && $filterMonth) {
            // Compare current month vs previous month in the same year
            $currentMonth = strtoupper(substr($filterMonth, 0, 3));
            $monthOrderArray = ['JAN', 'FEB', 'MAR', 'APR', 'MAY', 'JUN', 'JUL', 'AUG', 'SEP', 'OCT', 'NOV', 'DEC'];
            $currentMonthIndex = array_search($currentMonth, $monthOrderArray);
            
            if ($currentMonthIndex > 0) {
                $previousMonth = $monthOrderArray[$currentMonthIndex - 1];
                $currentMonthProduction = $query->clone()->where('year', $selectedYear)->where('month', $currentMonth)->sum('production');
                $previousMonthProduction = Crop::where('year', $selectedYear)->where('month', $previousMonth)->sum('production');
                
                if ($previousMonthProduction > 0) {
                    $productionTrend = (($currentMonthProduction - $previousMonthProduction) / $previousMonthProduction) * 100;
                }
            }
        } else {
            // Compare current year vs previous year
            $currentYearProduction = $query->clone()->where('year', $selectedYear)->sum('production');
            $previousYearProduction = $query->clone()->where('year', $selectedYear - 1)->sum('production');
            
            if ($previousYearProduction > 0) {
                $productionTrend = (($currentYearProduction - $previousYearProduction) / $previousYearProduction) * 100;
            }
        }
        
        // Get last update date from database
        $lastUpdate = Crop::latest('updated_at')->first()?->updated_at ?? now();
        
        // Generate ML predictions for next period
        $predictions = $this->generatePredictions($filterMunicipality, $filterCrop, $filterYear);
        
        return view('admin.data-analytics', compact(
            'totalFarmers',
            'totalAreaHarvested',
            'averageYield',
            'topCrops',
            'topMunicipality',
            'years',
            'municipalities',
            'allYears',
            'allMunicipalities',
            'trendChartData',
            'monthlyDemand',
            'productionTrend',
            'lastUpdate',
            'filterCrop',
            'filterMunicipality',
            'filterMonth',
            'filterYear',
            'selectedYear',
            'chartMode',
            'predictions'
        ));
    }
    
    /**
     * Generate ML predictions based on filters and historical data
     */
    protected function generatePredictions($municipality = null, $crop = null, $year = null)
    {
        $predictionService = new PredictionService();
        
        // Check if prediction service is available
        if (!$predictionService->checkHealth()) {
            return [
                'available' => false,
                'message' => 'Prediction service is currently unavailable'
            ];
        }
        
        $predictions = [];
        
        // Get recent historical data to base predictions on
        $query = Crop::query();
        
        if ($municipality) {
            $query->where('municipality', $municipality);
        }
        
        if ($crop) {
            $query->where('crop', $crop);
        }
        
        // Get average area harvested for predictions
        $recentData = $query
            ->where('year', '>=', ($year ?? date('Y')) - 2)
            ->select('municipality', 'crop', 'farm_type', 'month', DB::raw('AVG(area_harvested) as avg_area'))
            ->groupBy('municipality', 'crop', 'farm_type', 'month')
            ->get();
        
        if ($recentData->isEmpty()) {
            return [
                'available' => false,
                'message' => 'Insufficient historical data for predictions'
            ];
        }
        
        // Generate predictions for top combinations
        foreach ($recentData->take(5) as $data) {
            $predictionInput = [
                'municipality' => $data->municipality,
                'farm_type' => $data->farm_type ?? 'IRRIGATED',
                'month' => $data->month,
                'crop' => $data->crop,
                'area_harvested' => round($data->avg_area, 2)
            ];
            
            $result = $predictionService->predictProduction($predictionInput);
            
            if (isset($result['success']) && $result['success'] && isset($result['predicted_production'])) {
                $predictions[] = [
                    'municipality' => ucwords(strtolower($data->municipality)),
                    'crop' => ucwords(strtolower($data->crop)),
                    'month' => $data->month,
                    'area_harvested' => round($data->avg_area, 2),
                    'predicted_production' => round($result['predicted_production'], 2),
                    'confidence' => 'High'
                ];
            }
        }
        
        return [
            'available' => true,
            'predictions' => $predictions,
            'count' => count($predictions)
        ];
    }
    
    public function exportSummary()
    {
        // Future implementation for exporting summary data
        return response()->json(['message' => 'Export functionality coming soon']);
    }
}
