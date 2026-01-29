<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Crop;
use App\Models\CropProduction;
use Illuminate\Support\Facades\DB;

class SyncCropProduction extends Command
{
    protected $signature = 'crops:sync-production 
                            {--truncate : Clear existing crop_production data first}
                            {--dry-run : Show what would be synced without making changes}';

    protected $description = 'Sync data from crops table to crop_production table for ML API';

    public function handle()
    {
        $this->info('🔄 Syncing crops data to crop_production table...');
        $this->newLine();

        // Count source records
        $cropCount = Crop::count();
        $this->line("   📊 Source (crops table): {$cropCount} records");

        // Current crop_production count
        $currentCount = CropProduction::count();
        $this->line("   📊 Current crop_production: {$currentCount} records");

        if ($this->option('dry-run')) {
            $this->newLine();
            $this->info('   🔍 DRY RUN - No changes will be made');
            
            // Show sample of what would be synced
            $sample = Crop::select('municipality', 'crop', 'year', 'month', 'farm_type', 'area_harvested', 'production', 'productivity')
                ->limit(5)
                ->get();
            
            $this->newLine();
            $this->line('   Sample data to sync:');
            foreach ($sample as $row) {
                $this->line("   - {$row->crop} | {$row->municipality} | {$row->year}-{$row->month} | Prod: {$row->production}");
            }
            
            return 0;
        }

        if ($this->option('truncate')) {
            $this->line('   🗑️  Truncating crop_production table...');
            CropProduction::truncate();
        }

        $this->newLine();
        $this->line('   ⏳ Syncing data (this may take a moment)...');

        // Sync in batches for performance
        $batchSize = 1000;
        $synced = 0;

        Crop::select('municipality', 'crop', 'year', 'month', 'farm_type', 'area_harvested', 'area_planted', 'production', 'productivity')
            ->orderBy('id')
            ->chunk($batchSize, function ($crops) use (&$synced) {
                $insertData = [];
                
                foreach ($crops as $crop) {
                    $insertData[] = [
                        'municipality' => strtoupper($crop->municipality),
                        'crop' => strtoupper($crop->crop),
                        'year' => $crop->year,
                        'month' => strtoupper($crop->month),
                        'farm_type' => strtoupper($crop->farm_type),
                        'area_harvested' => $crop->area_harvested,
                        'production' => $crop->production,
                        'productivity' => $crop->productivity,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
                
                // Use upsert to avoid duplicates
                CropProduction::upsert(
                    $insertData,
                    ['municipality', 'crop', 'year', 'month', 'farm_type'],
                    ['area_harvested', 'production', 'productivity', 'updated_at']
                );
                
                $synced += count($crops);
                $this->output->write("\r   📥 Synced: {$synced} records");
            });

        $this->newLine();
        $this->newLine();

        // Final count
        $finalCount = CropProduction::count();
        $this->line("   ✅ Final crop_production count: {$finalCount} records");

        $this->newLine();
        $this->info('✅ Sync completed!');
        $this->line('   💡 Now retrain the ML model: python retrain_model.py');

        return 0;
    }
}
