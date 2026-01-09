<?php

namespace App\Http\Controllers\Farmer;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\CropType;
use App\Models\CropProduction;
use App\Models\Crop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        
        // Get all farmer events for calendar
        $events = $this->getFarmerEvents($farmer, true);
        
        return view('farmers.calendar', compact('announcements', 'events'));
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
        
        // Get available crop types
        $cropTypes = CropType::active()->orderBy('name')->get();
        
        // Get production summary
        $summary = $this->getProductionSummary($farmer);
        
        return view('farmers.harvest-history', compact('crops', 'cropTypes', 'summary'));
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
        
        // Format for JavaScript
        foreach ($farmingEvents as $event) {
            $events[$event['date']][] = [
                'title' => $event['title'],
                'type' => $event['type'],
                'description' => $event['description'],
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
        // More comprehensive price list
        return [
            ['name' => 'Cabbage', 'emoji' => '🥬', 'price' => 77.43, 'change' => -24.00, 'unit' => 'kg', 'category' => 'Leafy Vegetables'],
            ['name' => 'Chinese Cabbage', 'emoji' => '🥬', 'price' => 149.00, 'change' => 16.00, 'unit' => 'kg', 'category' => 'Leafy Vegetables'],
            ['name' => 'Carrots', 'emoji' => '🥕', 'price' => 80.00, 'change' => -3.00, 'unit' => 'kg', 'category' => 'Root Vegetables'],
            ['name' => 'Potatoes', 'emoji' => '🥔', 'price' => 65.00, 'change' => 5.00, 'unit' => 'kg', 'category' => 'Root Vegetables'],
            ['name' => 'Tomatoes', 'emoji' => '🍅', 'price' => 45.00, 'change' => -8.00, 'unit' => 'kg', 'category' => 'Fruit Vegetables'],
            ['name' => 'Bell Pepper', 'emoji' => '🫑', 'price' => 120.00, 'change' => 10.00, 'unit' => 'kg', 'category' => 'Fruit Vegetables'],
            ['name' => 'Broccoli', 'emoji' => '🥦', 'price' => 95.00, 'change' => -5.00, 'unit' => 'kg', 'category' => 'Cruciferous'],
            ['name' => 'Cauliflower', 'emoji' => '🥬', 'price' => 85.00, 'change' => 3.00, 'unit' => 'kg', 'category' => 'Cruciferous'],
            ['name' => 'Lettuce', 'emoji' => '🥗', 'price' => 55.00, 'change' => -2.00, 'unit' => 'kg', 'category' => 'Leafy Vegetables'],
            ['name' => 'Sayote', 'emoji' => '🥒', 'price' => 35.00, 'change' => 0.00, 'unit' => 'kg', 'category' => 'Fruit Vegetables'],
            ['name' => 'String Beans', 'emoji' => '🫛', 'price' => 60.00, 'change' => 8.00, 'unit' => 'kg', 'category' => 'Legumes'],
            ['name' => 'Snap Peas', 'emoji' => '🫛', 'price' => 90.00, 'change' => -12.00, 'unit' => 'kg', 'category' => 'Legumes'],
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
}
