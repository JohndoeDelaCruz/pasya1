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
            'events_count' => $thisMonthEvents,
            'active_crops' => $this->getActiveCropsCount($farmer),
            'announcements_count' => $announcements->count(),
        ];

        $farmerMunicipality = $farmer->municipality
            ? ucwords(strtolower($farmer->municipality))
            : null;

        return view('farmers.dashboard', compact('announcements', 'events', 'prices', 'stats', 'farmerMunicipality'));
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
     * Show the weather monitoring page for farmers
     */
    public function weather()
    {
        $farmer = Auth::guard('farmer')->user();
        $farmerMunicipality = $farmer->municipality
            ? ucwords(strtolower($farmer->municipality))
            : null;

        $municipalities = \App\Models\Municipality::active()
            ->orderBy('name')
            ->pluck('name')
            ->map(fn($name) => ucwords(strtolower($name)))
            ->values();

        return view('farmers.weather', compact('municipalities', 'farmerMunicipality'));
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
            'email' => 'nullable|email|max:255|unique:farmers,email,' . $farmer->id,
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
            'password' => \Illuminate\Support\Facades\Hash::make($validated['password']),
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

        /** @var CropPlan $plan */
        foreach ($cropPlans as $plan) {
            // Add planting event (includes basal fertilizer note)
            $plantingKey = $plan->planting_date->format('Y-m-d');
            if (!isset($events[$plantingKey])) {
                $events[$plantingKey] = [];
            }
            $events[$plantingKey][] = $plan->toPlantingEvent();

            // Add harvest event (EDOH)
            $harvestKey = $plan->expected_harvest_date->format('Y-m-d');
            if (!isset($events[$harvestKey])) {
                $events[$harvestKey] = [];
            }
            $events[$harvestKey][] = array_merge($plan->toHarvestEvent(), [
                'is_edoh' => true,
            ]);

            // Add fertilizer events (side-dress applications based on growth stages)
            $fertilizerEvents = $plan->toFertilizerEvents();
            foreach ($fertilizerEvents as $dateKey => $dayEvents) {
                if (!isset($events[$dateKey])) {
                    $events[$dateKey] = [];
                }
                foreach ($dayEvents as $event) {
                    $events[$dateKey][] = $event;
                }
            }
        }

        return $events;
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
        // Prefer active crop types, then any crop types, then built-in defaults.
        $cropTypes = $this->getPriceWatchCropTypes();

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
        // Use the same resilient source as cards so chart never renders empty.
        $cropTypes = $this->getPriceWatchCropTypes(5);

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
     * Resolve crop types for Price Watch with graceful fallbacks.
     */
    private function getPriceWatchCropTypes(?int $limit = null)
    {
        $activeQuery = CropType::active()->orderBy('name');
        $activeCropTypes = $limit ? $activeQuery->take($limit)->get() : $activeQuery->get();

        if ($activeCropTypes->isNotEmpty()) {
            return $activeCropTypes;
        }

        $allQuery = CropType::query()->orderBy('name');
        $allCropTypes = $limit ? $allQuery->take($limit)->get() : $allQuery->get();

        if ($allCropTypes->isNotEmpty()) {
            return $allCropTypes;
        }

        $defaults = collect([
            ['name' => 'Cabbage', 'category' => 'Leafy Vegetables', 'description' => 'Cool weather crop.'],
            ['name' => 'Chinese Cabbage', 'category' => 'Leafy Vegetables', 'description' => 'Also known as Wombok.'],
            ['name' => 'Lettuce', 'category' => 'Leafy Vegetables', 'description' => 'Popular salad green.'],
            ['name' => 'Carrots', 'category' => 'Root Vegetables', 'description' => 'Popular root vegetable.'],
            ['name' => 'Potatoes', 'category' => 'Root Vegetables', 'description' => 'Major highland tuber crop.'],
            ['name' => 'Broccoli', 'category' => 'Cruciferous', 'description' => 'High-value cool weather crop.'],
            ['name' => 'Cauliflower', 'category' => 'Cruciferous', 'description' => 'Cool-climate vegetable.'],
            ['name' => 'Snap Beans', 'category' => 'Legumes', 'description' => 'Also known as Baguio beans.'],
            ['name' => 'String Beans', 'category' => 'Legumes', 'description' => 'Long pod bean variety.'],
            ['name' => 'Tomatoes', 'category' => 'Fruit Vegetables', 'description' => 'Versatile fruit vegetable.'],
            ['name' => 'Bell Pepper', 'category' => 'Fruit Vegetables', 'description' => 'Sweet pepper variety.'],
            ['name' => 'Sayote', 'category' => 'Fruit Vegetables', 'description' => 'Also known as chayote.'],
        ])->map(static fn (array $crop) => (object) $crop);

        return $limit ? $defaults->take($limit)->values() : $defaults;
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
     * Store a new crop plan
     */
    public function storeCropPlan(Request $request)
    {
        Log::info('storeCropPlan: Starting...', ['input' => $request->all()]);

        /** @var \App\Models\Farmer $farmer */
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
                'planting_date' => 'required|date',
                'area_hectares' => 'required|numeric|min:0.01|max:1000',
                'farm_type' => 'nullable|string|in:IRRIGATED,RAINFED',
                'planting_material_type' => 'nullable|string|in:SEED,SEEDLING',
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
            $plantingMaterialType = strtoupper((string) ($validated['planting_material_type'] ?? $cropType->default_planting_material_type));

            if (!$cropType->supportsPlantingMaterialType($plantingMaterialType)) {
                return response()->json([
                    'success' => false,
                    'message' => 'The selected planting material is not available for this crop.',
                    'errors' => [
                        'planting_material_type' => ['The selected planting material is not available for this crop.'],
                    ],
                ], 422);
            }

            // Calculate Expected Date of Harvest (EDOH)
            $daysToHarvest = $cropType->getDaysToHarvestForMaterial($plantingMaterialType);
            $expectedHarvestDate = $cropType->calculateHarvestDate($plantingDate, $plantingMaterialType);
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
                'planting_material_type' => $plantingMaterialType,
                'status' => 'planned',
                'notes' => $validated['notes'] ?? null,
            ]);
            Log::info('storeCropPlan: CropPlan created', ['crop_plan_id' => $cropPlan->id]);

            // Create notification for the farmer with growth period info
            try {
                FarmerNotification::createCropPlanNotification($farmer, $cropPlan, $daysToHarvest);
                Log::info('storeCropPlan: Notification created');
            } catch (\Exception $notifEx) {
                Log::warning('storeCropPlan: Notification creation failed, but crop plan saved', [
                    'error' => $notifEx->getMessage(),
                    'trace' => $notifEx->getTraceAsString()
                ]);
                // Continue even if notification fails
            }

            // Generate fertilizer events for the response
            $fertilizerEvents = $cropPlan->toFertilizerEvents();

            return response()->json([
                'success' => true,
                'message' => 'Crop plan created successfully!',
                'data' => [
                    'id' => $cropPlan->id,
                    'crop_name' => $cropPlan->crop_name,
                    'planting_date' => $cropPlan->planting_date->format('Y-m-d'),
                    'expected_harvest_date' => $cropPlan->expected_harvest_date->format('Y-m-d'),
                    'edoh_formatted' => $cropPlan->expected_harvest_date->format('M d, Y'),
                    'days_to_harvest' => $daysToHarvest,
                    'area_hectares' => $cropPlan->area_hectares,
                    'predicted_production' => $cropPlan->predicted_production,
                    'predicted_production_formatted' => $cropPlan->formatted_production,
                    'planting_material_type' => $cropPlan->planting_material_type,
                    'planting_material_label' => $cropPlan->planting_material_label,
                    'planting_event' => $cropPlan->toPlantingEvent(),
                    'harvest_event' => array_merge($cropPlan->toHarvestEvent(), [
                        'is_edoh' => true,
                    ]),
                    'fertilizer_events' => $fertilizerEvents,
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
            'planting_material_type' => 'nullable|string|in:SEED,SEEDLING',
        ]);

        try {
            $cropType = CropType::findOrFail($validated['crop_type_id']);
            $plantingDate = Carbon::parse($validated['planting_date']);
            $areaHectares = floatval($validated['area_hectares']);
            $farmType = $validated['farm_type'] ?? 'IRRIGATED';
            $plantingMaterialType = strtoupper((string) ($validated['planting_material_type'] ?? $cropType->default_planting_material_type));

            if (!$cropType->supportsPlantingMaterialType($plantingMaterialType)) {
                return response()->json([
                    'success' => false,
                    'message' => 'The selected planting material is not available for this crop.',
                    'errors' => [
                        'planting_material_type' => ['The selected planting material is not available for this crop.'],
                    ],
                ], 422);
            }

            // Calculate EDOH
            $daysToHarvest = $cropType->getDaysToHarvestForMaterial($plantingMaterialType);
            $expectedHarvestDate = $cropType->calculateHarvestDate($plantingDate, $plantingMaterialType);

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
                    'planting_material_type' => $plantingMaterialType,
                    'area_hectares' => $areaHectares,
                    'predicted_production' => round($predictedProduction, 2),
                    'predicted_production_formatted' => number_format($predictedProduction, 2) . ' MT',
                    'average_yield_per_hectare' => $cropType->average_yield_value,
                    'seedling_days' => $cropType->seedling_days_value,
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
    public function deleteCropPlan($id)
    {
        $farmer = Auth::guard('farmer')->user();

        $cropPlan = CropPlan::find($id);

        if (!$cropPlan) {
            // Already deleted — treat as success
            return response()->json([
                'success' => true,
                'message' => 'Crop plan already deleted.',
            ]);
        }

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
        /** @var CropPlan $plan */
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
     * Helper: Get predicted production using ML API V2 or fallback
     * 
     * V2 API returns:
     * - prediction.productivity_mt_ha: Predicted yield per hectare
     * - prediction.production_mt: Total production (productivity × area)
     */
    private function getPredictedProduction(
        string $cropName,
        string $municipality,
        string $farmType,
        Carbon $plantingDate,
        float $areaHectares
    ): float {
        try {
            // Try ML API V2 prediction
            $predictionService = new PredictionService();
            $result = $predictionService->predictProduction([
                'municipality' => strtoupper($municipality),
                'farm_type' => strtoupper($farmType),
                'month' => strtoupper($plantingDate->format('M')),
                'crop' => strtoupper($cropName),
                'area_harvested' => $areaHectares,
                'year' => $plantingDate->year,
            ]);

            // V2 API returns prediction.production_mt (total production in MT)
            if (isset($result['success']) && $result['success'] && isset($result['prediction']['production_mt'])) {
                return floatval($result['prediction']['production_mt']);
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
