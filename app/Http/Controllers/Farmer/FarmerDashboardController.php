<?php

namespace App\Http\Controllers\Farmer;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\CropType;
use App\Models\CropProduction;
use App\Models\CropPlan;
use App\Models\CropPlanDamageReport;
use App\Models\Crop;
use App\Models\CropPrice;
use App\Models\FarmerNotification;
use App\Services\MLApiService;
use App\Services\PredictionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class FarmerDashboardController extends Controller
{
    public function __construct(
        protected PredictionService $predictionService,
        protected MLApiService $mlApiService
    )
    {
    }

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
            ->with(['cropType', 'latestDamageReport'])
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
        if (!Hash::check($validated['current_password'], $farmer->password)) {
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

        $prices = $this->getAllPrices();
        $priceFilters = $this->getPriceFilters($prices);
        $trends = $this->getPriceTrends();

        $lastUpdated = CropPrice::max('updated_at');

        return view('farmers.price-watch', compact('prices', 'priceFilters', 'trends', 'lastUpdated'));
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

        // Get farmer's crop plans (harvest history)
        $cropPlans = CropPlan::where('farmer_id', $farmer->id)
            ->with('cropType')
            ->orderBy('planting_date', 'desc')
            ->get()
            ->map(function ($plan) {
                $daysUntilHarvest = $plan->days_until_harvest;
                $progressPercentage = $plan->progress_percentage;
                $isHarvestReady = $daysUntilHarvest <= 7 && $plan->status !== 'harvested' && !$plan->has_damage_report;
                $isOverdue = $daysUntilHarvest <= 0 && $plan->status !== 'harvested';
                $displayStatus = $plan->display_status;

                return [
                    'id' => $plan->id,
                    'cropType' => $plan->crop_name,
                    'datePlanted' => $plan->planting_date->format('M d, Y'),
                    'dateHarvested' => $plan->status === 'harvested'
                        ? ($plan->actual_harvest_date
                            ? $plan->actual_harvest_date->format('M d, Y')
                            : $plan->expected_harvest_date->format('M d, Y'))
                        : '--',
                    'expectedHarvest' => $plan->expected_harvest_date->format('M d, Y'),
                    'status' => match ($displayStatus) {
                        'harvested' => 'Completed',
                        'damaged' => 'Damaged',
                        default => 'Growing',
                    },
                    'area' => $plan->area_hectares,
                    'predictedProduction' => $plan->adjusted_predicted_production,
                    'predictedProductionFormatted' => $plan->formatted_adjusted_production,
                    'originalPredictedProduction' => $plan->predicted_production,
                    'plan_status' => $plan->status,
                    'display_status' => $displayStatus,
                    'daysUntilHarvest' => $daysUntilHarvest,
                    'progressPercentage' => $progressPercentage,
                    'isHarvestReady' => $isHarvestReady,
                    'isOverdue' => $isOverdue,
                    'maturityStatus' => $this->getMaturityStatus($daysUntilHarvest, $plan->status),
                    'hasDamageReport' => $plan->has_damage_report,
                    'damagedAreaHectares' => $plan->damaged_area_hectares,
                    'adjustedAreaHectares' => $plan->adjusted_area_hectares,
                    'productionLossMt' => $plan->production_loss_mt,
                    'damageCauseLabel' => $plan->damage_cause_label,
                    'damageNotes' => $plan->damage_notes,
                    'damageOccurredOn' => optional($plan->damage_occurred_on)->format('M d, Y'),
                    'damageReportedAt' => optional($plan->damage_reported_at)->format('M d, Y h:i A'),
                ];
            });

        // Get production summary
        $summary = $this->getProductionSummary($farmer);

        return view('farmers.harvest-history', compact('crops', 'cropPlans', 'summary'));
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
            ->with(['cropType', 'latestDamageReport'])
            ->get();

        /** @var CropPlan $plan */
        foreach ($cropPlans as $plan) {
            $this->appendCropPlanEvents($events, $plan);
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
        $cropTypes = $this->getPriceWatchCropTypes();

        // Load all admin-set prices keyed by crop_type_id
        $dbPrices = CropPrice::all()->keyBy('crop_type_id');

        $emojiMap = [
            'chinese cabbage' => '🥬', 'cabbage' => '🥬', 'lettuce' => '🥬', 'celery' => '🥬',
            'carrots' => '🥕', 'carrot' => '🥕', 'radish' => '🥕',
            'potatoes' => '🥔', 'potato' => '🥔',
            'broccoli' => '🥦', 'cauliflower' => '🥦',
            'snap beans' => '🫛', 'string beans' => '🫛', 'baguio beans' => '🫛',
            'beans' => '🫛', 'sweet peas' => '🫛', 'garden peas' => '🫛', 'peas' => '🫛',
            'tomatoes' => '🍅', 'tomato' => '🍅',
            'bell pepper' => '🫑', 'pepper' => '🫑',
            'sayote' => '🥒', 'onion' => '🧅', 'garlic' => '🧄', 'strawberry' => '🍓',
        ];

        $imageMap = [
            'chinese cabbage' => 'images/crops/Chinese_cabbage.jpg',
            'cabbage' => 'images/crops/cabbage.jpg',
            'lettuce' => 'images/crops/Lettuce-Baguio.png',
            'carrots' => 'images/crops/carrots2023-12-2716-44-36_2024-01-03_22-33-52.jpg',
            'carrot' => 'images/crops/carrots2023-12-2716-44-36_2024-01-03_22-33-52.jpg',
            'white potato' => 'images/crops/White_potato.jpg',
            'potatoes' => 'images/crops/White_potato.jpg', 'potato' => 'images/crops/White_potato.jpg',
            'bell pepper' => 'images/crops/Bell-peppers.webp',
            'sweet pepper' => 'images/crops/Bell-peppers.webp', 'pepper' => 'images/crops/Bell-peppers.webp',
            'cauliflower' => 'images/crops/Cauli-flower.jpg',
            'broccoli' => 'images/crops/brocolli.jpg',
            'snap beans' => 'images/crops/snap_beans.jpg', 'string beans' => 'images/crops/snap_beans.jpg',
            'baguio beans' => 'images/crops/snap_beans.jpg', 'beans' => 'images/crops/snap_beans.jpg',
            'garden peas' => 'images/crops/garden_peas.jpg', 'peas' => 'images/crops/garden_peas.jpg',
        ];

        $specificationMap = [
            'cabbage' => '2 heads/kg', 'chinese cabbage' => '1 pc/kg', 'lettuce' => '4 pcs/kg',
            'carrots' => '6 pcs/kg', 'carrot' => '6 pcs/kg',
            'potatoes' => '4 pcs/kg', 'potato' => '4 pcs/kg',
            'cauliflower' => '2 heads/kg', 'broccoli' => '2 heads/kg',
            'snap beans' => '45 pcs/kg', 'string beans' => '40 pcs/kg',
            'baguio beans' => '60 pcs/kg', 'beans' => '50 pcs/kg',
            'sweet peas' => '50 pcs/kg', 'peas' => '50 pcs/kg',
            'bell pepper' => '5 pcs/kg', 'pepper' => '5 pcs/kg',
            'tomatoes' => '8 pcs/kg', 'tomato' => '8 pcs/kg',
            'sayote' => '3 pcs/kg', 'radish' => '5 pcs/kg',
            'onion' => '8 pcs/kg', 'garlic' => '15 pcs/kg',
            'strawberry' => '1 pack', 'celery' => '3 stalks/kg',
        ];

        $prices = [];

        foreach ($cropTypes as $crop) {
            $cropId = $crop->id ?? null;
            $dbPrice = $cropId ? ($dbPrices[$cropId] ?? null) : null;

            // Skip crops with no price set or price = 0
            if (!$dbPrice || (float) $dbPrice->price_per_kg <= 0) {
                continue;
            }

            $name = strtolower($crop->name);
            $emoji = '🌱';
            $image = 'images/crops/unnamed.jpg';
            $specification = '1 kg';

            foreach ($emojiMap as $key => $value) {
                if (str_contains($name, $key)) { $emoji = $value; break; }
            }
            foreach ($imageMap as $key => $value) {
                if (str_contains($name, $key)) { $image = $value; break; }
            }
            foreach ($specificationMap as $key => $value) {
                if (str_contains($name, $key)) { $specification = $value; break; }
            }

            $currentPrice = (float) $dbPrice->price_per_kg;
            $previousPrice = $dbPrice->previous_price !== null ? (float) $dbPrice->previous_price : null;
            $change = $previousPrice !== null ? round($currentPrice - $previousPrice, 2) : 0;

            $weeklyAvg  = $dbPrice->weekly_average  !== null ? (float) $dbPrice->weekly_average  : null;
            $monthlyAvg = $dbPrice->monthly_average !== null ? (float) $dbPrice->monthly_average : null;
            $lastYear   = $dbPrice->last_year_price !== null ? (float) $dbPrice->last_year_price  : null;

            $prices[] = [
                'name' => $crop->name,
                'emoji' => $emoji,
                'image' => asset($image),
                'specification' => $specification,
                'price' => $currentPrice,
                'change' => $change,
                'unit' => 'kg',
                'category' => $crop->category ?? 'Vegetables',
                'description' => $crop->description ?? "{$crop->name} from Benguet highlands.",
                'updated_at' => $dbPrice->updated_at,
                'weekly_average'  => $weeklyAvg,
                'monthly_average' => $monthlyAvg,
                'last_year_price' => $lastYear,
            ];
        }

        return $prices;
    }

    /**
     * Build Price Watch filters from the actual crop list shown on the page.
     */
    private function getPriceFilters(array $prices): array
    {
        $filters = [[
            'value' => 'all',
            'label' => 'All',
            'count' => count($prices),
        ]];

        collect($prices)
            ->groupBy(fn (array $price) => (string) ($price['category'] ?? 'Other'))
            ->sortKeys()
            ->each(function ($categoryPrices, string $category) use (&$filters) {
                $cropNames = $categoryPrices
                    ->pluck('name')
                    ->filter()
                    ->values();

                $filters[] = [
                    'value' => $category,
                    'label' => $cropNames->isNotEmpty() ? $cropNames->take(3)->join(', ') : $category,
                    'category' => $category,
                    'count' => $categoryPrices->count(),
                    'title' => $cropNames->join(', '),
                ];
            });

        return $filters;
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

            $prediction = $this->getProductionPredictionData(
                $cropType->name,
                $farmer->municipality ?? 'BUGUIAS',
                $farmType,
                $plantingDate,
                $areaHectares
            );
            $predictedProduction = $prediction['predicted_production'];

            Log::info('storeCropPlan: Production predicted', [
                'predicted' => $predictedProduction,
                'source' => $prediction['prediction_source'],
                'productivity_mt_ha' => $prediction['productivity_mt_ha'],
                'confidence_score' => $prediction['confidence_score'],
            ]);

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
                'lgu_validation_status' => CropPlan::VALIDATION_PENDING,
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
                'message' => 'Crop plan submitted for LGU validation.',
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
                    'prediction_source' => $prediction['prediction_source'],
                    'prediction_source_label' => $prediction['prediction_source_label'],
                    'productivity_mt_ha' => $prediction['productivity_mt_ha'],
                    'confidence_score' => $prediction['confidence_score'],
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
     * Revise a crop plan that is still pending or was returned by the LGU.
     */
    public function updateCropPlan(Request $request, CropPlan $cropPlan)
    {
        /** @var \App\Models\Farmer $farmer */
        $farmer = Auth::guard('farmer')->user();

        if (!$farmer || $cropPlan->farmer_id !== $farmer->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        if (!in_array($cropPlan->lgu_validation_status, [CropPlan::VALIDATION_PENDING, CropPlan::VALIDATION_REJECTED], true)) {
            return response()->json([
                'success' => false,
                'message' => 'Only pending or rejected crop plans can be revised.',
            ], 422);
        }

        $validated = $request->validate([
            'crop_type_id' => 'required|exists:crop_types,id',
            'planting_date' => 'required|date',
            'area_hectares' => 'required|numeric|min:0.01|max:1000',
            'farm_type' => 'nullable|string|in:IRRIGATED,RAINFED',
            'planting_material_type' => 'nullable|string|in:SEED,SEEDLING',
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            $cropType = CropType::findOrFail($validated['crop_type_id']);
            $plantingDate = Carbon::parse($validated['planting_date']);
            $areaHectares = (float) $validated['area_hectares'];
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

            $daysToHarvest = $cropType->getDaysToHarvestForMaterial($plantingMaterialType);
            $expectedHarvestDate = $cropType->calculateHarvestDate($plantingDate, $plantingMaterialType);
            $prediction = $this->getProductionPredictionData(
                $cropType->name,
                $farmer->municipality ?? 'BUGUIAS',
                $farmType,
                $plantingDate,
                $areaHectares
            );

            $revisionCount = (int) ($cropPlan->lgu_validation_revision ?? 0);
            if ($cropPlan->lgu_validation_status === CropPlan::VALIDATION_REJECTED) {
                $revisionCount++;
            }

            $cropPlan->update([
                'crop_type_id' => $cropType->id,
                'crop_name' => $cropType->name,
                'planting_date' => $plantingDate,
                'expected_harvest_date' => $expectedHarvestDate,
                'area_hectares' => $areaHectares,
                'predicted_production' => $prediction['predicted_production'],
                'municipality' => strtoupper($farmer->municipality ?? 'BUGUIAS'),
                'farm_type' => $farmType,
                'planting_material_type' => $plantingMaterialType,
                'status' => 'planned',
                'notes' => $validated['notes'] ?? null,
                'lgu_validation_status' => CropPlan::VALIDATION_PENDING,
                'lgu_validated_by' => null,
                'lgu_validated_at' => null,
                'lgu_validation_notes' => null,
                'lgu_validation_revision' => $revisionCount,
                'submitted_to_da_at' => null,
            ]);

            $cropPlan->refresh();

            return response()->json([
                'success' => true,
                'message' => 'Crop plan resubmitted for LGU validation.',
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
                    'prediction_source' => $prediction['prediction_source'],
                    'prediction_source_label' => $prediction['prediction_source_label'],
                    'productivity_mt_ha' => $prediction['productivity_mt_ha'],
                    'confidence_score' => $prediction['confidence_score'],
                    'planting_material_type' => $cropPlan->planting_material_type,
                    'planting_material_label' => $cropPlan->planting_material_label,
                    'planting_event' => $cropPlan->toPlantingEvent(),
                    'harvest_event' => array_merge($cropPlan->toHarvestEvent(), [
                        'is_edoh' => true,
                    ]),
                    'fertilizer_events' => $cropPlan->toFertilizerEvents(),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to revise crop plan', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'crop_plan_id' => $cropPlan->id,
                'farmer_id' => $farmer->id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to revise crop plan. Please try again.',
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

            $prediction = $this->getProductionPredictionData(
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
                    'predicted_production' => $prediction['predicted_production'],
                    'predicted_production_formatted' => number_format($prediction['predicted_production'], 2) . ' MT',
                    'productivity_mt_ha' => $prediction['productivity_mt_ha'],
                    'productivity_mt_ha_formatted' => $prediction['productivity_mt_ha'] !== null
                        ? number_format($prediction['productivity_mt_ha'], 2) . ' MT/ha'
                        : null,
                    'average_yield_per_hectare' => $prediction['productivity_mt_ha'] ?? $cropType->average_yield_value,
                    'productivity_label' => $prediction['productivity_label'],
                    'prediction_source' => $prediction['prediction_source'],
                    'prediction_source_label' => $prediction['prediction_source_label'],
                    'confidence_score' => $prediction['confidence_score'],
                    'confidence_score_formatted' => $prediction['confidence_score'] !== null
                        ? number_format($prediction['confidence_score'], 2) . '%'
                        : null,
                    'ml_error' => $prediction['ml_error'],
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
            'actual_harvest_date' => 'nullable|date',
        ]);

        $updateData = ['status' => $validated['status']];
        if (!empty($validated['actual_harvest_date'])) {
            $updateData['actual_harvest_date'] = $validated['actual_harvest_date'];
        }

        $cropPlan->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'Status updated successfully!',
            'data' => $cropPlan,
        ]);
    }

    public function reportCropDamage(Request $request, CropPlan $cropPlan)
    {
        $farmer = Auth::guard('farmer')->user();

        if ($cropPlan->farmer_id !== $farmer->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        if (in_array($cropPlan->status, ['harvested', 'cancelled'], true)) {
            return response()->json([
                'success' => false,
                'message' => 'Damage reports can only be submitted for active crop plans.',
            ], 422);
        }

        $plantedArea = (float) $cropPlan->area_hectares;

        $validated = $request->validate([
            'damaged_area_hectares' => 'required|numeric|min:0.01|max:' . $plantedArea,
            'damage_cause' => 'required|string|in:' . implode(',', array_keys(CropPlan::DAMAGE_CAUSE_LABELS)),
            'damage_occurred_on' => [
                'required',
                'date',
                'before_or_equal:' . today()->format('Y-m-d'),
            ],
            'damage_notes' => 'nullable|string|max:500',
        ], [
            'damaged_area_hectares.max' => 'Damaged hectares cannot exceed the planted area of ' . number_format($plantedArea, 2) . ' hectares.',
        ]);

        $damageReport = $cropPlan->damageReports()
            ->whereIn('lgu_validation_status', [
                CropPlanDamageReport::VALIDATION_PENDING,
                CropPlanDamageReport::VALIDATION_REJECTED,
            ])
            ->latest()
            ->first();

        $revisionCount = (int) ($damageReport?->lgu_validation_revision ?? 0);
        if ($damageReport?->lgu_validation_status === CropPlanDamageReport::VALIDATION_REJECTED) {
            $revisionCount++;
        }

        if ($damageReport) {
            $damageReport->update([
                'damaged_area_hectares' => $validated['damaged_area_hectares'],
                'damage_cause' => $validated['damage_cause'],
                'damage_notes' => $validated['damage_notes'] ?? null,
                'damage_occurred_on' => $validated['damage_occurred_on'],
                'lgu_validation_status' => CropPlanDamageReport::VALIDATION_PENDING,
                'lgu_validated_by' => null,
                'lgu_validated_at' => null,
                'lgu_validation_notes' => null,
                'lgu_validation_revision' => $revisionCount,
                'submitted_to_da_at' => null,
                'applied_at' => null,
            ]);
        } else {
            $damageReport = CropPlanDamageReport::create([
                'crop_plan_id' => $cropPlan->id,
                'farmer_id' => $farmer->id,
                'damaged_area_hectares' => $validated['damaged_area_hectares'],
                'damage_cause' => $validated['damage_cause'],
                'damage_notes' => $validated['damage_notes'] ?? null,
                'damage_occurred_on' => $validated['damage_occurred_on'],
                'lgu_validation_status' => CropPlanDamageReport::VALIDATION_PENDING,
            ]);
        }

        $damageReport->load(['cropPlan', 'farmer']);
        $cropPlan->load('latestDamageReport')->refresh();

        return response()->json([
            'success' => true,
            'message' => 'Damage report submitted for LGU validation.',
            'data' => [
                'crop_plan_id' => $cropPlan->id,
                'display_status' => $cropPlan->display_status,
                'damage_report_id' => $damageReport->id,
                'damage_cause' => $damageReport->damage_cause,
                'damage_cause_label' => $damageReport->damage_cause_label,
                'damaged_area_hectares' => (float) $damageReport->damaged_area_hectares,
                'adjusted_area_hectares' => $cropPlan->adjusted_area_hectares,
                'adjusted_predicted_production' => $cropPlan->adjusted_predicted_production,
                'formatted_adjusted_production' => $cropPlan->formatted_adjusted_production,
                'production_loss_mt' => $damageReport->estimated_production_loss_mt,
                'damage_notes' => $damageReport->damage_notes,
                'damage_occurred_on' => optional($damageReport->damage_occurred_on)->format('Y-m-d'),
                'damage_occurred_on_formatted' => optional($damageReport->damage_occurred_on)->format('M d, Y'),
                'damage_reported_at' => optional($damageReport->updated_at)->toIso8601String(),
                'damage_reported_at_formatted' => optional($damageReport->updated_at)->format('M d, Y h:i A'),
                'lgu_validation_status' => $damageReport->lgu_validation_status,
                'lgu_validation_status_label' => $damageReport->lgu_validation_status_label,
                'planting_event' => $cropPlan->toPlantingEvent(),
                'harvest_event' => array_merge($cropPlan->toHarvestEvent(), [
                    'is_edoh' => true,
                ]),
                'damage_event' => $damageReport->toCalendarEvent(),
                'fertilizer_events' => $cropPlan->toFertilizerEvents(),
            ],
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
            ->with(['cropType', 'latestDamageReport'])
            ->orderBy('planting_date')
            ->get();

        // Format for calendar display
        $events = [];
        /** @var CropPlan $plan */
        foreach ($cropPlans as $plan) {
            $this->appendCropPlanEvents($events, $plan);
        }

        return response()->json([
            'success' => true,
            'events' => $events,
            'plans' => $cropPlans,
        ]);
    }

    private function appendCropPlanEvents(array &$events, CropPlan $plan): void
    {
        $plantingKey = $plan->planting_date->format('Y-m-d');
        if (!isset($events[$plantingKey])) {
            $events[$plantingKey] = [];
        }
        $events[$plantingKey][] = $plan->toPlantingEvent();

        $harvestKey = $plan->expected_harvest_date->format('Y-m-d');
        if (!isset($events[$harvestKey])) {
            $events[$harvestKey] = [];
        }
        $events[$harvestKey][] = array_merge($plan->toHarvestEvent(), [
            'is_edoh' => true,
        ]);

        foreach ($plan->toFertilizerEvents() as $dateKey => $dayEvents) {
            if (!isset($events[$dateKey])) {
                $events[$dateKey] = [];
            }

            foreach ($dayEvents as $event) {
                $events[$dateKey][] = $event;
            }
        }

        if ($plan->has_damage_report && $plan->damage_occurred_on) {
            $damageKey = $plan->damage_occurred_on->format('Y-m-d');

            if (!isset($events[$damageKey])) {
                $events[$damageKey] = [];
            }

            $events[$damageKey][] = $plan->toDamageEvent();
        }

        $latestDamageReport = $plan->relationLoaded('latestDamageReport')
            ? $plan->latestDamageReport
            : null;

        if (
            $latestDamageReport
            && in_array($latestDamageReport->lgu_validation_status, [
                CropPlanDamageReport::VALIDATION_PENDING,
                CropPlanDamageReport::VALIDATION_REJECTED,
            ], true)
            && $latestDamageReport->damage_occurred_on
        ) {
            $damageKey = $latestDamageReport->damage_occurred_on->format('Y-m-d');

            if (!isset($events[$damageKey])) {
                $events[$damageKey] = [];
            }

            $events[$damageKey][] = $latestDamageReport->toCalendarEvent();
        }
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
    private function getProductionPredictionData(
        string $cropName,
        string $municipality,
        string $farmType,
        Carbon $plantingDate,
        float $areaHectares
    ): array {
        $fallbackYield = CropType::getAverageYield($cropName);
        $basePayload = [
            'municipality' => strtoupper(trim($municipality)),
            'farm_type' => strtoupper(trim($farmType)),
            'month' => strtoupper($plantingDate->format('M')),
            'crop' => strtoupper(trim($cropName)),
            'area_harvested' => $areaHectares,
            'year' => $plantingDate->year,
        ];

        try {
            // Try ML API V2 prediction
            $result = $this->predictionService->predictProduction($basePayload);

            // V2 API returns prediction.production_mt (total production in MT)
            if (isset($result['success']) && $result['success'] && isset($result['prediction']['production_mt'])) {
                $predictedProduction = round((float) $result['prediction']['production_mt'], 2);
                $predictedProductivity = data_get($result, 'prediction.productivity_mt_ha');
                $confidenceScore = data_get($result, 'prediction.confidence_score');

                return [
                    'predicted_production' => $predictedProduction,
                    'productivity_mt_ha' => is_numeric($predictedProductivity)
                        ? round((float) $predictedProductivity, 2)
                        : ($areaHectares > 0 ? round($predictedProduction / $areaHectares, 2) : null),
                    'productivity_label' => 'Predicted productivity',
                    'prediction_source' => 'ml',
                    'prediction_source_label' => 'Live ML API',
                    'confidence_score' => is_numeric($confidenceScore) ? round((float) $confidenceScore, 2) : null,
                    'ml_error' => null,
                ];
            }

            $mlError = $result['error'] ?? 'ML prediction did not return a production value.';

            Log::warning('ML prediction unavailable for crop plan preview, using fallback', [
                'crop' => $cropName,
                'municipality' => $municipality,
                'farm_type' => $farmType,
                'month' => strtoupper($plantingDate->format('M')),
                'year' => $plantingDate->year,
                'error' => $mlError,
            ]);
        } catch (\Exception $e) {
            Log::warning('ML prediction failed, using fallback', [
                'crop' => $cropName,
                'error' => $e->getMessage(),
            ]);

            $mlError = $e->getMessage();
        }

        try {
            $directMlResult = $this->mlApiService->predict([
                'municipality' => $basePayload['municipality'],
                'farm_type' => $basePayload['farm_type'],
                'month' => $basePayload['month'],
                'crop' => $this->predictionService->patternBasedNormalization($basePayload['crop']),
                'area_planted' => $areaHectares,
                'area_harvested' => $areaHectares,
                'year' => $basePayload['year'],
            ]);

            if (($directMlResult['success'] ?? false) === true && isset($directMlResult['prediction']['production_mt'])) {
                $predictedProduction = round((float) $directMlResult['prediction']['production_mt'], 2);
                $predictedProductivity = data_get($directMlResult, 'prediction.productivity_mt_ha');
                $confidenceScore = data_get($directMlResult, 'prediction.confidence_score');

                Log::info('Farmer crop plan preview recovered via direct ML API retry', [
                    'crop' => $cropName,
                    'municipality' => $municipality,
                    'farm_type' => $farmType,
                    'month' => $basePayload['month'],
                    'year' => $basePayload['year'],
                    'previous_error' => $mlError ?? null,
                ]);

                return [
                    'predicted_production' => $predictedProduction,
                    'productivity_mt_ha' => is_numeric($predictedProductivity)
                        ? round((float) $predictedProductivity, 2)
                        : ($areaHectares > 0 ? round($predictedProduction / $areaHectares, 2) : null),
                    'productivity_label' => 'Predicted productivity',
                    'prediction_source' => 'ml',
                    'prediction_source_label' => 'Live ML API',
                    'confidence_score' => is_numeric($confidenceScore) ? round((float) $confidenceScore, 2) : null,
                    'ml_error' => null,
                ];
            }

            $mlError = $directMlResult['error'] ?? $mlError ?? 'Direct ML API retry did not return a production value.';
        } catch (\Exception $e) {
            Log::warning('Direct ML API retry failed for crop plan preview', [
                'crop' => $cropName,
                'municipality' => $municipality,
                'farm_type' => $farmType,
                'month' => $basePayload['month'],
                'year' => $basePayload['year'],
                'error' => $e->getMessage(),
            ]);

            $mlError = $e->getMessage();
        }

        // Fallback to simple calculation based on average yield
        return [
            'predicted_production' => round($areaHectares * $fallbackYield, 2),
            'productivity_mt_ha' => round($fallbackYield, 2),
            'productivity_label' => 'Average yield fallback',
            'prediction_source' => 'fallback',
            'prediction_source_label' => 'Fallback estimate',
            'confidence_score' => null,
            'ml_error' => $mlError ?? null,
        ];
    }

    /**
     * Get farmer's notifications (all types including announcements)
     */
    public function getNotifications(Request $request)
    {
        $farmer = Auth::guard('farmer')->user();
        $limit = min((int) $request->get('limit', 20), 100);

        $notifications = FarmerNotification::where('farmer_id', $farmer->id)
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
