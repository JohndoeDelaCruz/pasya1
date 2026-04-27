<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Municipality extends Model
{
    use HasFactory;

    public const BENGUET_MUNICIPALITIES = [
        'ATOK',
        'BAKUN',
        'BOKOD',
        'BUGUIAS',
        'ITOGON',
        'KABAYAN',
        'KAPANGAN',
        'KIBUNGAN',
        'LA TRINIDAD',
        'MANKAYAN',
        'SABLAN',
        'TUBA',
        'TUBLAY',
    ];

    protected $fillable = [
        'name',
        'province',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Scope to get only active municipalities
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get the municipality name in Title Case format
     */
    public function getNameDisplayAttribute(): string
    {
        return ucwords(strtolower($this->name ?? ''));
    }

    /**
     * Get the province name in Title Case format
     */
    public function getProvinceDisplayAttribute(): string
    {
        return ucwords(strtolower($this->province ?? ''));
    }
}
