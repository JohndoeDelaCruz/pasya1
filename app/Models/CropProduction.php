<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Crop Production Model
 * 
 * Shared table with ML API for historical production data
 * Enables Laravel to query the same database as the ML API
 */
class CropProduction extends Model
{
    protected $table = 'crop_production';

    protected $fillable = [
        'municipality',
        'crop',
        'year',
        'month',
        'farm_type',
        'area_harvested',
        'production',
        'productivity',
    ];

    protected $casts = [
        'year' => 'integer',
        'area_harvested' => 'decimal:2',
        'production' => 'decimal:2',
        'productivity' => 'decimal:2',
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
     * Scope: Filter by year
     */
    public function scopeYear($query, $year)
    {
        return $query->where('year', $year);
    }

    /**
     * Scope: Filter by month
     */
    public function scopeMonth($query, $month)
    {
        return $query->where('month', strtoupper($month));
    }

    /**
     * Scope: Filter by farm type
     */
    public function scopeFarmType($query, $farmType)
    {
        return $query->where('farm_type', strtoupper($farmType));
    }
}
