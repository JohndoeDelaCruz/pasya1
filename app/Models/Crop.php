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
        'uploaded_by',
    ];

    protected $casts = [
        'year' => 'integer',
        'area_planted' => 'decimal:2',
        'area_harvested' => 'decimal:2',
        'production' => 'decimal:2',
        'productivity' => 'decimal:2',
    ];

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
