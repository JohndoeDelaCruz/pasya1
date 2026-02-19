<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Crop;
use App\Services\WeatherService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MapController extends Controller
{
    protected $weatherService;

    public function __construct(WeatherService $weatherService)
    {
        $this->weatherService = $weatherService;
    }

    /**
     * Display the interactive map page for admin.
     */
    public function index()
    {
        // Get weather data for ALL Benguet municipalities
        $municipalities = ['La Trinidad', 'Buguias', 'Atok', 'Bakun', 'Bokod', 'Itogon', 'Kabayan', 'Kapangan', 'Kibungan', 'Mankayan', 'Sablan', 'Tuba', 'Tublay'];

        $municipalityWeather = [];
        foreach ($municipalities as $municipality) {
            $municipalityWeather[] = $this->weatherService->getForecast($municipality, 4);
        }

        // Get hourly forecast for La Trinidad (main municipality)
        $hourlyForecast = $this->weatherService->getHourlyForecast('La Trinidad');

        // Get optimal planting window and climate risk
        $optimalWindow = $this->weatherService->getOptimalPlantingWindow($hourlyForecast);
        $climateRisk = $this->weatherService->getClimateRisk($municipalityWeather[0]['forecast']);

        // Best crops for the region
        $bestCrops = $this->getBestCrops();

        return view('admin.map.index', [
            'municipalityWeather' => $municipalityWeather,
            'hourlyForecast' => $hourlyForecast,
            'optimalWindow' => $optimalWindow,
            'climateRisk' => $climateRisk,
            'bestCrops' => $bestCrops,
        ]);
    }

    private function getBestCrops()
    {
        $topCrops = Crop::select('crop', DB::raw('AVG(productivity) as avg_productivity'))
            ->groupBy('crop')
            ->orderByDesc('avg_productivity')
            ->limit(3)
            ->get()
            ->pluck('crop')
            ->toArray();

        if (empty($topCrops)) {
            return 'Beans, Cabbage, Broccoli';
        }

        return implode(', ', $topCrops);
    }
}
