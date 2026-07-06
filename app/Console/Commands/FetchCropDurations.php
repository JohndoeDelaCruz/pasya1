<?php

namespace App\Console\Commands;

use App\Models\CropType;
use App\Services\CropDurationService;
use Illuminate\Console\Command;

class FetchCropDurations extends Command
{
    protected $signature = 'harvest:fetch-durations
                            {--apply : Update crop_types.days_to_harvest when a value is found}
                            {--only-missing : Only attempt for crop types with null days_to_harvest}
                            {--limit= : Limit number of crop types to process}';

    protected $description = 'Fetch days-to-harvest suggestions from Google for crop types (requires GOOGLE_CUSTOM_SEARCH_* env vars)';

    public function handle(CropDurationService $service)
    {
        $query = CropType::query()->orderBy('name');

        if ($this->option('only-missing')) {
            $query->whereNull('days_to_harvest');
        }

        if ($limit = (int) $this->option('limit')) {
            $query->limit($limit);
        }

        $types = $query->get();

        if ($types->isEmpty()) {
            $this->info('No crop types to process.');
            return Command::SUCCESS;
        }

        $this->info('Ensure GOOGLE_CUSTOM_SEARCH_API_KEY and GOOGLE_CUSTOM_SEARCH_ENGINE_ID are set to enable online lookup.');

        foreach ($types as $type) {
            $this->line("Processing: {$type->name} (current days: " . ($type->days_to_harvest ?? 'NULL') . ")");
            $days = $service->fetchDaysFromGoogle($type->name);

            if ($days === null) {
                $this->line('  No suggestion found.');
                continue;
            }

            $this->info("  Suggestion: {$days} days");

            if ($this->option('apply')) {
                $type->days_to_harvest = $days;
                $type->save();
                $this->info('  Saved to database.');
            }
        }

        return Command::SUCCESS;
    }
}
