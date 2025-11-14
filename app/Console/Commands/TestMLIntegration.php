<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\PredictionService;
use App\Models\CropProduction;

class TestMLIntegration extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'ml:test
                            {--health : Test ML API health only}
                            {--prediction : Test a sample prediction}
                            {--cache : Test caching performance}
                            {--database : Test database connection}
                            {--all : Run all tests}';

    /**
     * The console command description.
     */
    protected $description = 'Test ML API integration with Laravel';

    protected $predictionService;

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->predictionService = new PredictionService();
        
        $runAll = $this->option('all');

        $this->info('🧪 Testing ML API Integration...');
        $this->newLine();

        // Health Check
        if ($this->option('health') || $runAll) {
            $this->testHealth();
        }

        // Database Check
        if ($this->option('database') || $runAll) {
            $this->testDatabase();
        }

        // Prediction Check
        if ($this->option('prediction') || $runAll) {
            $this->testPrediction();
        }

        // Cache Check
        if ($this->option('cache') || $runAll) {
            $this->testCache();
        }

        // If no options provided, run all tests
        if (!$this->option('health') && !$this->option('database') && 
            !$this->option('prediction') && !$this->option('cache') && !$runAll) {
            $this->testHealth();
            $this->testDatabase();
            $this->testPrediction();
            $this->testCache();
        }

        $this->newLine();
        $this->info('✅ All tests completed!');
    }

    private function testHealth()
    {
        $this->info('📡 Testing ML API Health...');
        
        $isHealthy = $this->predictionService->checkHealth();
        
        if ($isHealthy) {
            $this->line('   ✅ ML API is <fg=green>HEALTHY</> and responding');
        } else {
            $this->line('   ❌ ML API is <fg=red>UNAVAILABLE</>');
            $this->line('   💡 Start the ML API with: python ml_api_scalable.py');
        }
        
        $this->newLine();
    }

    private function testDatabase()
    {
        $this->info('🗄️  Testing Database Connection...');
        
        try {
            $count = CropProduction::count();
            $this->line("   ✅ Database connected: <fg=green>{$count} production records</>");
            
            if ($count === 0) {
                $this->line('   💡 Import CSV data: python database/migrate_csv_to_db.py');
            } else {
                // Show sample data
                $sample = CropProduction::orderBy('year', 'desc')->first();
                if ($sample) {
                    $this->line("   📊 Sample: {$sample->crop} in {$sample->municipality} ({$sample->year})");
                }
            }
        } catch (\Exception $e) {
            $this->line('   ❌ Database error: ' . $e->getMessage());
            $this->line('   💡 Run migration: php artisan migrate');
        }
        
        $this->newLine();
    }

    private function testPrediction()
    {
        $this->info('🔮 Testing Sample Prediction...');
        
        $sampleData = [
            'municipality' => 'ATOK',
            'crop' => 'CABBAGE',
            'farm_type' => 'IRRIGATED',
            'month' => 'JAN',
            'area_harvested' => 100,
            'year' => 2024
        ];
        
        $this->line('   Input: CABBAGE in ATOK (IRRIGATED, 100 ha)');
        
        $start = microtime(true);
        $result = $this->predictionService->predictProduction($sampleData);
        $duration = round((microtime(true) - $start) * 1000, 2);
        
        if (isset($result['success']) && $result['success']) {
            $production = $result['predicted_production'] ?? 'N/A';
            $confidence = isset($result['confidence']) ? round($result['confidence'] * 100, 1) . '%' : 'N/A';
            
            $this->line("   ✅ Predicted: <fg=green>{$production} MT</> (Confidence: {$confidence})");
            $this->line("   ⏱️  Response time: {$duration}ms");
        } else {
            $error = $result['error'] ?? 'Unknown error';
            $this->line("   ❌ Prediction failed: <fg=red>{$error}</>");
        }
        
        $this->newLine();
    }

    private function testCache()
    {
        $this->info('⚡ Testing Cache Performance...');
        
        $this->line('   First request (cold cache)...');
        $start = microtime(true);
        $this->predictionService->getValidValues();
        $firstDuration = round((microtime(true) - $start) * 1000, 2);
        
        $this->line('   Second request (warm cache)...');
        $start = microtime(true);
        $this->predictionService->getValidValues();
        $cachedDuration = round((microtime(true) - $start) * 1000, 2);
        
        $speedup = $firstDuration > 0 ? round($firstDuration / $cachedDuration, 2) : 0;
        
        $this->line("   ⏱️  First request: {$firstDuration}ms");
        $this->line("   ⏱️  Cached request: {$cachedDuration}ms");
        
        if ($speedup > 2) {
            $this->line("   ✅ Cache is <fg=green>WORKING</> ({$speedup}x faster!)");
        } elseif ($speedup > 1) {
            $this->line("   ⚠️  Cache is working but speedup is low ({$speedup}x)");
        } else {
            $this->line("   ❌ Cache may not be working properly");
            $this->line("   💡 Check ML_API_CACHE_ENABLED in .env");
        }
        
        $this->newLine();
    }
}
