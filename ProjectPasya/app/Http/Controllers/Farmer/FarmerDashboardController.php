<?php

namespace App\Http\Controllers\Farmer;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\CropType;
use App\Models\CropProduction;
use App\Models\CropPlan;
use App\Models\Crop;
use App\Services\PredictionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class FarmerDashboardController extends Controller
{
    /**
     * Show the farmer dashboard
     */
    public function dashboard()
    {
        $farmer = Auth::guard('farmer')->user();
        
        // Get announcements for this farmer
        $announcements = Announcement::active()
            ->forFarmers()
            ->forMunicipality($farmer->municipality ?? null)
            ->orderBy('priority', 'desc')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();
        
        // Get farmer's events/tasks
        $events = $this->getFarmerEvents($farmer);
        
        // Get weather data (placeholder - can integrate with weather API)
        $weather = $this->getWeatherData($farmer->municipality ?? 'Buguias');
        
        // Get price watch data
        $prices = $this->getPriceWatchData();
        
        // Get stats
        $stats = [
            'weather_temp' => $weather['temperature'] ?? 28,
            'events_count' => count($events),
            'active_crops' => $this->getActiveCropsCount($farmer),
            'announcements_count' => $announcements->count(),
        ];
        
        return view('farmers.dashboard', compact('announcements', 'events', 'weather', 'prices', 'stats'));
    }
    
    /**
     * Show the calendar page
     */
    public function calendar()
    {
        $farmer = Auth::guard('farmer')->user();
        
        $announcements = Announcement::active()
            ->forFarmers()
            ->forMunicipality($farmer->municipality ?? null)
            ->orderBy('priority', 'desc')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();
        
        // Get all farmer events for calendar (including crop plans)
        $events = $this->getFarmerEvents($farmer, true);
        
        // Get crop types for the planning form
        $cropTypes = CropType::active()->orderBy('name')->get();
        
        // Get farmer's active crop plans
        $cropPlans = CropPlan::where('farmer_id', $farmer->id)
            ->active()
            ->with('cropType')
            ->orderBy('planting_date')
            ->get();
        
        return view('farmers.calendar', compact('announcements', 'events', 'cropTypes', 'cropPlans'));
    }
    
    /**
     * Show the price watch page
     */
    public function priceWatch()
    {
        $farmer = Auth::guard('farmer')->user();
        
        // Get all crop prices with mock data (can be replaced with real API)
        $prices = $this->getAllPrices();
        
        // Get price trends
        $trends = $this->getPriceTrends();
        
        return view('farmers.price-watch', compact('prices', 'trends'));
    }
    
    /**
     * Show the harvest history page
     */
    public function harvestHistory()
    {
        $farmer = Auth::guard('farmer')->user();
        
        // Get crops for this farmer's municipality
        $crops = CropProduction::where('municipality', strtoupper($farmer->municipality ?? 'BUGUIAS'))
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->take(50)
            ->get();
        
        // Get available crop types with all needed info
        $cropTypes = CropType::active()->orderBy('name')->get()->map(function ($crop) {
            return [
                'id' => $crop->id,
                'name' => $crop->name,
                'category' => $crop->category,
                'description' => $crop->description,
                'days_to_harvest' => $crop->days_to_harvest_value,
                'average_yield_per_hectare' => $crop->average_yield_value,
                'growth_cycle' => $this->formatGrowthCycle($crop->days_to_harvest_value),
            ];
        });
        
        // Get farmer's crop plans (harvest history)
        $cropPlans = CropPlan::where('farmer_id', $farmer->id)
            ->with('cropType')
            ->orderBy('planting_date', 'desc')
            ->get()
            ->map(function ($plan) {
                return [
                    'id' => $plan->id,
                    'cropType' => $plan->crop_name,
                    'datePlanted' => $plan->planting_date->format('M d, Y'),
                    'dateHarvested' => $plan->status === 'harvested' 
                        ? $plan->expected_harvest_date->format('M d, Y') 
                        : '--',
                    'expectedHarvest' => $plan->expected_harvest_date->format('M d, Y'),
                    'status' => $plan->status === 'harvested' ? 'Completed' : 'Growing',
                    'area' => $plan->area_hectares,
                    'predictedProduction' => $plan->predicted_production,
                    'plan_status' => $plan->status,
                ];
            });
        
        // Get production summary
        $summary = $this->getProductionSummary($farmer);
        
        return view('farmers.harvest-history', compact('crops', 'cropTypes', 'cropPlans', 'summary'));
    }
    
    /**
     * Format days to harvest as growth cycle string
     */
    private function formatGrowthCycle(int $days): string
    {
        $months = $days / 30;
        if ($months < 2) {
            return '1-2 months';
        } elseif ($months < 3) {
            return '2-3 months';
        } elseif ($months < 4) {
            return '3-4 months';
        } elseif ($months < 5) {
            return '4-5 months';
        } else {
            return '5-6 months';
        }
    }
    
    /**
     * Get farmer's calendar events
     */
    private function getFarmerEvents($farmer, $allEvents = false)
    {
        $events = [];
        $today = now();
        
        // Sample farming events based on crop cycles (replace with database events later)
        $farmingEvents = [
            [
                'date' => $today->copy()->addDays(2)->format('Y-m-d'),
                'title' => 'Claim fertilizer',
                'type' => 'claim',
                'description' => 'Fertilizer subsidy available at the Municipal Agriculture Office. Bring your ID.',
            ],
            [
                'date' => $today->copy()->addDays(6)->format('Y-m-d'),
                'title' => 'Plant Carrots',
                'type' => 'plant',
                'description' => 'Time to plant carrot seeds in your prepared beds. Make sure soil is well-drained.',
            ],
            [
                'date' => $today->format('Y-m-d'),
                'title' => 'Harvest Cabbage',
                'type' => 'harvest',
                'description' => 'Your cabbage is ready for harvest today. Best to harvest in the early morning.',
            ],
            [
                'date' => $today->copy()->addDays(10)->format('Y-m-d'),
                'title' => 'Plant Beans',
                'type' => 'plant',
                'description' => 'Start planting string beans in prepared beds.',
            ],
            [
                'date' => $today->copy()->addDays(14)->format('Y-m-d'),
                'title' => 'Harvest Broccoli',
                'type' => 'harvest',
                'description' => 'Broccoli is ready for harvest.',
            ],
            [
                'date' => $today->copy()->addDays(20)->format('Y-m-d'),
                'title' => 'Claim seeds',
                'type' => 'claim',
                'description' => 'Collect your free seeds from the Municipal Agriculture Office.',
            ],
        ];
        
        // Format sample events for JavaScript
        foreach ($farmingEvents as $event) {
            $events[$event['date']][] = [
                'title' => $event['title'],
                'type' => $event['type'],
                'description' => $event['description'],
            ];
        }
        
        // Add events from farmer's crop plans (database)
        $cropPlans = CropPlan::where('farmer_id', $farmer->id)
            ->whereIn('status', ['planned', 'planted', 'growing'])
            ->get();
        
        foreach ($cropPlans as $plan) {
            // Add planting event
            $plantingKey = $plan->planting_date->format('Y-m-d');
            if (!isset($events[$plantingKey])) {
                $events[$plantingKey] = [];
            }
            $events[$plantingKey][] = [
                'title' => "Plant {$plan->crop_name}",
                'type' => 'plant',
                'description' => "Plant {$plan->crop_name} on {$plan->area_hectares} hectares. Expected harvest: {$plan->expected_harvest_date->format('M d, Y')}. Predicted production: {$plan->formatted_production}",
                'crop_plan_id' => $plan->id,
                'area' => $plan->area_hectares,
                'predicted_production' => $plan->predicted_production,
            ];
            
            // Add harvest event (EDOH)
            $harvestKey = $plan->expected_harvest_date->format('Y-m-d');
            if (!isset($events[$harvestKey])) {
                $events[$harvestKey] = [];
            }
            $events[$harvestKey][] = [
                'title' => "Harvest {$plan->crop_name}",
                'type' => 'harvest',
                'description' => "Expected harvest of {$plan->crop_name} from {$plan->area_hectares} ha. Predicted production: {$plan->formatted_production}",
                'crop_plan_id' => $plan->id,
                'area' => $plan->area_hectares,
                'predicted_production' => $plan->predicted_production,
                'is_edoh' => true,
            ];
        }
        
        return $events;
    }
    
    /**
     * Get weather data (placeholder)
     */
    private function getWeatherData($municipality)
    {
        // In production, integrate with weather API (PAGASA or OpenWeatherMap)
        return [
            'temperature' => 28,
            'feels_like' => 32,
            'condition' => 'Partly Cloudy',
            'humidity' => 75,
            'wind_speed' => 12,
            'high' => 31,
            'low' => 22,
            'location' => $municipality . ', Benguet',
        ];
    }
    
    /**
     * Get price watch data for dashboard
     */
    private function getPriceWatchData()
    {
        // Mock data - replace with real price API or database
        return [
            [
                'name' => 'Cabbage',
                'emoji' => '🥬',
                'price' => 77.43,
                'change' => -24.00,
                'unit' => 'kg',
            ],
            [
                'name' => 'Chinese Cabbage',
                'emoji' => '🥬',
                'price' => 149.00,
                'change' => 16.00,
                'unit' => 'kg',
            ],
            [
                'name' => 'Carrots',
                'emoji' => '🥕',
                'price' => 80.00,
                'change' => -3.00,
                'unit' => 'kg',
            ],
        ];
    }
    
    /**
     * Get all crop prices
     */
    private function getAllPrices()
    {
        // More comprehensive price list with specifications
        return [
            ['name' => 'Cabbage', 'emoji' => '🥬', 'specification' => '2 heads/kg', 'price' => 77.43, 'change' => -24.00, 'unit' => 'kg', 'category' => 'Leafy Vegetables'],
            ['name' => 'Chinese Cabbage', 'emoji' => '🥬', 'specification' => '1 pc/kg', 'price' => 149.00, 'change' => 10.00, 'unit' => 'kg', 'category' => 'Leafy Vegetables'],
            ['name' => 'Carrots', 'emoji' => '🥕', 'specification' => '6 pcs/kg', 'price' => 80.00, 'change' => -3.00, 'unit' => 'kg', 'category' => 'Root Vegetables'],
            ['name' => 'Sweet Peas', 'emoji' => '🫛', 'specification' => '50 pcs/kg', 'price' => 680.00, 'change' => 62.00, 'unit' => 'kg', 'category' => 'Legumes'],
            ['name' => 'Potato', 'emoji' => '🥔', 'specification' => '4 pcs/kg', 'price' => 145.45, 'change' => -2.00, 'unit' => 'kg', 'category' => 'Root Vegetables'],
            ['name' => 'Baguio Beans', 'emoji' => '🫛', 'specification' => '60 pcs/kg', 'price' => 119.31, 'change' => -1.00, 'unit' => 'kg', 'category' => 'Legumes'],
            ['name' => 'Cauliflower', 'emoji' => '🥦', 'specification' => '2 heads/kg', 'price' => 237.00, 'change' => 30.00, 'unit' => 'kg', 'category' => 'Cruciferous'],
            ['name' => 'Lettuce', 'emoji' => '🥗', 'specification' => '4 pcs/kg', 'price' => 160.00, 'change' => -70.00, 'unit' => 'kg', 'category' => 'Leafy Vegetables'],
            ['name' => 'Broccoli', 'emoji' => '🥦', 'specification' => '2 heads/kg', 'price' => 380.00, 'change' => 46.00, 'unit' => 'kg', 'category' => 'Cruciferous'],
            ['name' => 'Radish', 'emoji' => '🥕', 'specification' => '5 pcs/kg', 'price' => 229.00, 'change' => 0.00, 'unit' => 'kg', 'category' => 'Root Vegetables'],
            ['name' => 'Tomatoes', 'emoji' => '🍅', 'specification' => '8 pcs/kg', 'price' => 45.00, 'change' => -8.00, 'unit' => 'kg', 'category' => 'Fruit Vegetables'],
            ['name' => 'Bell Pepper', 'emoji' => '🫑', 'specification' => '5 pcs/kg', 'price' => 120.00, 'change' => 10.00, 'unit' => 'kg', 'category' => 'Fruit Vegetables'],
            ['name' => 'Sayote', 'emoji' => '🥒', 'specification' => '3 pcs/kg', 'price' => 35.00, 'change' => 0.00, 'unit' => 'kg', 'category' => 'Fruit Vegetables'],
            ['name' => 'String Beans', 'emoji' => '🫛', 'specification' => '40 pcs/kg', 'price' => 60.00, 'change' => 8.00, 'unit' => 'kg', 'category' => 'Legumes'],
            ['name' => 'Snap Peas', 'emoji' => '🫛', 'specification' => '45 pcs/kg', 'price' => 90.00, 'change' => -12.00, 'unit' => 'kg', 'category' => 'Legumes'],
        ];
    }
    
    /**
     * Get price trends for charts
     */
    private function getPriceTrends()
    {
        // Mock trend data for charts
        $months = [];
        for ($i = 5; $i >= 0; $i--) {
            $months[] = now()->subMonths($i)->format('M');
        }
        
        return [
            'labels' => $months,
            'datasets' => [
                [
                    'label' => 'Cabbage',
                    'data' => [85, 92, 88, 101, 95, 77],
                    'color' => '#22c55e',
                ],
                [
                    'label' => 'Carrots',
                    'data' => [75, 78, 82, 79, 83, 80],
                    'color' => '#f97316',
                ],
                [
                    'label' => 'Potatoes',
                    'data' => [55, 58, 62, 60, 63, 65],
                    'color' => '#eab308',
                ],
            ],
        ];
    }
    
    /**
     * Get active crops count for farmer
     */
    private function getActiveCropsCount($farmer)
    {
        return CropProduction::where('municipality', strtoupper($farmer->municipality ?? 'BUGUIAS'))
            ->where('year', now()->year)
            ->distinct('crop')
            ->count('crop');
    }
    
    /**
     * Get production summary for farmer
     */
    private function getProductionSummary($farmer)
    {
        $municipality = strtoupper($farmer->municipality ?? 'BUGUIAS');
        $currentYear = now()->year;
        
        $totalProduction = CropProduction::where('municipality', $municipality)
            ->where('year', $currentYear)
            ->sum('production');
        
        $totalArea = CropProduction::where('municipality', $municipality)
            ->where('year', $currentYear)
            ->sum('area_harvested');
        
        $cropCount = CropProduction::where('municipality', $municipality)
            ->where('year', $currentYear)
            ->distinct('crop')
            ->count('crop');
        
        return [
            'total_production' => number_format($totalProduction, 2),
            'total_area' => number_format($totalArea, 2),
            'crop_count' => $cropCount,
            'year' => $currentYear,
        ];
    }
    
    /**
     * API: Get events for a specific month
     */
    public function getEvents(Request $request)
    {
        $farmer = Auth::guard('farmer')->user();
        $month = $request->get('month', now()->month);
        $year = $request->get('year', now()->year);
        
        $events = $this->getFarmerEvents($farmer, true);
        
        return response()->json([
            'success' => true,
            'events' => $events,
        ]);
    }
    
    /**
     * API: Get current prices
     */
    public function getPrices(Request $request)
    {
        $prices = $this->getAllPrices();
        
        return response()->json([
            'success' => true,
            'prices' => $prices,
            'updated_at' => now()->format('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Store a new crop plan
     */
    public function storeCropPlan(Request $request)
    {
        $farmer = Auth::guard('farmer')->user();
        
        $validated = $request->validate([
            'crop_type_id' => 'required|exists:crop_types,id',
            'planting_date' => 'required|date|after_or_equal:today',
            'area_hectares' => 'required|numeric|min:0.01|max:1000',
            'farm_type' => 'nullable|string|in:IRRIGATED,RAINFED',
            'notes' => 'nullable|string|max:500',
        ]);
        
        try {
            $cropType = CropType::findOrFail($validated['crop_type_id']);
            $plantingDate = Carbon::parse($validated['planting_date']);
            $areaHectares = floatval($validated['area_hectares']);
            $farmType = $validated['farm_type'] ?? 'IRRIGATED';
            
            // Calculate Expected Date of Harvest (EDOH)
            $expectedHarvestDate = $cropType->calculateHarvestDate($plantingDate);
            
            // Get predicted production using ML API or fallback to simple calculation
            $predictedProduction = $this->getPredictedProduction(
                $cropType->name,
                $farmer->municipality ?? 'BUGUIAS',
                $farmType,
                $plantingDate,
                $areaHectares
            );
            
            // Create the crop plan
            $cropPlan = CropPlan::create([
                'farmer_id' => $farmer->id,
                'crop_type_id' => $cropType->id,
                'crop_name' => $cropType->name,
                'planting_date' => $plantingDate,
                'expected_harvest_date' => $expectedHarvestDate,
                'area_hectares' => $areaHectares,
                'predicted_production' => $predictedProduction,
                'municipality' => strtoupper($farmer->municipality ?? 'BUGUIAS'),
                'farm_type' => $farmType,
                'status' => 'planned',
                'notes' => $validated['notes'] ?? null,
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Crop plan created successfully!',
                'data' => [
                    'id' => $cropPlan->id,
                    'crop_name' => $cropPlan->crop_name,
                    'planting_date' => $cropPlan->planting_date->format('Y-m-d'),
                    'expected_harvest_date' => $cropPlan->expected_harvest_date->format('Y-m-d'),
                    'edoh_formatted' => $cropPlan->expected_harvest_date->format('M d, Y'),
                    'days_to_harvest' => $cropType->days_to_harvest_value,
                    'area_hectares' => $cropPlan->area_hectares,
                    'predicted_production' => $cropPlan->predicted_production,
                    'predicted_production_formatted' => $cropPlan->formatted_production,
                ],
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to create crop plan', [
                'error' => $e->getMessage(),
                'farmer_id' => $farmer->id,
                'data' => $validated,
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to create crop plan. Please try again.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get prediction for crop plan (preview before saving)
     */
    public function previewCropPlan(Request $request)
    {
        $farmer = Auth::guard('farmer')->user();
        
        $validated = $request->validate([
            'crop_type_id' => 'required|exists:crop_types,id',
            'planting_date' => 'required|date',
            'area_hectares' => 'required|numeric|min:0.01',
            'farm_type' => 'nullable|string|in:IRRIGATED,RAINFED',
        ]);
        
        try {
            $cropType = CropType::findOrFail($validated['crop_type_id']);
            $plantingDate = Carbon::parse($validated['planting_date']);
            $areaHectares = floatval($validated['area_hectares']);
            $farmType = $validated['farm_type'] ?? 'IRRIGATED';
            
            // Calculate EDOH
            $expectedHarvestDate = $cropType->calculateHarvestDate($plantingDate);
            $daysToHarvest = $cropType->days_to_harvest_value;
            
            // Get prediction
            $predictedProduction = $this->getPredictedProduction(
                $cropType->name,
                $farmer->municipality ?? 'BUGUIAS',
                $farmType,
                $plantingDate,
                $areaHectares
            );
            
            return response()->json([
                'success' => true,
                'data' => [
                    'crop_name' => $cropType->name,
                    'planting_date' => $plantingDate->format('Y-m-d'),
                    'planting_date_formatted' => $plantingDate->format('M d, Y'),
                    'expected_harvest_date' => $expectedHarvestDate->format('Y-m-d'),
                    'edoh_formatted' => $expectedHarvestDate->format('M d, Y'),
                    'days_to_harvest' => $daysToHarvest,
                    'area_hectares' => $areaHectares,
                    'predicted_production' => round($predictedProduction, 2),
                    'predicted_production_formatted' => number_format($predictedProduction, 2) . ' MT',
                    'average_yield_per_hectare' => $cropType->average_yield_value,
                ],
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to calculate prediction.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update crop plan status
     */
    public function updateCropPlanStatus(Request $request, CropPlan $cropPlan)
    {
        $farmer = Auth::guard('farmer')->user();
        
        // Ensure the crop plan belongs to this farmer
        if ($cropPlan->farmer_id !== $farmer->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }
        
        $validated = $request->validate([
            'status' => 'required|in:planned,planted,growing,harvested,cancelled',
        ]);
        
        $cropPlan->update(['status' => $validated['status']]);
        
        return response()->json([
            'success' => true,
            'message' => 'Status updated successfully!',
            'data' => $cropPlan,
        ]);
    }

    /**
     * Delete a crop plan
     */
    public function deleteCropPlan(CropPlan $cropPlan)
    {
        $farmer = Auth::guard('farmer')->user();
        
        if ($cropPlan->farmer_id !== $farmer->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }
        
        $cropPlan->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Crop plan deleted successfully!',
        ]);
    }

    /**
     * Get all crop plans for calendar display
     */
    public function getCropPlans(Request $request)
    {
        $farmer = Auth::guard('farmer')->user();
        
        $cropPlans = CropPlan::where('farmer_id', $farmer->id)
            ->with('cropType')
            ->orderBy('planting_date')
            ->get();
        
        // Format for calendar display
        $events = [];
        foreach ($cropPlans as $plan) {
            // Add planting event
            $plantingKey = $plan->planting_date->format('Y-m-d');
            if (!isset($events[$plantingKey])) {
                $events[$plantingKey] = [];
            }
            $events[$plantingKey][] = $plan->toPlantingEvent();
            
            // Add harvest event
            $harvestKey = $plan->expected_harvest_date->format('Y-m-d');
            if (!isset($events[$harvestKey])) {
                $events[$harvestKey] = [];
            }
            $events[$harvestKey][] = $plan->toHarvestEvent();
        }
        
        return response()->json([
            'success' => true,
            'events' => $events,
            'plans' => $cropPlans,
        ]);
    }

    /**
     * Get crop types with harvest info for the form
     */
    public function getCropTypes()
    {
        $cropTypes = CropType::active()
            ->orderBy('name')
            ->get()
            ->map(function ($crop) {
                return [
                    'id' => $crop->id,
                    'name' => $crop->name,
                    'category' => $crop->category,
                    'days_to_harvest' => $crop->days_to_harvest_value,
                    'average_yield_per_hectare' => $crop->average_yield_value,
                ];
            });
        
        return response()->json([
            'success' => true,
            'crop_types' => $cropTypes,
        ]);
    }

    /**
     * Helper: Get predicted production using ML API or fallback
     */
    private function getPredictedProduction(
        string $cropName,
        string $municipality,
        string $farmType,
        Carbon $plantingDate,
        float $areaHectares
    ): float {
        try {
            // Try ML API prediction
            $predictionService = new PredictionService();
            $result = $predictionService->predictProduction([
                'municipality' => strtoupper($municipality),
                'farm_type' => strtoupper($farmType),
                'month' => strtoupper($plantingDate->format('M')),
                'crop' => strtoupper($cropName),
                'area_harvested' => $areaHectares,
                'year' => $plantingDate->year,
            ]);
            
            if (isset($result['success']) && $result['success'] && isset($result['prediction'])) {
                return floatval($result['prediction']);
            }
        } catch (\Exception $e) {
            Log::warning('ML prediction failed, using fallback', [
                'crop' => $cropName,
                'error' => $e->getMessage(),
            ]);
        }
        
        // Fallback to simple calculation based on average yield
        $averageYield = CropType::getAverageYield($cropName);
        return round($areaHectares * $averageYield, 2);
    }
}
