<?php

namespace App\Console\Commands;

use App\Models\Crop;
use App\Services\PredictionService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Investigates the 43.99 mt/ha productivity value issue
 * 
 * This command traces the calculation flow to identify where
 * the 43.99 value originates from.
 * 
 * Investigation based on John Paul's questions:
 * 1. The ML model predicts PRODUCTIVITY (mt/ha) first
 * 2. Production = Productivity × Area
 * 3. But the dashboard calculates: production_mt / avgAreaHarvested
 * 
 * If the ML predicts production_mt based on area A, but dashboard
 * divides by a different avgAreaHarvested B, then:
 * displayed_productivity = (predicted_productivity × A) / B
 * 
 * This can produce unexpected values like 43.99
 */
class InvestigatePrediction extends Command
{
    protected $signature = 'predict:investigate 
                            {crop : Crop name (e.g., CABBAGE)}
                            {municipality : Municipality name (e.g., BUGUIAS)}
                            {--farm_type=IRRIGATED : Farm type}
                            {--month=JAN : Month}
                            {--area=10 : Area in hectares}';

    protected $description = 'Trace prediction calculation to investigate unexpected productivity values';

    public function handle()
    {
        $crop = strtoupper($this->argument('crop'));
        $municipality = strtoupper($this->argument('municipality'));
        $farmType = strtoupper($this->option('farm_type'));
        $month = strtoupper($this->option('month'));
        $inputArea = floatval($this->option('area'));

        $this->info('');
        $this->info('╔══════════════════════════════════════════════════════════════╗');
        $this->info('║      PREDICTION CALCULATION INVESTIGATION                     ║');
        $this->info('╚══════════════════════════════════════════════════════════════╝');
        $this->info('');

        // Input Parameters
        $this->info('📋 INPUT PARAMETERS:');
        $this->table(['Parameter', 'Value'], [
            ['Crop', $crop],
            ['Municipality', $municipality],
            ['Farm Type', $farmType],
            ['Month', $month],
            ['Input Area', $inputArea . ' ha'],
        ]);

        // Step 1: Get historical productivity from database
        $this->newLine();
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->info('STEP 1: DATABASE HISTORICAL PRODUCTIVITY');
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

        $historicalData = Crop::where('crop', $crop)
            ->where('municipality', $municipality)
            ->where('farm_type', $farmType)
            ->where('month', $month)
            ->get(['year', 'productivity', 'production', 'area_harvested']);

        if ($historicalData->isEmpty()) {
            $this->warn('  No historical data found for this combination.');
        } else {
            $this->table(
                ['Year', 'Productivity (mt/ha)', 'Production (mt)', 'Area (ha)', 'Calc: Prod/Area'],
                $historicalData->map(fn($r) => [
                    $r->year,
                    number_format($r->productivity, 2),
                    number_format($r->production, 2),
                    number_format($r->area_harvested, 2),
                    $r->area_harvested > 0 ? number_format($r->production / $r->area_harvested, 2) : 'N/A'
                ])->toArray()
            );

            $avgHistoricalProductivity = $historicalData->avg('productivity');
            $maxHistoricalProductivity = $historicalData->max('productivity');
            $this->info("  Average Historical Productivity: " . number_format($avgHistoricalProductivity, 2) . " mt/ha");
            $this->info("  Maximum Historical Productivity: " . number_format($maxHistoricalProductivity, 2) . " mt/ha");
        }

        // Step 2: Get avgAreaHarvested as used in dashboard
        $this->newLine();
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->info('STEP 2: AVERAGE AREA CALCULATION (as used in Dashboard)');
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

        $avgAreaHarvested = Crop::where('crop', $crop)
            ->where('municipality', $municipality)
            ->where('farm_type', $farmType)
            ->avg('area_harvested') ?? 100;

        $medianArea = Crop::where('crop', $crop)
            ->where('municipality', $municipality)
            ->where('farm_type', $farmType)
            ->orderBy('area_harvested')
            ->get('area_harvested')
            ->median('area_harvested');

        $this->table(['Calculation', 'Value'], [
            ['AVG(area_harvested) - Used in Dashboard', number_format($avgAreaHarvested, 2) . ' ha'],
            ['MEDIAN(area_harvested) - Recommended', number_format($medianArea ?? 0, 2) . ' ha'],
            ['Input Area from Request', number_format($inputArea, 2) . ' ha'],
        ]);

        if (abs($avgAreaHarvested - $inputArea) > 1) {
            $this->warn("  ⚠️ Input area ($inputArea ha) differs from avgAreaHarvested ($avgAreaHarvested ha)!");
            $this->warn("  This mismatch can cause unexpected productivity calculations.");
        }

        // Step 3: Make ML Prediction
        $this->newLine();
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->info('STEP 3: ML API PREDICTION');
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

        try {
            $predictionService = new PredictionService();
            
            // Prediction with INPUT area
            $predictionInput = $predictionService->predictProduction([
                'municipality' => $municipality,
                'farm_type' => $farmType,
                'month' => $month,
                'crop' => $crop,
                'area_harvested' => $inputArea
            ]);

            // Prediction with AVG area (what dashboard uses)
            $predictionAvg = $predictionService->predictProduction([
                'municipality' => $municipality,
                'farm_type' => $farmType,
                'month' => $month,
                'crop' => $crop,
                'area_harvested' => $avgAreaHarvested
            ]);

            $this->info('  Prediction with INPUT area (' . $inputArea . ' ha):');
            if (isset($predictionInput['success']) && $predictionInput['success']) {
                $productionMT = $predictionInput['prediction']['production_mt'] ?? 0;
                $this->line("    → Production: " . number_format($productionMT, 2) . " MT");
                $this->line("    → Implied Productivity: " . number_format($productionMT / $inputArea, 2) . " mt/ha");
            } else {
                $this->error('    → Prediction failed: ' . ($predictionInput['error'] ?? 'Unknown error'));
            }

            $this->newLine();
            $this->info('  Prediction with AVG area (' . number_format($avgAreaHarvested, 2) . ' ha):');
            if (isset($predictionAvg['success']) && $predictionAvg['success']) {
                $productionMT = $predictionAvg['prediction']['production_mt'] ?? 0;
                $this->line("    → Production: " . number_format($productionMT, 2) . " MT");
                $this->line("    → Implied Productivity: " . number_format($productionMT / $avgAreaHarvested, 2) . " mt/ha");
            } else {
                $this->error('    → Prediction failed: ' . ($predictionAvg['error'] ?? 'Unknown error'));
            }

        } catch (\Exception $e) {
            $this->error('  ML API Error: ' . $e->getMessage());
        }

        // Step 4: Simulate Dashboard Calculation
        $this->newLine();
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->info('STEP 4: DASHBOARD CALCULATION SIMULATION');
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->warn('  Dashboard formula: predictedProductivity = production_mt / avgAreaHarvested');
        $this->newLine();

        if (isset($predictionInput['success']) && $predictionInput['success']) {
            $productionMT = $predictionInput['prediction']['production_mt'] ?? 0;
            
            // Correct calculation (divide by input area)
            $correctProductivity = $inputArea > 0 ? $productionMT / $inputArea : 0;
            
            // Dashboard calculation (divide by avgAreaHarvested - potential bug)
            $dashboardProductivity = $avgAreaHarvested > 0 ? $productionMT / $avgAreaHarvested : 0;

            $this->table(['Calculation Method', 'Result', 'Status'], [
                [
                    'Correct: production / INPUT area',
                    number_format($correctProductivity, 2) . ' mt/ha',
                    '✓ Correct'
                ],
                [
                    'Dashboard: production / AVG area',
                    number_format($dashboardProductivity, 2) . ' mt/ha',
                    abs($correctProductivity - $dashboardProductivity) > 1 ? '⚠️ Different!' : '~ Similar'
                ],
            ]);

            // Check if this could produce 43.99
            if (abs($dashboardProductivity - 43.99) < 1 || abs($correctProductivity - 43.99) < 1) {
                $this->error('');
                $this->error('  🎯 FOUND IT! One of these calculations produces ~43.99 mt/ha!');
            }
        }

        // Step 5: Explain the potential issue
        $this->newLine();
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->info('STEP 5: ANALYSIS & RECOMMENDATIONS');
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

        $this->line('');
        $this->line('  📌 THE 43.99 ISSUE EXPLANATION:');
        $this->line('');
        $this->line('  The ML model works as follows:');
        $this->line('    1. Input: Crop, Municipality, Farm Type, Month, Area');
        $this->line('    2. Predicts: Productivity (mt/ha)');
        $this->line('    3. Returns: production_mt = predicted_productivity × input_area');
        $this->line('');
        $this->line('  The Dashboard then INCORRECTLY calculates:');
        $this->line('    displayed_productivity = production_mt / avgAreaHarvested');
        $this->line('');
        $this->line('  If input_area ≠ avgAreaHarvested, this creates a scaling error!');
        $this->line('');
        $this->line('  Example: If ML predicts 15 mt/ha productivity');
        $this->line('    - Input area = 100 ha → production_mt = 1500 MT');
        $this->line('    - avgAreaHarvested = 35 ha (different from input!)');
        $this->line('    - Dashboard shows: 1500 / 35 = 42.86 mt/ha (WRONG!)');
        $this->line('');
        
        $this->info('  🔧 FIX:');
        $this->info('     The dashboard should either:');
        $this->info('     A) Use the same area for input AND division');
        $this->info('     B) Request productivity directly from ML API (if available)');
        $this->info('     C) Store the input_area with prediction and use it for division');

        return Command::SUCCESS;
    }
}
