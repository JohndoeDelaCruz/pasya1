<?php

namespace App\Console\Commands;

use App\Models\Crop;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Data Quality Report Command
 * 
 * Analyzes crop data for quality issues including:
 * - Median-imputed placeholder values (Area=5, Production=55, Productivity=11)
 * - Outliers and suspicious values
 * - Data distribution analysis by municipality and crop
 * 
 * Created in response to John Paul's data quality questions
 */
class DataQualityReport extends Command
{
    protected $signature = 'data:quality-report 
                            {--crop= : Filter by specific crop (e.g., CABBAGE)}
                            {--municipality= : Filter by specific municipality}
                            {--export : Export detailed report to CSV}
                            {--fix-flags : Add is_imputed flag column to database}';

    protected $description = 'Generate comprehensive data quality report for crop production data';

    /**
     * Known median-imputed placeholder values
     * These appear when original data was missing and was filled with medians
     */
    private const MEDIAN_AREA = 5.0;
    private const MEDIAN_PRODUCTION = 55.0;
    private const MEDIAN_PRODUCTIVITY = 11.0;
    private const TOLERANCE = 0.01; // Allow for floating point comparison

    public function handle()
    {
        $this->info('');
        $this->info('╔══════════════════════════════════════════════════════════════╗');
        $this->info('║           CROP DATA QUALITY ANALYSIS REPORT                  ║');
        $this->info('║     Analyzing for median-imputed and suspicious values       ║');
        $this->info('╚══════════════════════════════════════════════════════════════╝');
        $this->info('');

        // Build base query with optional filters
        $query = Crop::query();
        
        if ($crop = $this->option('crop')) {
            $query->where('crop', strtoupper($crop));
            $this->info("Filtering by crop: " . strtoupper($crop));
        }
        
        if ($municipality = $this->option('municipality')) {
            $query->where('municipality', strtoupper($municipality));
            $this->info("Filtering by municipality: " . strtoupper($municipality));
        }

        $this->newLine();

        // ═══════════════════════════════════════════════════════════════
        // SECTION 1: Overall Statistics
        // ═══════════════════════════════════════════════════════════════
        $this->section1_OverallStats($query->clone());

        // ═══════════════════════════════════════════════════════════════
        // SECTION 2: Median-Imputed Records Detection
        // ═══════════════════════════════════════════════════════════════
        $this->section2_MedianImputedAnalysis($query->clone());

        // ═══════════════════════════════════════════════════════════════
        // SECTION 3: Productivity Distribution Analysis
        // ═══════════════════════════════════════════════════════════════
        $this->section3_ProductivityDistribution($query->clone());

        // ═══════════════════════════════════════════════════════════════
        // SECTION 4: Investigation of 43.99 mt/ha Value
        // ═══════════════════════════════════════════════════════════════
        $this->section4_Investigate4399();

        // ═══════════════════════════════════════════════════════════════
        // SECTION 5: Municipality-Crop Productivity Breakdown
        // ═══════════════════════════════════════════════════════════════
        if ($this->option('crop')) {
            $this->section5_MunicipalityBreakdown($this->option('crop'));
        }

        // ═══════════════════════════════════════════════════════════════
        // SECTION 6: Recommendations
        // ═══════════════════════════════════════════════════════════════
        $this->section6_Recommendations();

        // Export if requested
        if ($this->option('export')) {
            $this->exportDetailedReport($query->clone());
        }

        return Command::SUCCESS;
    }

    /**
     * SECTION 1: Overall Statistics
     */
    private function section1_OverallStats($query)
    {
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->info('  SECTION 1: OVERALL DATASET STATISTICS');
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        
        $stats = $query->selectRaw('
            COUNT(*) as total_records,
            COUNT(DISTINCT crop) as unique_crops,
            COUNT(DISTINCT municipality) as unique_municipalities,
            MIN(year) as min_year,
            MAX(year) as max_year,
            AVG(area_harvested) as avg_area,
            AVG(production) as avg_production,
            AVG(productivity) as avg_productivity,
            MIN(productivity) as min_productivity,
            MAX(productivity) as max_productivity
        ')->first();

        $this->table(
            ['Metric', 'Value'],
            [
                ['Total Records', number_format($stats->total_records)],
                ['Unique Crops', $stats->unique_crops],
                ['Unique Municipalities', $stats->unique_municipalities],
                ['Year Range', "{$stats->min_year} - {$stats->max_year}"],
                ['Avg Area Harvested', number_format($stats->avg_area, 2) . ' ha'],
                ['Avg Production', number_format($stats->avg_production, 2) . ' mt'],
                ['Avg Productivity', number_format($stats->avg_productivity, 2) . ' mt/ha'],
                ['Productivity Range', number_format($stats->min_productivity, 2) . ' - ' . number_format($stats->max_productivity, 2) . ' mt/ha'],
            ]
        );
        $this->newLine();
    }

    /**
     * SECTION 2: Median-Imputed Records Detection
     */
    private function section2_MedianImputedAnalysis($query)
    {
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->info('  SECTION 2: MEDIAN-IMPUTED PLACEHOLDER DETECTION');
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->warn('  Looking for pattern: Area=5, Production=55, Productivity=11');
        $this->newLine();

        $totalRecords = $query->clone()->count();

        // Count records with each median value
        $areaIs5 = $query->clone()
            ->whereBetween('area_harvested', [self::MEDIAN_AREA - self::TOLERANCE, self::MEDIAN_AREA + self::TOLERANCE])
            ->count();

        $productionIs55 = $query->clone()
            ->whereBetween('production', [self::MEDIAN_PRODUCTION - self::TOLERANCE, self::MEDIAN_PRODUCTION + self::TOLERANCE])
            ->count();

        $productivityIs11 = $query->clone()
            ->whereBetween('productivity', [self::MEDIAN_PRODUCTIVITY - self::TOLERANCE, self::MEDIAN_PRODUCTIVITY + self::TOLERANCE])
            ->count();

        // Records with ALL three median values
        $allThreeMedian = $query->clone()
            ->whereBetween('area_harvested', [self::MEDIAN_AREA - self::TOLERANCE, self::MEDIAN_AREA + self::TOLERANCE])
            ->whereBetween('production', [self::MEDIAN_PRODUCTION - self::TOLERANCE, self::MEDIAN_PRODUCTION + self::TOLERANCE])
            ->whereBetween('productivity', [self::MEDIAN_PRODUCTIVITY - self::TOLERANCE, self::MEDIAN_PRODUCTIVITY + self::TOLERANCE])
            ->count();

        // Records with Area=5 AND Production=55 (regardless of productivity)
        $areaAndProduction = $query->clone()
            ->whereBetween('area_harvested', [self::MEDIAN_AREA - self::TOLERANCE, self::MEDIAN_AREA + self::TOLERANCE])
            ->whereBetween('production', [self::MEDIAN_PRODUCTION - self::TOLERANCE, self::MEDIAN_PRODUCTION + self::TOLERANCE])
            ->count();

        $pctArea = $totalRecords > 0 ? ($areaIs5 / $totalRecords) * 100 : 0;
        $pctProduction = $totalRecords > 0 ? ($productionIs55 / $totalRecords) * 100 : 0;
        $pctProductivity = $totalRecords > 0 ? ($productivityIs11 / $totalRecords) * 100 : 0;
        $pctAllThree = $totalRecords > 0 ? ($allThreeMedian / $totalRecords) * 100 : 0;
        $pctAreaAndProd = $totalRecords > 0 ? ($areaAndProduction / $totalRecords) * 100 : 0;

        $this->table(
            ['Issue', 'Count', 'Percentage', 'Status'],
            [
                [
                    'Area = 5.0 ha (median)',
                    number_format($areaIs5),
                    number_format($pctArea, 1) . '%',
                    $pctArea > 30 ? '⚠️ HIGH' : ($pctArea > 10 ? '⚡ MODERATE' : '✓ OK')
                ],
                [
                    'Production = 55.0 mt (median)',
                    number_format($productionIs55),
                    number_format($pctProduction, 1) . '%',
                    $pctProduction > 30 ? '⚠️ HIGH' : ($pctProduction > 10 ? '⚡ MODERATE' : '✓ OK')
                ],
                [
                    'Productivity = 11.0 mt/ha (median)',
                    number_format($productivityIs11),
                    number_format($pctProductivity, 1) . '%',
                    $pctProductivity > 30 ? '⚠️ HIGH' : ($pctProductivity > 10 ? '⚡ MODERATE' : '✓ OK')
                ],
                [
                    'Area=5 AND Production=55',
                    number_format($areaAndProduction),
                    number_format($pctAreaAndProd, 1) . '%',
                    $pctAreaAndProd > 20 ? '⚠️ HIGH' : ($pctAreaAndProd > 5 ? '⚡ MODERATE' : '✓ OK')
                ],
                [
                    'ALL THREE median values',
                    number_format($allThreeMedian),
                    number_format($pctAllThree, 1) . '%',
                    $pctAllThree > 20 ? '🚨 CRITICAL' : ($pctAllThree > 5 ? '⚠️ HIGH' : '✓ OK')
                ],
            ]
        );

        if ($pctAllThree > 20) {
            $this->error("  ⚠️  WARNING: " . number_format($pctAllThree, 1) . "% of records appear to be median-imputed placeholders!");
            $this->warn("  These records may bias the ML model toward median values.");
        }

        $this->newLine();
    }

    /**
     * SECTION 3: Productivity Distribution Analysis
     */
    private function section3_ProductivityDistribution($query)
    {
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->info('  SECTION 3: PRODUCTIVITY DISTRIBUTION ANALYSIS');
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->warn('  Checking for the mysterious 43.99 mt/ha value and outliers');
        $this->newLine();

        // Define productivity ranges
        $ranges = [
            ['min' => 0, 'max' => 0.5, 'label' => '0 - 0.5 (Very Low)'],
            ['min' => 0.5, 'max' => 5, 'label' => '0.5 - 5 (Low)'],
            ['min' => 5, 'max' => 10, 'label' => '5 - 10 (Below Average)'],
            ['min' => 10, 'max' => 12, 'label' => '10 - 12 (Median Zone)'],
            ['min' => 12, 'max' => 20, 'label' => '12 - 20 (Average)'],
            ['min' => 20, 'max' => 30, 'label' => '20 - 30 (Good)'],
            ['min' => 30, 'max' => 40, 'label' => '30 - 40 (High)'],
            ['min' => 40, 'max' => 50, 'label' => '40 - 50 (Very High)'],
            ['min' => 50, 'max' => 100, 'label' => '50 - 100 (Exceptional)'],
            ['min' => 100, 'max' => 99999, 'label' => '> 100 (Outliers)'],
        ];

        $totalRecords = $query->clone()->count();
        $tableData = [];

        foreach ($ranges as $range) {
            $count = $query->clone()
                ->where('productivity', '>=', $range['min'])
                ->where('productivity', '<', $range['max'])
                ->count();
            
            $pct = $totalRecords > 0 ? ($count / $totalRecords) * 100 : 0;
            
            if ($count > 0) {
                $tableData[] = [
                    $range['label'],
                    number_format($count),
                    number_format($pct, 2) . '%',
                    str_repeat('█', (int)($pct / 2)) . str_repeat('░', 50 - (int)($pct / 2))
                ];
            }
        }

        $this->table(['Productivity Range (mt/ha)', 'Count', 'Percentage', 'Distribution'], $tableData);

        // Specifically check for values around 43.99
        $around44 = $query->clone()
            ->whereBetween('productivity', [43, 45])
            ->count();

        if ($around44 > 0) {
            $this->error("  Found {$around44} records with productivity between 43-45 mt/ha");
            
            // Show sample records
            $samples = $query->clone()
                ->whereBetween('productivity', [43, 45])
                ->limit(5)
                ->get(['id', 'crop', 'municipality', 'year', 'month', 'productivity', 'production', 'area_harvested']);
            
            $this->info('  Sample records with ~44 mt/ha productivity:');
            $this->table(
                ['ID', 'Crop', 'Municipality', 'Year', 'Month', 'Productivity', 'Production', 'Area'],
                $samples->map(fn($r) => [
                    $r->id, $r->crop, $r->municipality, $r->year, $r->month,
                    number_format($r->productivity, 2), number_format($r->production, 2), number_format($r->area_harvested, 2)
                ])->toArray()
            );
        } else {
            $this->info("  ✓ No records found with productivity between 43-45 mt/ha in raw data");
            $this->warn("  → The 43.99 value likely comes from a CALCULATION, not raw data!");
        }

        $this->newLine();
    }

    /**
     * SECTION 4: Investigate the 43.99 mt/ha Source
     */
    private function section4_Investigate4399()
    {
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->info('  SECTION 4: INVESTIGATING 43.99 mt/ha SOURCE');
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        
        // Check if 43.99 could come from a calculation error
        $this->info('  Possible sources of 43.99 value:');
        $this->newLine();

        // Check 1: Production/Area calculations that result in ~44
        $this->info('  1️⃣ Checking for Production/Area ≈ 44 calculations...');
        
        $calcMatches = Crop::query()
            ->whereRaw('(production / NULLIF(area_harvested, 0)) BETWEEN 43 AND 45')
            ->limit(10)
            ->get(['id', 'crop', 'municipality', 'production', 'area_harvested', 'productivity']);

        if ($calcMatches->count() > 0) {
            $this->warn("     Found records where Production/Area ≈ 44:");
            foreach ($calcMatches as $r) {
                $calculated = $r->area_harvested > 0 ? $r->production / $r->area_harvested : 0;
                $this->line("     - ID:{$r->id} {$r->crop}/{$r->municipality}: {$r->production}/{$r->area_harvested} = " . number_format($calculated, 2) . " (stored: {$r->productivity})");
            }
        } else {
            $this->info("     ✓ No raw records have Production/Area ≈ 44");
        }

        // Check 2: Weighted average that could produce 43.99
        $this->newLine();
        $this->info('  2️⃣ Checking weighted productivity averages by crop...');
        
        $weightedAvgs = Crop::query()
            ->select('crop')
            ->selectRaw('SUM(production) / NULLIF(SUM(area_harvested), 0) as weighted_productivity')
            ->selectRaw('SUM(area_harvested) as total_area')
            ->selectRaw('SUM(production) as total_production')
            ->groupBy('crop')
            ->havingRaw('SUM(production) / NULLIF(SUM(area_harvested), 0) BETWEEN 40 AND 50')
            ->get();

        if ($weightedAvgs->count() > 0) {
            $this->warn('     Found crops with weighted productivity 40-50 mt/ha:');
            $this->table(
                ['Crop', 'Weighted Productivity', 'Total Area', 'Total Production'],
                $weightedAvgs->map(fn($r) => [
                    $r->crop,
                    number_format($r->weighted_productivity, 2) . ' mt/ha',
                    number_format($r->total_area, 2) . ' ha',
                    number_format($r->total_production, 2) . ' mt'
                ])->toArray()
            );
        } else {
            $this->info("     ✓ No crop has overall weighted productivity between 40-50 mt/ha");
        }

        // Check 3: Dashboard calculation - dividing wrong values
        $this->newLine();
        $this->info('  3️⃣ Possible Dashboard Calculation Error:');
        $this->warn('     If 43.99 appears in the dashboard but NOT in raw data,');
        $this->warn('     check if the dashboard is incorrectly calculating:');
        $this->line('     • Production / Wrong_Area instead of Production / Area_Harvested');
        $this->line('     • Dividing MT by wrong units');
        $this->line('     • Using area_planted instead of area_harvested');

        $this->newLine();
    }

    /**
     * SECTION 5: Municipality Breakdown for specific crop
     */
    private function section5_MunicipalityBreakdown(string $crop)
    {
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->info("  SECTION 5: PRODUCTIVITY BY MUNICIPALITY FOR " . strtoupper($crop));
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

        $breakdown = Crop::query()
            ->where('crop', strtoupper($crop))
            ->select('municipality')
            ->selectRaw('COUNT(*) as records')
            ->selectRaw('AVG(productivity) as avg_productivity')
            ->selectRaw('MIN(productivity) as min_productivity')
            ->selectRaw('MAX(productivity) as max_productivity')
            ->selectRaw('SUM(CASE WHEN ABS(productivity - 11) < 0.01 THEN 1 ELSE 0 END) as median_count')
            ->groupBy('municipality')
            ->orderByDesc('avg_productivity')
            ->get();

        $this->table(
            ['Municipality', 'Records', 'Avg Prod (mt/ha)', 'Min', 'Max', 'Median=11 Count', '% Median'],
            $breakdown->map(fn($r) => [
                $r->municipality,
                $r->records,
                number_format($r->avg_productivity, 2),
                number_format($r->min_productivity, 2),
                number_format($r->max_productivity, 2),
                $r->median_count,
                number_format(($r->median_count / $r->records) * 100, 1) . '%'
            ])->toArray()
        );

        $this->newLine();
    }

    /**
     * SECTION 6: Recommendations
     */
    private function section6_Recommendations()
    {
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->info('  SECTION 6: RECOMMENDATIONS');
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->newLine();

        $recommendations = [
            [
                'priority' => '🔴 HIGH',
                'issue' => 'Median-Imputed Records',
                'action' => 'Add is_imputed flag to crops table to distinguish genuine vs placeholder data',
                'command' => 'php artisan data:quality-report --fix-flags'
            ],
            [
                'priority' => '🔴 HIGH',
                'issue' => '43.99 mt/ha Investigation',
                'action' => 'Check CropTrendsController.php line 449 - productivity calculation may divide production by wrong area',
                'command' => 'Review: predictedProductivity = production_mt / avgAreaHarvested'
            ],
            [
                'priority' => '🟡 MEDIUM',
                'issue' => 'Training Data Quality',
                'action' => 'Consider excluding records where productivity=11 from ML training OR weight them lower',
                'command' => 'Modify retrain_model.py filter to exclude productivity=11 ± 0.01'
            ],
            [
                'priority' => '🟡 MEDIUM',
                'issue' => 'Large Farm Bias',
                'action' => 'Use median instead of mean for area calculations to reduce outlier influence',
                'command' => 'Review avgAreaHarvested calculation in controllers'
            ],
            [
                'priority' => '🟢 LOW',
                'issue' => 'Data Validation',
                'action' => 'Add validation rules to reject suspicious patterns on data import',
                'command' => 'Update CropsImport.php validation'
            ],
        ];

        foreach ($recommendations as $rec) {
            $this->line("  {$rec['priority']} {$rec['issue']}");
            $this->line("     → {$rec['action']}");
            $this->line("     📌 {$rec['command']}");
            $this->newLine();
        }
    }

    /**
     * Export detailed report to CSV
     */
    private function exportDetailedReport($query)
    {
        $this->info('Exporting detailed report to CSV...');
        
        $filename = storage_path('app/data_quality_report_' . now()->format('Y-m-d_His') . '.csv');
        
        $records = $query->get();
        
        $fp = fopen($filename, 'w');
        
        // Header
        fputcsv($fp, [
            'id', 'crop', 'municipality', 'farm_type', 'year', 'month',
            'area_harvested', 'production', 'productivity',
            'is_median_area', 'is_median_production', 'is_median_productivity',
            'is_likely_imputed', 'calculated_productivity', 'productivity_diff'
        ]);
        
        foreach ($records as $record) {
            $isMedianArea = abs($record->area_harvested - self::MEDIAN_AREA) < self::TOLERANCE;
            $isMedianProduction = abs($record->production - self::MEDIAN_PRODUCTION) < self::TOLERANCE;
            $isMedianProductivity = abs($record->productivity - self::MEDIAN_PRODUCTIVITY) < self::TOLERANCE;
            $isLikelyImputed = $isMedianArea && $isMedianProduction;
            
            $calculatedProductivity = $record->area_harvested > 0 
                ? $record->production / $record->area_harvested 
                : 0;
            
            fputcsv($fp, [
                $record->id,
                $record->crop,
                $record->municipality,
                $record->farm_type,
                $record->year,
                $record->month,
                $record->area_harvested,
                $record->production,
                $record->productivity,
                $isMedianArea ? 1 : 0,
                $isMedianProduction ? 1 : 0,
                $isMedianProductivity ? 1 : 0,
                $isLikelyImputed ? 1 : 0,
                round($calculatedProductivity, 4),
                round($record->productivity - $calculatedProductivity, 4)
            ]);
        }
        
        fclose($fp);
        
        $this->info("Report exported to: {$filename}");
    }
}
