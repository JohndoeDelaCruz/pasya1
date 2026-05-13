<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CropPrice extends Model
{
    protected $fillable = [
        'crop_type_id',
        'price_per_kg',
        'previous_price',
    ];

    protected $casts = [
        'price_per_kg' => 'decimal:2',
        'previous_price' => 'decimal:2',
    ];

    public function cropType(): BelongsTo
    {
        return $this->belongsTo(CropType::class);
    }

    public function getPriceChangeAttribute(): float
    {
        if ($this->previous_price === null) {
            return 0.0;
        }
        return round((float) $this->price_per_kg - (float) $this->previous_price, 2);
    }
}
