<?php

namespace App\Observers;

use App\Models\CropType;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class CropTypeObserver
{
    /**
     * Handle the CropType "created" event.
     * Log new crop type for ML system awareness
     */
    public function created(CropType $cropType): void
    {
        Log::info("[CropTypeObserver] New crop type added to system", [
            'crop_type' => $cropType->name,
            'category' => $cropType->category,
            'status' => 'ready_for_ml_predictions'
        ]);
        
        // Clear any crop type cache
        Cache::forget('active_crop_types');
    }

    /**
     * Handle the CropType "updated" event.
     */
    public function updated(CropType $cropType): void
    {
        if ($cropType->isDirty('name')) {
            Log::info("[CropTypeObserver] Crop type name updated", [
                'old_name' => $cropType->getOriginal('name'),
                'new_name' => $cropType->name
            ]);
        }
        
        // Clear cache when updated
        Cache::forget('active_crop_types');
    }

    /**
     * Handle the CropType "deleted" event.
     */
    public function deleted(CropType $cropType): void
    {
        Log::info("[CropTypeObserver] Crop type removed from system", [
            'crop_type' => $cropType->name
        ]);
        
        Cache::forget('active_crop_types');
    }
}
