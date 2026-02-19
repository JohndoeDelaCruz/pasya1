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

        return view('admin.recommendations', [
            'crops' => $crops,
            'municipalities' => $municipalities,
            'filterCrop' => $filterCrop,
            'filterStatus' => $filterStatus,
            'subsidies' => $subsidies,
            'allocationData' => $allocationData,
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
                // Priority based on average productivity (production / area)
                // Normalize productivity to 0.5-0.8 range for allocation percentage
                $avgProductivity = $crop->total_area_planted > 0 
                    ? $crop->total_production / $crop->total_area_planted 
                    : 0;
                // Assume typical productivity range is 5-20 mt/ha, normalize to 0.5-0.8
                $successRate = min(0.80, max(0.50, 0.5 + ($avgProductivity / 20) * 0.3));
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
            'farmer_id' => 'required|string',
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

        // Check if farmer_id already exists
        $existingSubsidy = Subsidy::where('farmer_id', $request->farmer_id)->first();
        if ($existingSubsidy) {
            return back()->withInput()->with('error', 
                'This Farmer ID (' . $request->farmer_id . ') already exists! ' .
                'Farmer: ' . $existingSubsidy->full_name . '. ' .
                'Please use a different Farmer ID or update the existing record.');
        }

        // Calculate productivity if not provided
        $productivity = $request->productivity;
        if (!$productivity && $request->area_harvested > 0) {
            $productivity = ($request->production * 1000) / $request->area_harvested;
        }

        try {
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
                
        } catch (\Illuminate\Database\QueryException $e) {
            // Handle unique constraint violation at database level
            if ($e->getCode() == 23000) {
                return back()->withInput()->with('error', 
                    'This Farmer ID already exists in the database. Please use a different Farmer ID.');
            }
            
            return back()->withInput()->with('error', 'Failed to allocate subsidy: ' . $e->getMessage());
        }
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

    /**
     * Get weather data for a specific municipality via AJAX
     */
    public function getWeather(Request $request)
    {
        $municipality = $request->input('municipality', 'La Trinidad');
        
        // Validate municipality
        $validMunicipalities = ['Atok', 'Bakun', 'Bokod', 'Buguias', 'Itogon', 'Kabayan', 'Kapangan', 'Kibungan', 'La Trinidad', 'Mankayan', 'Sablan', 'Tuba', 'Tublay'];
        
        if (!in_array($municipality, $validMunicipalities)) {
            return response()->json(['error' => 'Invalid municipality'], 400);
        }
        
        try {
            $forecast = $this->weatherService->getForecast($municipality, 4);
            $hourly = $this->weatherService->getHourlyForecast($municipality, 6);
            $current = $this->weatherService->getCurrentConditions($municipality);
            
            return response()->json([
                'success' => true,
                'municipality' => $municipality,
                'current' => $current,
                'forecast' => $forecast,
                'hourly' => $hourly,
                'optimalWindow' => $this->weatherService->getOptimalPlantingWindow($hourly),
                'climateRisk' => $this->weatherService->getClimateRisk($forecast['forecast'] ?? [])
            ]);
        } catch (\Exception $e) {
            Log::error("Weather API error: " . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch weather data'], 500);
        }
    }

    /**
     * Get weather for all municipalities
     */
    public function getAllWeather()
    {
        $municipalities = ['Atok', 'Bakun', 'Bokod', 'Buguias', 'Itogon', 'Kabayan', 'Kapangan', 'Kibungan', 'La Trinidad', 'Mankayan', 'Sablan', 'Tuba', 'Tublay'];
        
        $weatherData = [];
        
        foreach ($municipalities as $municipality) {
            try {
                $weatherData[] = [
                    'municipality' => $municipality,
                    'current' => $this->weatherService->getCurrentConditions($municipality),
                    'forecast' => $this->weatherService->getForecast($municipality, 4)
                ];
            } catch (\Exception $e) {
                Log::warning("Weather API error for {$municipality}: " . $e->getMessage());
                $weatherData[] = [
                    'municipality' => $municipality,
                    'error' => true
                ];
            }
        }
        
        return response()->json([
            'success' => true,
            'data' => $weatherData
        ]);
    }
}
