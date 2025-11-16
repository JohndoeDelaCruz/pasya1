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
        $filterName = $request->input('name');
        $filterId = $request->input('id');
        $filterCrop = $request->input('crop');
        $filterStatus = $request->input('status');

        // Get unique crops for filter dropdown
        $crops = Crop::distinct()->pluck('crop')->sort()->values();
        
        // Get unique municipalities
        $municipalities = Crop::distinct()->pluck('municipality')->sort()->values();

        // Build query for subsidy data
        $subsidyQuery = Subsidy::query();

        // Apply filters
        if ($filterName) {
            $subsidyQuery->where('full_name', 'like', '%' . $filterName . '%');
        }
        if ($filterId) {
            $subsidyQuery->where('farmer_id', 'like', '%' . $filterId . '%');
        }
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
        // Get the latest year from database for predictions
        $latestYear = Crop::max('year') ?? now()->year;
        $currentMonth = now()->month;
        
        // Get top crops by historical area planted (last 2 years)
        $cropData = Crop::select(
            'crop',
            DB::raw('SUM(area_planted) as total_area_planted'),
            DB::raw('AVG(area_planted) as avg_area_planted'),
            DB::raw('SUM(production) as total_production')
        )
        ->where('year', '>=', $latestYear - 1)
        ->groupBy('crop')
        ->orderByDesc('total_area_planted')
        ->limit(7)
        ->get();

        $labels = [];
        $needed = [];
        $allocated = [];

        // Seed rates (kg per hectare) based on crop type
        $seedRates = [
            'whitepotato' => 1500,  // White potato: 1500 kg/ha
            'cabbage' => 0.5,        // Cabbage: 0.5 kg/ha
            'carrots' => 3,          // Carrots: 3 kg/ha
            'chinesecabbage' => 2,   // Chinese cabbage: 2 kg/ha
            'snapbeans' => 50,       // Snap beans: 50 kg/ha
            'sweetpepper' => 1,      // Sweet pepper: 1 kg/ha
            'lettuce' => 1.5,        // Lettuce: 1.5 kg/ha
        ];

        foreach ($cropData as $crop) {
            $cropName = strtolower(str_replace(' ', '', $crop->crop));
            $labels[] = strtoupper($crop->crop);
            
            // Get seed rate for this crop, default to 10 kg/ha if not specified
            $seedRate = $seedRates[$cropName] ?? 10;
            
            // Calculate needed seeds based on projected area to be planted next year
            // Use average area from recent years as projection
            $projectedArea = $crop->avg_area_planted;
            $neededAmount = $projectedArea * $seedRate;
            
            // Calculate allocated seeds from subsidy records
            // Subsidy amount is in pesos, estimate seed allocation based on actual records
            $subsidyRecords = Subsidy::where('crop', $crop->crop)
                ->where('subsidy_status', 'Approved')
                ->get();
            
            if ($subsidyRecords->count() > 0) {
                // Calculate allocated based on subsidy area planted
                $totalSubsidyArea = $subsidyRecords->sum('area_planted');
                $allocatedAmount = $totalSubsidyArea * $seedRate;
            } else {
                // If no subsidy data, allocate 50-80% based on crop priority
                // Priority based on production success rate
                $successRate = min(0.80, max(0.50, 
                    $crop->total_production / ($crop->total_area_planted * 1000)
                ));
                $allocatedAmount = $neededAmount * $successRate;
            }
            
            $needed[] = round($neededAmount, 2);
            $allocated[] = round($allocatedAmount, 2);
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

    public function storeResource(Request $request)
    {
        $request->validate([
            'resource_type' => 'required|string',
            'quantity' => 'required|numeric|min:0',
            'municipality' => 'required|string'
        ]);

        // Store resource allocation in database
        // You can create a Resource model or store in a resources table
        DB::table('resource_allocations')->insert([
            'resource_type' => $request->resource_type,
            'quantity' => $request->quantity,
            'municipality' => $request->municipality,
            'created_by' => auth()->user()->name ?? 'admin',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return redirect()->route('admin.recommendations')
            ->with('success', 'Resource allocated successfully!');
    }
}
