<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subsidy extends Model
{
    protected $fillable = [
        'full_name',
        'farmer_id',
        'crop',
        'subsidy_status',
        'subsidy_amount',
        'municipality',
        'farm_type',
        'year',
        'area_planted',
        'area_harvested',
        'production',
        'productivity',
    ];

    protected $casts = [
        'subsidy_amount' => 'decimal:2',
        'area_planted' => 'decimal:2',
        'area_harvested' => 'decimal:2',
        'production' => 'decimal:2',
        'productivity' => 'decimal:2',
    ];
}
