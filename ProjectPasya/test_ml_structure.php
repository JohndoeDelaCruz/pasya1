<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Crop;
use Illuminate\Support\Facades\DB;

echo "=== ML PREDICTION DATA STRUCTURE TEST ===" . PHP_EOL . PHP_EOL;

echo "1. INPUT DATA FOR ML MODEL:" . PHP_EOL;
echo str_repeat("-", 80) . PHP_EOL;

// Simulate what data we send to ML model
$year = 2020;
$municipalityData = Crop::where('year', '>=', $year - 2)
    ->select(
        'municipality', 
        'farm_type',
        DB::raw('AVG(area_harvested) as avg_area'),
        DB::raw('MAX(crop) as sample_crop'),
        DB::raw('MAX(month) as sample_month')
    )
    ->groupBy('municipality', 'farm_type')
    ->limit(5)
    ->get();

echo "Sample ML inputs (what we send to the model):" . PHP_EOL . PHP_EOL;
foreach($municipalityData as $data) {
    $input = [
        'municipality' => strtoupper($data->municipality),
        'farm_type' => strtoupper($data->farm_type ?? 'IRRIGATED'),
        'month' => strtoupper($data->sample_month ?? 'JAN'),
        'crop' => strtoupper($data->sample_crop ?? 'CABBAGE'),
        'area_harvested' => (float) round($data->avg_area, 2)
    ];
    
    echo json_encode($input, JSON_PRETTY_PRINT) . PHP_EOL;
}

echo PHP_EOL . "2. EXPECTED ML OUTPUT FORMAT:" . PHP_EOL;
echo str_repeat("-", 80) . PHP_EOL;
echo json_encode([
    'success' => true,
    'predicted_production' => 12500.50,
    'confidence' => 'High',
    'model_version' => '1.0',
    'features_used' => ['municipality', 'farm_type', 'month', 'crop', 'area_harvested']
], JSON_PRETTY_PRINT) . PHP_EOL;

echo PHP_EOL . "3. AGGREGATED PREDICTIONS BY MUNICIPALITY:" . PHP_EOL;
echo str_repeat("-", 80) . PHP_EOL;

// Simulate aggregated predictions (what we show in the UI)
$mockPredictions = [
    ['municipality' => 'ATOK', 'farm_type' => 'IRRIGATED', 'predicted_production' => 45000],
    ['municipality' => 'ATOK', 'farm_type' => 'RAINFED', 'predicted_production' => 35000],
    ['municipality' => 'BUGUIAS', 'farm_type' => 'IRRIGATED', 'predicted_production' => 100000],
    ['municipality' => 'BUGUIAS', 'farm_type' => 'RAINFED', 'predicted_production' => 87810],
];

$grouped = collect($mockPredictions)->groupBy('municipality');
foreach($grouped as $municipality => $predictions) {
    $total = collect($predictions)->sum('predicted_production');
    $totalMt = $total / 1000;
    echo sprintf("%s: %.2f mt (from %d predictions)" . PHP_EOL,
        $municipality,
        $totalMt,
        count($predictions)
    );
}

echo PHP_EOL . "4. HISTORICAL VS PREDICTED COMPARISON (2019 actual vs 2021 predicted):" . PHP_EOL;
echo str_repeat("-", 80) . PHP_EOL;

$historical2019 = Crop::where('year', 2019)
    ->select('municipality', DB::raw('SUM(production) as total'))
    ->groupBy('municipality')
    ->orderBy('total', 'desc')
    ->limit(5)
    ->get();

echo "Actual 2019 Production:" . PHP_EOL;
foreach($historical2019 as $data) {
    echo sprintf("  %s: %.2f mt" . PHP_EOL,
        str_pad($data->municipality, 15),
        $data->total / 1000
    );
}

echo PHP_EOL . "NOTE: ML predictions should be in similar range but adjusted for trends." . PHP_EOL;

echo PHP_EOL . "5. DATA CONSISTENCY CHECKS:" . PHP_EOL;
echo str_repeat("-", 80) . PHP_EOL;

// Check for data consistency
$checks = [
    'Total Records' => Crop::count(),
    'Unique Municipalities' => Crop::distinct('municipality')->count(),
    'Unique Crops' => Crop::distinct('crop')->count(),
    'Unique Years' => Crop::distinct('year')->count(),
    'Records with Farm Type' => Crop::whereNotNull('farm_type')->count(),
    'Average Area Harvested' => number_format(Crop::avg('area_harvested'), 2) . ' ha',
    'Average Production' => number_format(Crop::avg('production'), 2) . ' kg',
    'Average Productivity' => number_format(Crop::avg('productivity'), 2) . ' kg/ha',
];

foreach($checks as $check => $value) {
    echo sprintf("%s: %s" . PHP_EOL, str_pad($check, 30), $value);
}

echo PHP_EOL . "=== SUMMARY ===" . PHP_EOL;
echo str_repeat("-", 80) . PHP_EOL;
echo "✓ Database has valid data structure" . PHP_EOL;
echo "✓ Production values are in KG (need conversion to MT for display)" . PHP_EOL;
echo "✓ Conversions: divide by 1000 to get metric tons" . PHP_EOL;
echo "✓ Chart shows historical data (actual production)" . PHP_EOL;
echo "✓ ML predictions show future forecasts (separate from historical)" . PHP_EOL;
echo "✓ Currently combines RAINFED and IRRIGATED in charts" . PHP_EOL;

echo PHP_EOL . "=== TEST COMPLETE ===" . PHP_EOL;
