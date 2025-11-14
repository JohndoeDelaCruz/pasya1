<?php

namespace App\Observers;

use App\Models\Municipality;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class MunicipalityObserver
{
    /**
     * Handle the Municipality "created" event.
     * Log new municipality for ML system awareness
     */
    public function created(Municipality $municipality): void
    {
        Log::info("[MunicipalityObserver] New municipality added to system", [
            'municipality' => $municipality->name,
            'province' => $municipality->province,
            'status' => 'ready_for_ml_predictions'
        ]);
        
        // Clear any municipality cache
        Cache::forget('active_municipalities');
    }

    /**
     * Handle the Municipality "updated" event.
     */
    public function updated(Municipality $municipality): void
    {
        if ($municipality->isDirty('name')) {
            Log::info("[MunicipalityObserver] Municipality name updated", [
                'old_name' => $municipality->getOriginal('name'),
                'new_name' => $municipality->name
            ]);
        }
        
        // Clear cache when updated
        Cache::forget('active_municipalities');
    }

    /**
     * Handle the Municipality "deleted" event.
     */
    public function deleted(Municipality $municipality): void
    {
        Log::info("[MunicipalityObserver] Municipality removed from system", [
            'municipality' => $municipality->name
        ]);
        
        Cache::forget('active_municipalities');
    }
}
