<?php

namespace App\Console\Commands;

use App\Models\CropPlan;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class HarvestCheck extends Command
{
    protected $signature = 'harvest:check
                            {--fix : Update crop plans with recalculated expected harvest date}
                            {--status= : Comma-separated statuses to include (e.g., planted,growing)}
                            {--farmer= : Farmer ID to limit the check}
                            {--threshold=7 : Minimum day difference to report}
                            {--output= : CSV output path (storage/app/...)}';

    protected $description = 'Validate crop plan expected harvest dates against calculated durations';

    public function handle()
    {
        $this->info('Starting harvest date check...');

        $threshold = (int) $this->option('threshold');
        $statusOpt = (string) $this->option('status');
        $farmerId = $this->option('farmer');

        $query = CropPlan::query()->whereNotNull('planting_date')->whereNotNull('expected_harvest_date');

        if ($statusOpt !== '') {
            $statuses = array_map('trim', explode(',', $statusOpt));
            $query->whereIn('status', $statuses);
        }

        if ($farmerId) {
            $query->where('farmer_id', $farmerId);
        }

        $plans = $query->with('cropType')->get();

        if ($plans->isEmpty()) {
            $this->info('No crop plans found for the given filters.');
            return Command::SUCCESS;
        }

        $rows = [];

        foreach ($plans as $plan) {
            $cropType = $plan->cropType;

            if (!$cropType) {
                $rows[] = [
                    'id' => $plan->id,
                    'crop' => $plan->crop_name,
                    'planting_date' => $plan->planting_date?->format('Y-m-d') ?? '',
                    'expected_harvest_date' => $plan->expected_harvest_date?->format('Y-m-d') ?? '',
                    'calculated_harvest_date' => 'NO_CROPTYPE',
                    'expected_days' => '',
                    'calculated_days' => '',
                    'diff_days' => '',
                ];
                continue;
            }

            $calculated = $cropType->calculateHarvestDate($plan->planting_date, $plan->planting_material_type);

            $expected = $plan->expected_harvest_date;

            $expectedDays = $plan->planting_date->diffInDays($expected);
            $calculatedDays = $plan->planting_date->diffInDays($calculated);
            $diff = $calculatedDays - $expectedDays;

            if (abs($diff) >= $threshold) {
                $rows[] = [
                    'id' => $plan->id,
                    'crop' => $plan->crop_name,
                    'planting_date' => $plan->planting_date->format('Y-m-d'),
                    'expected_harvest_date' => $expected->format('Y-m-d'),
                    'calculated_harvest_date' => $calculated->format('Y-m-d'),
                    'expected_days' => $expectedDays,
                    'calculated_days' => $calculatedDays,
                    'diff_days' => $diff,
                ];

                if ($this->option('fix')) {
                    $plan->expected_harvest_date = $calculated;
                    $plan->save();
                    $this->info("Updated plan ID {$plan->id} expected_harvest_date -> {$calculated->format('Y-m-d')}");
                }
            }
        }

        if (empty($rows)) {
            $this->info('No significant harvest date discrepancies found.');
            return Command::SUCCESS;
        }

        // Prepare CSV
        $outputPath = $this->option('output') ?: ('harvest-date-check-' . now()->format('Ymd-His') . '.csv');
        $fullPath = storage_path('app/' . $outputPath);

        $dir = dirname($fullPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $fp = fopen($fullPath, 'w');
        fputcsv($fp, array_keys($rows[0]));
        foreach ($rows as $r) {
            fputcsv($fp, $r);
        }
        fclose($fp);

        $this->info('Found ' . count($rows) . ' mismatches. CSV written to: ' . $fullPath);

        return Command::SUCCESS;
    }
}
