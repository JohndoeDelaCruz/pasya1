<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Crop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RecommendationsController extends Controller
{
    public function index(Request $request)
    {
        // Get filter parameters
        $selectedMunicipality = $request->input('municipality');
        $selectedCrop = $request->input('crop');
        $selectedSubsidy = $request->input('subsidy_status');
        $selectedSubsidyAmount = $request->input('subsidy_amount');

        // Get unique municipalities and crops for filters
        $municipalities = Crop::distinct()->pluck('municipality')->sort()->values();
        $crops = Crop::distinct()->pluck('crop')->sort()->values();

        // Build query for policy dashboard data
        $query = Crop::select(
            'municipality',
            'crop',
            DB::raw('COUNT(DISTINCT CONCAT(year, "-", month)) as record_count'),
            DB::raw('AVG(area_planted) as avg_area_planted'),
            DB::raw('AVG(area_harvested) as avg_area_harvested'),
            DB::raw('AVG(production) as avg_production'),
            DB::raw('AVG(productivity) as avg_productivity')
        );

        // Apply filters
        if ($selectedMunicipality) {
            $query->where('municipality', $selectedMunicipality);
        }
        if ($selectedCrop) {
            $query->where('crop', $selectedCrop);
        }

        $policyData = $query->groupBy('municipality', 'crop')
            ->orderByDesc('avg_production')
            ->paginate(10);

        // Get weather data (mock data for now)
        $weatherData = $this->getWeatherData();

        // Get climate resilience recommendations
        $climateResilience = $this->getClimateResilience($selectedMunicipality);

        // Get disaster warning
        $disasterWarning = $this->getDisasterWarning();

        // Get best crops recommendation
        $bestCrops = $this->getBestCrops($selectedMunicipality);

        // Get climate rule recommendation
        $climateRule = $this->getClimateRule();

        // Get allocation data for bar chart
        $allocationData = $this->getAllocationData($selectedMunicipality);

        return view('admin.recommendations', [
            'municipalities' => $municipalities,
            'crops' => $crops,
            'selectedMunicipality' => $selectedMunicipality,
            'selectedCrop' => $selectedCrop,
            'selectedSubsidy' => $selectedSubsidy,
            'selectedSubsidyAmount' => $selectedSubsidyAmount,
            'policyData' => $policyData,
            'weatherData' => $weatherData,
            'climateResilience' => $climateResilience,
            'disasterWarning' => $disasterWarning,
            'bestCrops' => $bestCrops,
            'climateRule' => $climateRule,
            'allocationData' => $allocationData
        ]);
    }

    private function getAllocationData($municipality = null)
    {
        // Calculate seed allocation based on crop data
        $query = Crop::select(
            'crop',
            DB::raw('AVG(area_planted) as avg_area'),
            DB::raw('COUNT(*) as records')
        );

        if ($municipality) {
            $query->where('municipality', $municipality);
        }

        $cropData = $query->groupBy('crop')
            ->orderByDesc('avg_area')
            ->limit(6)
            ->get();

        $labels = [];
        $needed = [];
        $allocated = [];

        foreach ($cropData as $crop) {
            $labels[] = $crop->crop;
            // Mock calculation: area * seed rate
            $neededAmount = $crop->avg_area * 10; // 10kg per hectare
            $allocatedAmount = $neededAmount * 0.7; // 70% allocated
            
            $needed[] = round($neededAmount);
            $allocated[] = round($allocatedAmount);
        }

        return [
            'labels' => $labels,
            'needed' => $needed,
            'allocated' => $allocated
        ];
    }

    private function getWeatherData()
    {
        // Mock weather data - in production, integrate with weather API
        return [
            'today' => [
                'high' => 31,
                'low' => 27,
                'condition' => 'Sunny',
                'icon' => '☀️'
            ],
            'forecast' => [
                ['day' => 'Mon 4', 'high' => 33, 'low' => 27, 'condition' => 'Partly Cloudy'],
                ['day' => 'Tue 5', 'high' => 32, 'low' => 28, 'condition' => 'Rainy'],
                ['day' => 'Wed 6', 'high' => 30, 'low' => 26, 'condition' => 'Cloudy'],
                ['day' => 'Thu 7', 'high' => 31, 'low' => 27, 'condition' => 'Partly Cloudy'],
            ],
            'weekly' => [
                ['date' => '12/1', 'temp' => 19, 'condition' => 'Cloudy'],
                ['date' => '13/1', 'temp' => 19, 'condition' => 'Cloudy'],
                ['date' => '14/1', 'temp' => 22, 'condition' => 'Partly Cloudy'],
                ['date' => '15/1', 'temp' => 23, 'condition' => 'Partly Cloudy'],
                ['date' => '16/1', 'temp' => 24, 'condition' => 'Partly Cloudy'],
                ['date' => '17/1', 'temp' => 24, 'condition' => 'Partly Cloudy'],
            ]
        ];
    }

    private function getClimateResilience($municipality = null)
    {
        return [
            'title' => 'Climate Resilience',
            'description' => 'Climate resilience is very important especially in farm. These are the things you should do...',
            'action' => 'Read More'
        ];
    }

    private function getDisasterWarning()
    {
        return [
            'title' => 'Disaster Planting Window',
            'description' => 'A disaster is coming, you should not plant in Bocaue, Bulacan...',
            'action' => 'See More'
        ];
    }

    private function getBestCrops($municipality = null)
    {
        // Get top performing crops
        $query = Crop::select(
            'crop',
            DB::raw('AVG(productivity) as avg_productivity'),
            DB::raw('SUM(production) as total_production')
        );

        if ($municipality) {
            $query->where('municipality', $municipality);
        }

        $topCrops = $query->groupBy('crop')
            ->orderByDesc('avg_productivity')
            ->limit(3)
            ->get();

        return [
            'title' => 'Best Crops',
            'description' => 'Best crops for ' . ($municipality ?: 'your area') . ': ' . $topCrops->pluck('crop')->implode(', '),
            'action' => 'View Details'
        ];
    }

    private function getClimateRule()
    {
        return [
            'title' => 'Climate Rule',
            'description' => 'The climate is hot in Bocaue, do not plant crops that are...',
            'action' => 'See More'
        ];
    }

    public function updateSubsidy(Request $request, $id)
    {
        $request->validate([
            'subsidy_status' => 'required|in:Approved,Pending,Rejected',
            'subsidy_amount' => 'nullable|numeric|min:0'
        ]);

        // Update logic here (you'll need to create a subsidies table)
        // For now, just return success
        
        return response()->json([
            'success' => true,
            'message' => 'Subsidy updated successfully'
        ]);
    }

    public function storeSubsidy(Request $request)
    {
        $request->validate([
            'municipality' => 'required|string',
            'farm_type' => 'required|in:Rainfed,Irrigated',
            'year' => 'required|integer|min:2020|max:2050',
            'crop' => 'required|string',
            'area_planted' => 'required|numeric|min:0',
            'area_harvested' => 'required|numeric|min:0',
            'production' => 'required|numeric|min:0',
            'productivity' => 'nullable|numeric|min:0'
        ]);

        // Calculate productivity if not provided
        $productivity = $request->productivity;
        if (!$productivity && $request->area_harvested > 0) {
            $productivity = $request->production / $request->area_harvested;
        }

        // Store subsidy data (you can create a subsidies table or add to crops table)
        // For now, we'll create a crop record as a placeholder
        Crop::create([
            'municipality' => $request->municipality,
            'farm_type' => $request->farm_type,
            'year' => $request->year,
            'month' => strtoupper(date('M')), // Current month
            'crop' => $request->crop,
            'area_planted' => $request->area_planted,
            'area_harvested' => $request->area_harvested,
            'production' => $request->production * 1000, // Convert to kg
            'productivity' => $productivity
        ]);

        return redirect()->route('admin.recommendations')
            ->with('success', 'Subsidy allocated successfully!');
    }
}
