<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Model Metadata Model
 * 
 * Tracks ML model versions, performance metrics, and metadata
 */
class ModelMetadata extends Model
{
    protected $table = 'model_metadata';

    protected $fillable = [
        'model_name',
        'model_version',
        'model_type',
        'accuracy',
        'mae',
        'rmse',
        'hyperparameters',
        'feature_importance',
        'description',
        'trained_at',
        'is_active',
    ];

    protected $casts = [
        'accuracy' => 'decimal:4',
        'mae' => 'decimal:2',
        'rmse' => 'decimal:2',
        'hyperparameters' => 'array',
        'feature_importance' => 'array',
        'trained_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * Get active models
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get latest model
     */
    public static function getLatestModel()
    {
        return static::active()
            ->orderBy('trained_at', 'desc')
            ->first();
    }

    /**
     * Get model by version
     */
    public static function getByVersion(string $modelName, string $version)
    {
        return static::where('model_name', $modelName)
            ->where('model_version', $version)
            ->first();
    }
}
