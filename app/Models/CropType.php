<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CropType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'category',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Scope to get only active crop types
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
