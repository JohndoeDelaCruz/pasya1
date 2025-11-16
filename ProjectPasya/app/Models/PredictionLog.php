<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Prediction Log Model
 * 
 * Tracks all ML API prediction requests for analytics and monitoring
 */
class PredictionLog extends Model
{
    protected $table = 'prediction_logs';

    public $timestamps = false; // Uses requested_at instead

    protected $fillable = [
        'municipality',
        'crop',
        'year',
        'month',
        'farm_type',
        'area_harvested',
        'predicted_production',
        'confidence_score',
        'model_version',
        'input_data',
        'output_data',
        'request_ip',
        'user_agent',
        'response_time_ms',
        'requested_at',
    ];

    protected $casts = [
        'year' => 'integer',
        'area_harvested' => 'decimal:2',
        'predicted_production' => 'decimal:2',
        'confidence_score' => 'decimal:4',
        'input_data' => 'array',
        'output_data' => 'array',
        'response_time_ms' => 'integer',
        'requested_at' => 'datetime',
    ];

    /**
     * Get average response time
     */
    public static function averageResponseTime()
    {
        return static::avg('response_time_ms');
    }

    /**
     * Get total prediction count
     */
    public static function totalPredictions()
    {
        return static::count();
    }

    /**
     * Get predictions by crop
     */
    public static function predictionsByCrop()
    {
        return static::selectRaw('crop, COUNT(*) as count')
            ->whereNotNull('crop')
            ->groupBy('crop')
            ->orderByDesc('count')
            ->get();
    }

    /**
     * Get predictions by municipality
     */
    public static function predictionsByMunicipality()
    {
        return static::selectRaw('municipality, COUNT(*) as count')
            ->whereNotNull('municipality')
            ->groupBy('municipality')
            ->orderByDesc('count')
            ->get();
    }

    /**
     * Get recent predictions
     */
    public function scopeRecent($query, $limit = 100)
    {
        return $query->orderBy('requested_at', 'desc')->limit($limit);
    }
}
