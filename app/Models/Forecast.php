<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Forecast Model
 * 
 * ML-generated forecasts stored in shared database
 */
class Forecast extends Model
{
    protected $table = 'forecasts';

    protected $fillable = [
        'municipality',
        'crop',
        'year',
        'month',
        'predicted_production',
        'confidence_score',
        'model_version',
        'metadata',
        'forecast_date',
    ];

    protected $casts = [
        'year' => 'integer',
        'predicted_production' => 'decimal:2',
        'confidence_score' => 'decimal:4',
        'metadata' => 'array',
        'forecast_date' => 'datetime',
    ];

    /**
     * Scope: Filter by municipality
     */
    public function scopeMunicipality($query, $municipality)
    {
        return $query->where('municipality', strtoupper($municipality));
    }

    /**
     * Scope: Filter by crop
     */
    public function scopeCrop($query, $crop)
    {
        return $query->where('crop', strtoupper($crop));
    }

    /**
     * Scope: Get latest forecasts
     */
    public function scopeLatest($query)
    {
        return $query->orderBy('forecast_date', 'desc');
    }

    /**
     * Scope: Get high confidence forecasts
     */
    public function scopeHighConfidence($query, $threshold = 0.8)
    {
        return $query->where('confidence_score', '>=', $threshold);
    }
}
