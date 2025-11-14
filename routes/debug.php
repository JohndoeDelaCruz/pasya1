<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use App\Models\Crop;

Route::get('/debug-chart-data', function () {
    // Get the same query as the controller
    $query = Crop::query();
    
    $trendData = $query->select(
        'municipality',
        DB::raw('YEAR(month) as year'),
        DB::raw('SUM(production) / 1000 as total_production')
    )
    ->groupBy('municipality', DB::raw('YEAR(month)'))
    ->orderBy(DB::raw('YEAR(month)'))
    ->get();
    
    $municipalities = $trendData->pluck('municipality')->unique()->values();
    $years = $trendData->pluck('year')->unique()->sort()->values();
    
    $datasets = [];
    $colors = [
        'rgb(59, 130, 246)', 'rgb(239, 68, 68)', 'rgb(34, 197, 94)', 
        'rgb(234, 179, 8)', 'rgb(168, 85, 247)', 'rgb(236, 72, 153)',
        'rgb(20, 184, 166)', 'rgb(251, 146, 60)', 'rgb(156, 163, 175)',
        'rgb(14, 165, 233)', 'rgb(124, 58, 237)', 'rgb(220, 38, 38)',
        'rgb(22, 163, 74)'
    ];
    
    foreach ($municipalities as $index => $municipality) {
        $municipalityData = $trendData->where('municipality', $municipality);
        $data = [];
        
        foreach ($years as $year) {
            $yearData = $municipalityData->where('year', $year)->first();
            $data[] = $yearData ? round($yearData->total_production, 2) : 0;
        }
        
        $datasets[] = [
            'label' => $municipality,
            'data' => $data,
            'borderColor' => $colors[$index % count($colors)],
            'backgroundColor' => 'transparent',
            'borderWidth' => 3,
            'tension' => 0.4,
            'fill' => false,
            'pointRadius' => 5,
            'pointHoverRadius' => 8
        ];
    }
    
    return response()->json([
        'total_records' => $trendData->count(),
        'municipalities' => $municipalities,
        'years' => $years,
        'sample_data' => $trendData->take(5),
        'datasets_count' => count($datasets),
        'first_dataset' => $datasets[0] ?? null,
        'chart_data' => [
            'labels' => $years->toArray(),
            'datasets' => $datasets
        ]
    ]);
});
