<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Crop;
use Illuminate\Support\Facades\DB;

echo "=== DATA ACCURACY TEST ===" . PHP_EOL . PHP_EOL;

// Test 1: Check sample data
echo "1. SAMPLE CROPS DATA (First 3 records):" . PHP_EOL;
echo str_repeat("-", 80) . PHP_EOL;
$crops = Crop::take(3)->get(['municipality', 'crop', 'year', 'month', 'production', 'area_harvested', 'farm_type']);
foreach($crops as $crop) {
    echo sprintf("Municipality: %s | Crop: %s | Year: %s | Month: %s | Production: %.2f kg | Area: %.2f ha | Type: %s" . PHP_EOL,
        $crop->municipality,
        $crop->crop,
        $crop->year,
        $crop->month,
        $crop->production,
        $crop->area_harvested,
        $crop->farm_type
    );
}

// Test 2: Verify kg to mt conversion
echo PHP_EOL . "2. PRODUCTION TOTALS BY MUNICIPALITY (2020):" . PHP_EOL;
echo str_repeat("-", 80) . PHP_EOL;
$totals = Crop::where('year', 2020)
    ->select('municipality', DB::raw('SUM(production) as total_kg'))
    ->groupBy('municipality')
    ->orderBy('total_kg', 'desc')
    ->get();

foreach($totals as $total) {
    $mt = $total->total_kg / 1000;
    echo sprintf("%s: %s kg = %.2f mt" . PHP_EOL,
        str_pad($total->municipality, 20),
        number_format($total->total_kg, 2),
        $mt
    );
}

// Test 3: Verify controller calculations
echo PHP_EOL . "3. CONTROLLER CALCULATION VERIFICATION:" . PHP_EOL;
echo str_repeat("-", 80) . PHP_EOL;

// Simulate what the controller does
$year = 2020;
$query = Crop::where('year', $year);

$totalAreaHarvested = $query->sum('area_harvested');
$averageYield = $query->avg('productivity');
$totalProduction = $query->sum('production');

echo "Total Area Harvested: " . number_format($totalAreaHarvested, 2) . " ha" . PHP_EOL;
echo "Average Yield: " . number_format($averageYield, 2) . " kg/ha" . PHP_EOL;
echo "Total Production: " . number_format($totalProduction, 2) . " kg = " . number_format($totalProduction/1000, 2) . " mt" . PHP_EOL;

// Test 4: Top crops
echo PHP_EOL . "4. TOP 3 CROPS BY PRODUCTION (2020):" . PHP_EOL;
echo str_repeat("-", 80) . PHP_EOL;
$topCrops = Crop::where('year', 2020)
    ->select('crop', DB::raw('SUM(production) as total_production'))
    ->groupBy('crop')
    ->orderByDesc('total_production')
    ->limit(3)
    ->get();

foreach($topCrops as $crop) {
    echo sprintf("%s: %s kg = %.2f mt" . PHP_EOL,
        str_pad($crop->crop, 20),
        number_format($crop->total_production, 2),
        $crop->total_production / 1000
    );
}

// Test 5: Monthly breakdown for a municipality
echo PHP_EOL . "5. MONTHLY BREAKDOWN FOR ATOK (2020):" . PHP_EOL;
echo str_repeat("-", 80) . PHP_EOL;
$monthOrder = ['JAN', 'FEB', 'MAR', 'APR', 'MAY', 'JUN', 'JUL', 'AUG', 'SEP', 'OCT', 'NOV', 'DEC'];
$monthlyData = Crop::where('year', 2020)
    ->where('municipality', 'ATOK')
    ->select('month', DB::raw('SUM(production) as total_production'))
    ->groupBy('month')
    ->get()
    ->keyBy('month');

foreach($monthOrder as $month) {
    $production = $monthlyData->has($month) ? $monthlyData[$month]->total_production : 0;
    $mt = $production / 1000;
    echo sprintf("%s: %s kg = %.2f mt" . PHP_EOL,
        $month,
        str_pad(number_format($production, 2), 15, ' ', STR_PAD_LEFT),
        $mt
    );
}

// Test 6: Verify data types
echo PHP_EOL . "6. DATA TYPE VERIFICATION:" . PHP_EOL;
echo str_repeat("-", 80) . PHP_EOL;
$sample = Crop::first();
if ($sample) {
    echo "Production - Type: " . gettype($sample->production) . " | Value: " . $sample->production . PHP_EOL;
    echo "Area Harvested - Type: " . gettype($sample->area_harvested) . " | Value: " . $sample->area_harvested . PHP_EOL;
    echo "Productivity - Type: " . gettype($sample->productivity) . " | Value: " . $sample->productivity . PHP_EOL;
    echo "Year - Type: " . gettype($sample->year) . " | Value: " . $sample->year . PHP_EOL;
}

// Test 7: Count records by farm type
echo PHP_EOL . "7. RECORDS BY FARM TYPE:" . PHP_EOL;
echo str_repeat("-", 80) . PHP_EOL;
$farmTypes = Crop::select('farm_type', DB::raw('COUNT(*) as count'), DB::raw('SUM(production) as total_prod'))
    ->groupBy('farm_type')
    ->get();

foreach($farmTypes as $type) {
    echo sprintf("%s: %d records | Total: %s kg = %.2f mt" . PHP_EOL,
        str_pad($type->farm_type, 15),
        $type->count,
        number_format($type->total_prod, 2),
        $type->total_prod / 1000
    );
}

echo PHP_EOL . "=== TEST COMPLETE ===" . PHP_EOL;
