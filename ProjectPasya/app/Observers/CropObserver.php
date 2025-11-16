<?php

namespace App\Observers;

use App\Models\Crop;
use App\Models\CropNameMapping;
use App\Services\PredictionService;
use Illuminate\Support\Facades\Log;

class CropObserver
{
    /**
     * Handle the Crop "created" event.
     * Auto-create crop name mapping when a new crop is added
     */
    public function created(Crop $crop): void
    {
        $this->ensureCropMapping($crop->crop);
    }

    /**
     * Handle the Crop "updated" event.
     * Update mapping if crop name changed
     */
    public function updated(Crop $crop): void
    {
        if ($crop->isDirty('crop')) {
            $this->ensureCropMapping($crop->crop);
        }
    }

    /**
     * Ensure crop has a name mapping for ML API
     */
    private function ensureCropMapping(string $cropName): void
    {
        $cropName = strtoupper(trim($cropName));
        
        // Skip if mapping already exists
        if (CropNameMapping::where('database_name', $cropName)->exists()) {
            return;
        }
        
        // Create mapping using pattern recognition
        $service = app(PredictionService::class);
        $mlName = $service->patternBasedNormalization($cropName);
        
        CropNameMapping::create([
            'database_name' => $cropName,
            'ml_name' => $mlName,
            'is_active' => true,
            'notes' => 'Auto-created when crop was added to database',
        ]);
        
        Log::info("[CropObserver] Auto-created mapping for new crop", [
            'crop' => $cropName,
            'ml_name' => $mlName
        ]);
    }
}
