<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Crop;
use App\Models\Subsidy;
use App\Services\WeatherService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RecommendationsController extends Controller
{
    protected $weatherService;

    public function __construct(WeatherService $weatherService)
    {
        $this->weatherService = $weatherService;
    }

    public function index(Request $request)
    {
        // Get filter parameters for subsidies
        $filterCrop = $request->input('crop');
        $filterStatus = $request->input('status');

        // Get unique crops for filter dropdown
        $crops = Crop::distinct()->pluck('crop')->sort()->values();
        
        // Get unique municipalities
        $municipalities = Crop::distinct()->pluck('municipality')->sort()->values();

        // Build query for subsidy data
        $subsidyQuery = Subsidy::query();

        // Apply filters
        if ($filterCrop) {
            $subsidyQuery->where('crop', $filterCrop);
        }
        if ($filterStatus) {
            $subsidyQuery->where('subsidy_status', $filterStatus);
        }

        // Get paginated subsidy data
        $subsidies = $subsidyQuery->orderByDesc('updated_at')->paginate(10);

        // Get allocation data for bar chart
        $allocationData = $this->getAllocationData();

        // Get weather data for Benguet municipalities
        $municipalityWeather = [
            $this->weatherService->getForecast('Atok'),
            $this->weatherService->getForecast('Bakun'),
            $this->weatherService->getForecast('Bokod')
        ];

        // Get hourly forecast (using first municipality)
        $hourlyForecast = $this->weatherService->getHourlyForecast('Atok');

        // Get optimal planting window and climate risk
        $optimalWindow = $this->weatherService->getOptimalPlantingWindow($hourlyForecast);
        $climateRisk = $this->weatherService->getClimateRisk($municipalityWeather[0]['forecast']);

        // Best crops for the region
        $bestCrops = $this->getBestCrops();

        return view('admin.recommendations', [
            'crops' => $crops,
            'municipalities' => $municipalities,
            'filterCrop' => $filterCrop,
            'filterStatus' => $filterStatus,
            'subsidies' => $subsidies,
            'allocationData' => $allocationData,
            'municipalityWeather' => $municipalityWeather,
            'hourlyForecast' => $hourlyForecast,
            'optimalWindow' => $optimalWindow,
            'climateRisk' => $climateRisk,
            'bestCrops' => $bestCrops
        ]);
    }

    private function getAllocationData()
    {
        // Calculate seed allocation based on crop data
        $cropData = Crop::select(
            'crop',
            DB::raw('AVG(area_planted) as avg_area'),
            DB::raw('COUNT(*) as records')
        )
        ->groupBy('crop')
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

    private function getBestCrops()
    {
        // Get top performing crops based on productivity
        $topCrops = Crop::select('crop', DB::raw('AVG(productivity) as avg_productivity'))
            ->groupBy('crop')
            ->orderByDesc('avg_productivity')
            ->limit(3)
            ->get()
            ->pluck('crop')
            ->toArray();

        if (empty($topCrops)) {
            return 'Beans, Cabbage, Broccoli'; // Default
        }

        return implode(', ', $topCrops);
    }

    public function storeSubsidy(Request $request)
    {
        $request->validate([
            'full_name' => 'required|string|max:255',
            'farmer_id' => 'required|string|unique:subsidies,farmer_id',
            'crop' => 'required|string',
            'subsidy_status' => 'nullable|in:Approved,Pending,Rejected',
            'subsidy_amount' => 'nullable|numeric|min:0',
            'municipality' => 'required|string',
            'farm_type' => 'required|in:Rainfed,Irrigated',
            'year' => 'required|integer|min:2020|max:2050',
            'area_planted' => 'required|numeric|min:0',
            'area_harvested' => 'required|numeric|min:0',
            'production' => 'required|numeric|min:0',
            'productivity' => 'nullable|numeric|min:0'
        ]);

        // Calculate productivity if not provided
        $productivity = $request->productivity;
        if (!$productivity && $request->area_harvested > 0) {
            $productivity = ($request->production * 1000) / $request->area_harvested;
        }

        // Create subsidy record
        Subsidy::create([
            'full_name' => $request->full_name,
            'farmer_id' => $request->farmer_id,
            'crop' => $request->crop,
            'subsidy_status' => $request->subsidy_status ?? 'Pending',
            'subsidy_amount' => $request->subsidy_amount,
            'municipality' => $request->municipality,
            'farm_type' => $request->farm_type,
            'year' => $request->year,
            'area_planted' => $request->area_planted,
            'area_harvested' => $request->area_harvested,
            'production' => $request->production,
            'productivity' => $productivity
        ]);

        return redirect()->route('admin.recommendations')
            ->with('success', 'Subsidy allocated successfully!');
    }
}
