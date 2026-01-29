<?php

namespace App\Http\Controllers\Farmer;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\CropType;
use App\Models\CropProduction;
use App\Models\CropPlan;
use App\Models\Crop;
use App\Models\FarmerNotification;
use App\Services\PredictionService;
use App\Services\WeatherService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class FarmerDashboardController extends Controller
{
    protected $weatherService;

    public function __construct(WeatherService $weatherService)
    {
        $this->weatherService = $weatherService;
    }

    /**
     * Show the farmer dashboard
     */
    public function dashboard()
    {
        $farmer = Auth::guard('farmer')->user();
        $municipality = $farmer->municipality ?? 'Buguias';
        
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
        
        // Get weather data from Google Weather API
        $weather = $this->getWeatherData($municipality);
        
        // Get price watch data
        $prices = $this->getPriceWatchData();
        
        // Count events for this month
        $thisMonthEvents = 0;
        $currentMonth = now()->format('Y-m');
        foreach ($events as $date => $dateEvents) {
            if (str_starts_with($date, $currentMonth)) {
                $thisMonthEvents += count($dateEvents);
            }
        }
        
        // Get stats
        $stats = [
            'weather_temp' => $weather['temperature'] ?? 28,
            'events_count' => $thisMonthEvents,
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
     * Show the help and support page
     */
    public function help()
    {
        return view('farmers.help');
    }
    
    /**
     * Show the profile page
     */
    public function profile()
    {
        $farmer = Auth::guard('farmer')->user();
        
        // Get farmer stats
        $stats = [
            'total_crops' => CropPlan::where('farmer_id', $farmer->id)->count(),
            'harvested' => CropPlan::where('farmer_id', $farmer->id)->where('status', 'harvested')->count(),
        ];
        
        return view('farmers.profile', compact('farmer', 'stats'));
    }
    
    /**
     * Update farmer profile information
     */
    public function updateProfile(Request $request)
    {
        $farmer = Auth::guard('farmer')->user();
        
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'suffix' => 'nullable|string|max:50',
            'municipality' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'mobile_number' => 'nullable|string|max:20',
            'cooperative' => 'nullable|string|max:255',
        ]);
        
        $farmer->update($validated);
        
        return redirect()->route('farmers.profile')->with('success', 'Profile updated successfully!');
    }
    
    /**
     * Update farmer password
     */
    public function updatePassword(Request $request)
    {
        $farmer = Auth::guard('farmer')->user();
        
        $validated = $request->validate([
            'current_password' => 'required',
            'password' => 'required|string|min:8|confirmed',
        ]);
        
        // Check current password
        if (!password_verify($validated['current_password'], $farmer->password)) {
            return back()->withErrors(['current_password' => 'The current password is incorrect.']);
        }
        
        $farmer->update([
            'password' => bcrypt($validated['password']),
        ]);
        
        return redirect()->route('farmers.profile')->with('success', 'Password updated successfully!');
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
                $daysUntilHarvest = $plan->days_until_harvest;
                $progressPercentage = $plan->progress_percentage;
                $isHarvestReady = $daysUntilHarvest <= 7 && $plan->status !== 'harvested'; // Ready when 7 days or less until harvest
                $isOverdue = $daysUntilHarvest <= 0 && $plan->status !== 'harvested';
                
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
                    'daysUntilHarvest' => $daysUntilHarvest,
                    'progressPercentage' => $progressPercentage,
                    'isHarvestReady' => $isHarvestReady,
                    'isOverdue' => $isOverdue,
                    'maturityStatus' => $this->getMaturityStatus($daysUntilHarvest, $plan->status),
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
     * Get maturity status based on days until harvest
     */
    private function getMaturityStatus(int $daysUntilHarvest, string $status): string
    {
        if ($status === 'harvested') {
            return 'harvested';
        }
        
        if ($daysUntilHarvest <= 0) {
            return 'overdue'; // Past harvest date
        } elseif ($daysUntilHarvest <= 3) {
            return 'ready'; // Ready to harvest (0-3 days)
        } elseif ($daysUntilHarvest <= 7) {
            return 'almost_ready'; // Almost ready (4-7 days)
        } elseif ($daysUntilHarvest <= 14) {
            return 'approaching'; // Approaching harvest (8-14 days)
        } else {
            return 'growing'; // Still growing (more than 14 days)
        }
    }
    
    /**
     * Get farmer's calendar events from their crop plans only
     */
    private function getFarmerEvents($farmer, $allEvents = false)
    {
        $events = [];
        
        // Get events from farmer's crop plans (database only - no mock data)
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
     * Get weather data from Google Weather API
     */
    private function getWeatherData($municipality)
    {
        try {
            // Get current conditions from Google Weather API
            $currentConditions = $this->weatherService->getCurrentConditions($municipality);
            
            // Get forecast data
            $forecastData = $this->weatherService->getForecast($municipality, 4);
            
            // Get hourly forecast
            $hourlyForecast = $this->weatherService->getHourlyForecast($municipality, 6);
            
            return [
                'temperature' => $currentConditions['temperature'] ?? 22,
                'feels_like' => $currentConditions['feels_like'] ?? 24,
                'condition' => $currentConditions['condition'] ?? 'Partly Cloudy',
                'icon' => $currentConditions['icon'] ?? '⛅',
                'humidity' => $currentConditions['humidity'] ?? 75,
                'wind_speed' => $currentConditions['wind_speed'] ?? 12,
                'uv_index' => $currentConditions['uv_index'] ?? 5,
                'high' => isset($forecastData['forecast'][0]) ? explode('-', str_replace('°C', '', $forecastData['forecast'][0]['temp']))[1] ?? 28 : 28,
                'low' => isset($forecastData['forecast'][0]) ? explode('-', str_replace('°C', '', $forecastData['forecast'][0]['temp']))[0] ?? 18 : 18,
                'location' => $municipality . ', Benguet',
                'forecast' => $forecastData['forecast'] ?? [],
                'hourly' => $hourlyForecast ?? [],
            ];
        } catch (\Exception $e) {
            Log::warning("Weather API error for {$municipality}: " . $e->getMessage());
            
            // Return fallback data
            return [
                'temperature' => 22,
                'feels_like' => 24,
                'condition' => 'Partly Cloudy',
                'icon' => '⛅',
                'humidity' => 75,
                'wind_speed' => 12,
                'uv_index' => 5,
                'high' => 28,
                'low' => 18,
                'location' => $municipality . ', Benguet',
                'forecast' => [],
                'hourly' => [],
            ];
        }
    }
    
    /**
     * Get price watch data for dashboard (uses same logic as getAllPrices but returns top 3)
     */
    private function getPriceWatchData()
    {
        $allPrices = $this->getAllPrices();
        return array_slice($allPrices, 0, 3);
    }
    
    /**
     * Get all crop prices from database crops
     */
    private function getAllPrices()
    {
        // Get crops from database
        $cropTypes = CropType::active()->orderBy('name')->get();
        
        // Emoji mapping based on crop name
        $emojiMap = [
            'cabbage' => '🥬',
            'chinese cabbage' => '🥬',
            'lettuce' => '🥬',
            'celery' => '🥬',
            'carrots' => '🥕',
            'carrot' => '🥕',
            'potatoes' => '🥔',
            'potato' => '🥔',
            'radish' => '🥕',
            'broccoli' => '🥦',
            'cauliflower' => '🥦',
            'snap beans' => '🫛',
            'string beans' => '🫛',
            'baguio beans' => '🫛',
            'beans' => '🫛',
            'sweet peas' => '🫛',
            'peas' => '🫛',
            'garden peas' => '🫛',
            'tomatoes' => '🍅',
            'tomato' => '🍅',
            'bell pepper' => '🫑',
            'pepper' => '🫑',
            'sayote' => '🥒',
            'onion' => '🧅',
            'garlic' => '🧄',
            'strawberry' => '🍓',
        ];
        
        // Image mapping based on crop name (local images)
        $imageMap = [
            'cabbage' => 'images/crops/cabbage.jpg',
            'chinese cabbage' => 'images/crops/Chinese_cabbage.jpg',
            'lettuce' => 'images/crops/Lettuce-Baguio.png',
            'carrots' => 'images/crops/carrots2023-12-2716-44-36_2024-01-03_22-33-52.jpg',
            'carrot' => 'images/crops/carrots2023-12-2716-44-36_2024-01-03_22-33-52.jpg',
            'potatoes' => 'images/crops/White_potato.jpg',
            'potato' => 'images/crops/White_potato.jpg',
            'whitepotato' => 'images/crops/White_potato.jpg',
            'white potato' => 'images/crops/White_potato.jpg',
            'bell pepper' => 'images/crops/Bell-peppers.webp',
            'sweet pepper' => 'images/crops/Bell-peppers.webp',
            'pepper' => 'images/crops/Bell-peppers.webp',
            'cauliflower' => 'images/crops/Cauli-flower.jpg',
            'broccoli' => 'images/crops/brocolli.jpg',
            'beans' => 'images/crops/snap_beans.jpg',
            'snap beans' => 'images/crops/snap_beans.jpg',
            'string beans' => 'images/crops/snap_beans.jpg',
            'baguio beans' => 'images/crops/snap_beans.jpg',
            'garden peas' => 'images/crops/garden_peas.jpg',
            'peas' => 'images/crops/garden_peas.jpg',
        ];
        
        // Specification mapping based on crop name
        $specificationMap = [
            'cabbage' => '2 heads/kg',
            'chinese cabbage' => '1 pc/kg',
            'lettuce' => '4 pcs/kg',
            'carrots' => '6 pcs/kg',
            'carrot' => '6 pcs/kg',
            'potatoes' => '4 pcs/kg',
            'potato' => '4 pcs/kg',
            'cauliflower' => '2 heads/kg',
            'broccoli' => '2 heads/kg',
            'beans' => '50 pcs/kg',
            'snap beans' => '45 pcs/kg',
            'string beans' => '40 pcs/kg',
            'baguio beans' => '60 pcs/kg',
            'sweet peas' => '50 pcs/kg',
            'peas' => '50 pcs/kg',
            'bell pepper' => '5 pcs/kg',
            'pepper' => '5 pcs/kg',
            'tomatoes' => '8 pcs/kg',
            'tomato' => '8 pcs/kg',
            'sayote' => '3 pcs/kg',
            'radish' => '5 pcs/kg',
            'onion' => '8 pcs/kg',
            'garlic' => '15 pcs/kg',
            'strawberry' => '1 pack',
            'celery' => '3 stalks/kg',
        ];
        
        // Base prices for generating mock data (can be replaced with real API later)
        $basePrices = [
            'cabbage' => 77.00,
            'chinese cabbage' => 149.00,
            'lettuce' => 160.00,
            'celery' => 100.00,
            'carrots' => 80.00,
            'carrot' => 80.00,
            'potatoes' => 145.00,
            'potato' => 145.00,
            'radish' => 229.00,
            'broccoli' => 380.00,
            'cauliflower' => 237.00,
            'beans' => 120.00,
            'snap beans' => 90.00,
            'string beans' => 60.00,
            'baguio beans' => 119.00,
            'sweet peas' => 680.00,
            'peas' => 200.00,
            'garden peas' => 200.00,
            'tomatoes' => 45.00,
            'tomato' => 45.00,
            'bell pepper' => 120.00,
            'pepper' => 120.00,
            'sayote' => 35.00,
            'onion' => 80.00,
            'garlic' => 180.00,
            'strawberry' => 350.00,
        ];
        
        $prices = [];
        
        foreach ($cropTypes as $crop) {
            $name = strtolower($crop->name);
            
            // Find matching keys for this crop
            $emoji = '🌱';
            $image = 'images/crops/unnamed.jpg';
            $specification = '1 kg';
            $basePrice = 100.00;
            
            foreach ($emojiMap as $key => $value) {
                if (str_contains($name, $key)) {
                    $emoji = $value;
                    break;
                }
            }
            
            foreach ($imageMap as $key => $value) {
                if (str_contains($name, $key)) {
                    $image = $value;
                    break;
                }
            }
            
            foreach ($specificationMap as $key => $value) {
                if (str_contains($name, $key)) {
                    $specification = $value;
                    break;
                }
            }
            
            foreach ($basePrices as $key => $value) {
                if (str_contains($name, $key)) {
                    $basePrice = $value;
                    break;
                }
            }
            
            // Generate a small random change for price variation (simulating market changes)
            $change = round((rand(-20, 20) / 100) * $basePrice, 2);
            
            $prices[] = [
                'name' => $crop->name,
                'emoji' => $emoji,
                'image' => asset($image),
                'specification' => $specification,
                'price' => round($basePrice + $change, 2),
                'change' => $change,
                'unit' => 'kg',
                'category' => $crop->category ?? 'Vegetables',
                'description' => $crop->description ?? "{$crop->name} from Benguet highlands.",
            ];
        }
        
        return $prices;
    }
    
    /**
     * Get price trends for charts (using database crops)
     */
    private function getPriceTrends()
    {
        // Get up to 5 crops from database for the trend chart
        $cropTypes = CropType::active()->orderBy('name')->take(5)->get();
        
        // Generate months labels
        $months = [];
        for ($i = 5; $i >= 0; $i--) {
            $months[] = now()->subMonths($i)->format('M');
        }
        
        // Color palette for chart
        $colors = ['#22c55e', '#f97316', '#eab308', '#3b82f6', '#ec4899'];
        
        // Base prices for generating mock trend data
        $basePrices = [
            'cabbage' => 80,
            'chinese cabbage' => 150,
            'lettuce' => 160,
            'celery' => 100,
            'carrots' => 80,
            'potatoes' => 145,
            'radish' => 229,
            'broccoli' => 380,
            'cauliflower' => 237,
            'beans' => 120,
            'snap beans' => 90,
            'string beans' => 60,
            'baguio beans' => 119,
            'sweet peas' => 680,
            'peas' => 200,
            'tomatoes' => 45,
            'bell pepper' => 120,
            'sayote' => 35,
            'onion' => 80,
            'garlic' => 180,
            'strawberry' => 350,
        ];
        
        $datasets = [];
        $colorIndex = 0;
        
        foreach ($cropTypes as $crop) {
            $name = strtolower($crop->name);
            $basePrice = 100;
            
            // Find base price for this crop
            foreach ($basePrices as $key => $value) {
                if (str_contains($name, $key)) {
                    $basePrice = $value;
                    break;
                }
            }
            
            // Generate 6 months of mock trend data with slight variations
            $data = [];
            for ($i = 0; $i < 6; $i++) {
                $variation = rand(-15, 15) / 100; // ±15% variation
                $data[] = round($basePrice * (1 + $variation));
            }
            
            $datasets[] = [
                'label' => $crop->name,
                'data' => $data,
                'color' => $colors[$colorIndex % count($colors)],
            ];
            
            $colorIndex++;
        }
        
        return [
            'labels' => $months,
            'datasets' => $datasets,
        ];
    }
    
    /**
     * Get active crops count for farmer (crops currently being grown)
     */
    private function getActiveCropsCount($farmer)
    {
        return CropPlan::where('farmer_id', $farmer->id)
            ->where('status', 'growing')
            ->count();
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
     * API: Get weather data for farmer's municipality
     */
    public function getWeatherApi(Request $request)
    {
        $farmer = Auth::guard('farmer')->user();
        $municipality = $request->input('municipality', $farmer->municipality ?? 'Buguias');
        
        // Validate municipality
        $validMunicipalities = ['Atok', 'Bakun', 'Bokod', 'Buguias', 'Itogon', 'Kabayan', 'Kapangan', 'Kibungan', 'La Trinidad', 'Mankayan', 'Sablan', 'Tuba', 'Tublay'];
        
        if (!in_array($municipality, $validMunicipalities)) {
            return response()->json(['error' => 'Invalid municipality'], 400);
        }
        
        try {
            $weather = $this->getWeatherData($municipality);
            
            return response()->json([
                'success' => true,
                'municipality' => $municipality,
                'weather' => $weather,
                'updated_at' => now()->format('Y-m-d H:i:s'),
            ]);
        } catch (\Exception $e) {
            Log::error("Weather API error: " . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch weather data'], 500);
        }
    }

    /**
     * Store a new crop plan
     */
    public function storeCropPlan(Request $request)
    {
        Log::info('storeCropPlan: Starting...', ['input' => $request->all()]);
        
        $farmer = Auth::guard('farmer')->user();
        
        if (!$farmer) {
            Log::error('storeCropPlan: No authenticated farmer');
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Please log in again.',
            ], 401);
        }
        
        Log::info('storeCropPlan: Farmer authenticated', ['farmer_id' => $farmer->id]);
        
        try {
            $validated = $request->validate([
                'crop_type_id' => 'required|exists:crop_types,id',
                'planting_date' => 'required|date|after_or_equal:today',
                'area_hectares' => 'required|numeric|min:0.01|max:1000',
                'farm_type' => 'nullable|string|in:IRRIGATED,RAINFED',
                'notes' => 'nullable|string|max:500',
            ]);
            
            Log::info('storeCropPlan: Validation passed', $validated);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('storeCropPlan: Validation failed', ['errors' => $e->errors()]);
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }
        
        try {
            $cropType = CropType::findOrFail($validated['crop_type_id']);
            Log::info('storeCropPlan: CropType found', ['crop_type' => $cropType->name]);
            
            $plantingDate = Carbon::parse($validated['planting_date']);
            $areaHectares = floatval($validated['area_hectares']);
            $farmType = $validated['farm_type'] ?? 'IRRIGATED';
            
            // Calculate Expected Date of Harvest (EDOH)
            $expectedHarvestDate = $cropType->calculateHarvestDate($plantingDate);
            Log::info('storeCropPlan: Harvest date calculated', ['harvest_date' => $expectedHarvestDate->format('Y-m-d')]);
            
            // Get predicted production using ML API or fallback to simple calculation
            $predictedProduction = $this->getPredictedProduction(
                $cropType->name,
                $farmer->municipality ?? 'BUGUIAS',
                $farmType,
                $plantingDate,
                $areaHectares
            );
            Log::info('storeCropPlan: Production predicted', ['predicted' => $predictedProduction]);
            
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
            Log::info('storeCropPlan: CropPlan created', ['crop_plan_id' => $cropPlan->id]);
            
            // Create notification for the farmer with growth period info
            try {
                FarmerNotification::createCropPlanNotification($farmer, $cropPlan, $cropType->days_to_harvest_value);
                Log::info('storeCropPlan: Notification created');
            } catch (\Exception $notifEx) {
                Log::warning('storeCropPlan: Notification creation failed, but crop plan saved', [
                    'error' => $notifEx->getMessage(),
                    'trace' => $notifEx->getTraceAsString()
                ]);
                // Continue even if notification fails
            }
            
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
                'trace' => $e->getTraceAsString(),
                'farmer_id' => $farmer->id,
                'data' => $validated ?? [],
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to create crop plan. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'Server error',
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

    /**
     * Get farmer's notifications (crop plan related only)
     */
    public function getNotifications(Request $request)
    {
        $farmer = Auth::guard('farmer')->user();
        $limit = $request->get('limit', 10);
        
        $notifications = FarmerNotification::where('farmer_id', $farmer->id)
            ->cropPlanRelated()
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'type' => $notification->type,
                    'title' => $notification->title,
                    'message' => $notification->message,
                    'icon_svg' => $notification->icon_svg,
                    'icon_bg_class' => $notification->icon_bg_class,
                    'icon_text_class' => $notification->icon_text_class,
                    'link' => $notification->link,
                    'is_read' => $notification->is_read,
                    'time_ago' => $notification->time_ago,
                    'created_at' => $notification->created_at->format('Y-m-d H:i:s'),
                ];
            });
        
        $unreadCount = FarmerNotification::where('farmer_id', $farmer->id)
            ->cropPlanRelated()
            ->unread()
            ->count();
        
        return response()->json([
            'success' => true,
            'notifications' => $notifications,
            'unread_count' => $unreadCount,
        ]);
    }

    /**
     * Mark a notification as read
     */
    public function markNotificationRead(FarmerNotification $notification)
    {
        $farmer = Auth::guard('farmer')->user();
        
        if ($notification->farmer_id !== $farmer->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }
        
        $notification->markAsRead();
        
        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read',
        ]);
    }

    /**
     * Mark all notifications as read
     */
    public function markAllNotificationsRead()
    {
        $farmer = Auth::guard('farmer')->user();
        
        FarmerNotification::where('farmer_id', $farmer->id)
            ->unread()
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
        
        return response()->json([
            'success' => true,
            'message' => 'All notifications marked as read',
        ]);
    }
}
