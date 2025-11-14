<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class CropNameMapping extends Model
{
    use HasFactory;

    protected $fillable = [
        'database_name',
        'ml_name',
        'is_active',
        'notes'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get all active mappings as an associative array
     * Cached for performance
     * 
     * @return array [database_name => ml_name]
     */
    public static function getActiveMappings(): array
    {
        return Cache::remember('crop_name_mappings', 3600, function () {
            return self::where('is_active', true)
                ->pluck('ml_name', 'database_name')
                ->toArray();
        });
    }

    /**
     * Clear the mappings cache
     */
    public static function clearCache(): void
    {
        Cache::forget('crop_name_mappings');
    }

    /**
     * Boot method to clear cache on model changes
     */
    protected static function boot()
    {
        parent::boot();

        static::saved(function () {
            self::clearCache();
        });

        static::deleted(function () {
            self::clearCache();
        });
    }
}
