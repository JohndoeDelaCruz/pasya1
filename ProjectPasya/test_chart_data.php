<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Crop;
use Illuminate\Support\Facades\DB;

echo "=== CHART DATA VERIFICATION TEST ===" . PHP_EOL . PHP_EOL;

// Test what the controller produces for the trend chart
echo "1. YEARLY TREND CHART DATA (All Years):" . PHP_EOL;
echo str_repeat("-", 80) . PHP_EOL;

$years = Crop::select('year')->distinct()->orderBy('year')->pluck('year')->toArray();
$municipalities = Crop::select('municipality')->distinct()->pluck('municipality')->toArray();

echo "Years found: " . implode(', ', $years) . PHP_EOL;
echo "Municipalities found: " . count($municipalities) . PHP_EOL . PHP_EOL;

// Get production by municipality and year (like the controller does)
$productionByMunicipalityYear = Crop::select(
        'municipality',
        'year',
        DB::raw('SUM(production) as total_production')
    )
    ->groupBy('municipality', 'year')
    ->orderBy('municipality')
    ->orderBy('year')
    ->get()
    ->groupBy('municipality');

echo "Sample: ATOK Production by Year (in mt):" . PHP_EOL;
if (isset($productionByMunicipalityYear['ATOK'])) {
    foreach($productionByMunicipalityYear['ATOK'] as $data) {
        $mt = round($data->total_production / 1000, 2);
        echo sprintf("  %d: %.2f mt (%s kg)" . PHP_EOL, 
            $data->year, 
            $mt,
            number_format($data->total_production, 2)
        );
    }
}

// Test monthly chart data
echo PHP_EOL . "2. MONTHLY CHART DATA (2020):" . PHP_EOL;
echo str_repeat("-", 80) . PHP_EOL;

$year = 2020;
$monthOrder = ['JAN', 'FEB', 'MAR', 'APR', 'MAY', 'JUN', 'JUL', 'AUG', 'SEP', 'OCT', 'NOV', 'DEC'];

$productionByMunicipalityMonth = Crop::where('year', $year)
    ->select(
        'municipality',
        'month',
        DB::raw('SUM(production) as total_production')
    )
    ->groupBy('municipality', 'month')
    ->orderBy('municipality')
    ->get()
    ->groupBy('municipality');

echo "Sample: BUGUIAS Monthly Production 2020 (in mt):" . PHP_EOL;
if (isset($productionByMunicipalityMonth['BUGUIAS'])) {
    foreach($monthOrder as $month) {
        $monthData = $productionByMunicipalityMonth['BUGUIAS']->firstWhere('month', $month);
        $production = $monthData ? $monthData->total_production : 0;
        $mt = round($production / 1000, 2);
        echo sprintf("  %s: %.2f mt" . PHP_EOL, $month, $mt);
    }
}

// Test 3: Verify production trend calculation
echo PHP_EOL . "3. PRODUCTION TREND CALCULATION:" . PHP_EOL;
echo str_repeat("-", 80) . PHP_EOL;

$selectedYear = 2020;
$currentYearProduction = Crop::where('year', $selectedYear)->sum('production');
$previousYearProduction = Crop::where('year', $selectedYear - 1)->sum('production');

$productionTrend = 0;
if ($previousYearProduction > 0) {
    $productionTrend = (($currentYearProduction - $previousYearProduction) / $previousYearProduction) * 100;
}

echo "Year {$selectedYear} Production: " . number_format($currentYearProduction, 2) . " kg" . PHP_EOL;
echo "Year " . ($selectedYear - 1) . " Production: " . number_format($previousYearProduction, 2) . " kg" . PHP_EOL;
echo "Production Trend: " . number_format($productionTrend, 2) . "%" . PHP_EOL;

if ($productionTrend > 0) {
    echo "Status: Production UP by " . number_format(abs($productionTrend), 1) . "%" . PHP_EOL;
} elseif ($productionTrend < 0) {
    echo "Status: Production DOWN by " . number_format(abs($productionTrend), 1) . "%" . PHP_EOL;
} else {
    echo "Status: Production UNCHANGED" . PHP_EOL;
}

// Test 4: Monthly production chart
echo PHP_EOL . "4. MONTHLY PRODUCTION CHART (All Municipalities, 2020):" . PHP_EOL;
echo str_repeat("-", 80) . PHP_EOL;

$monthlyProduction = Crop::where('year', 2020)
    ->select('month', DB::raw('SUM(production) as total_production'))
    ->groupBy('month')
    ->get()
    ->keyBy('month');

$monthLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
foreach($monthOrder as $index => $month) {
    $production = $monthlyProduction->has($month) ? $monthlyProduction[$month]->total_production : 0;
    $mt = round($production / 1000, 2);
    echo sprintf("%s: %.2f mt" . PHP_EOL, $monthLabels[$index], $mt);
}

// Test 5: Check if RAINFED vs IRRIGATED are separated
echo PHP_EOL . "5. FARM TYPE SEPARATION TEST (2020):" . PHP_EOL;
echo str_repeat("-", 80) . PHP_EOL;

$farmTypeData = Crop::where('year', 2020)
    ->select('farm_type', DB::raw('SUM(production) as total_production'))
    ->groupBy('farm_type')
    ->get();

foreach($farmTypeData as $data) {
    echo sprintf("%s: %.2f mt (%s kg)" . PHP_EOL,
        $data->farm_type,
        $data->total_production / 1000,
        number_format($data->total_production, 2)
    );
}

echo PHP_EOL . "NOTE: Currently the chart COMBINES both RAINFED and IRRIGATED data." . PHP_EOL;
echo "To show them separately, we would need to modify the groupBy clause." . PHP_EOL;

echo PHP_EOL . "=== TEST COMPLETE ===" . PHP_EOL;
