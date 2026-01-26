<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Crop extends Model
{
    use HasFactory;

    protected $fillable = [
        'municipality',
        'farm_type',
        'year',
        'month',
        'crop',
        'area_planted',
        'area_harvested',
        'production',
        'productivity',
        'is_imputed',
        'data_quality_score',
        'uploaded_by',
    ];

    protected $casts = [
        'year' => 'integer',
        'area_planted' => 'decimal:2',
        'area_harvested' => 'decimal:2',
        'production' => 'decimal:2',
        'productivity' => 'decimal:2',
        'is_imputed' => 'boolean',
        'data_quality_score' => 'integer',
    ];

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Get the crop name in Title Case format
     */
    public function getCropDisplayAttribute(): string
    {
        return ucwords(strtolower($this->crop ?? ''));
    }

    /**
     * Get the municipality name in Title Case format
     */
    public function getMunicipalityDisplayAttribute(): string
    {
        return ucwords(strtolower($this->municipality ?? ''));
    }

    /**
     * Get the farm type in Title Case format
     */
    public function getFarmTypeDisplayAttribute(): string
    {
        return ucwords(strtolower($this->farm_type ?? ''));
    }

    /**
     * Scope to filter out imputed/placeholder records
     * Use this for more accurate analysis
     */
    public function scopeGenuineData($query)
    {
        return $query->where('is_imputed', false);
    }

    /**
     * Scope to filter by minimum data quality score
     */
    public function scopeMinQuality($query, int $minScore = 50)
    {
        return $query->where('data_quality_score', '>=', $minScore);
    }

    /**
     * Scope to filter out low-quality data for ML training
     * Excludes imputed records and those with quality score < 50
     */
    public function scopeForTraining($query)
    {
        return $query->where('is_imputed', false)
                     ->where('data_quality_score', '>=', 50)
                     ->where('productivity', '>', 0.5)
                     ->where('productivity', '<=', 40);
    }

    /**
     * Check if this record appears to be median-imputed
     */
    public function getIsLikelyImputedAttribute(): bool
    {
        $tolerance = 0.01;
        return abs($this->area_harvested - 5.0) < $tolerance 
            && abs($this->production - 55.0) < $tolerance;
    }

    /**
     * Get data quality status label
     */
    public function getQualityStatusAttribute(): string
    {
        if ($this->is_imputed) {
            return 'Imputed';
        }
        
        $score = $this->data_quality_score ?? 100;
        
        if ($score >= 80) return 'High';
        if ($score >= 50) return 'Medium';
        if ($score >= 30) return 'Low';
        return 'Very Low';
    }

    /**
     * Calculate productivity from production and area
     * Useful for verifying data consistency
     */
    public function getCalculatedProductivityAttribute(): ?float
    {
        if ($this->area_harvested > 0) {
            return round($this->production / $this->area_harvested, 2);
        }
        return null;
    }
}
