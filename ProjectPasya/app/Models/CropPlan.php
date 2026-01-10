<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class CropPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'farmer_id',
        'crop_type_id',
        'crop_name',
        'planting_date',
        'expected_harvest_date',
        'area_hectares',
        'predicted_production',
        'municipality',
        'farm_type',
        'status',
        'notes',
    ];

    protected $casts = [
        'planting_date' => 'date',
        'expected_harvest_date' => 'date',
        'area_hectares' => 'decimal:2',
        'predicted_production' => 'decimal:2',
    ];

    /**
     * Get the farmer that owns this crop plan
     */
    public function farmer(): BelongsTo
    {
        return $this->belongsTo(Farmer::class);
    }

    /**
     * Get the crop type for this plan
     */
    public function cropType(): BelongsTo
    {
        return $this->belongsTo(CropType::class);
    }

    /**
     * Scope for active/planned crops
     */
    public function scopeActive($query)
    {
        return $query->whereIn('status', ['planned', 'planted', 'growing']);
    }

    /**
     * Scope for upcoming harvests (within next 30 days)
     */
    public function scopeUpcomingHarvests($query, $days = 30)
    {
        return $query->where('expected_harvest_date', '>=', now())
                    ->where('expected_harvest_date', '<=', now()->addDays($days))
                    ->whereIn('status', ['planted', 'growing']);
    }

    /**
     * Scope for today's planting schedule
     */
    public function scopePlantingToday($query)
    {
        return $query->whereDate('planting_date', today())
                    ->where('status', 'planned');
    }

    /**
     * Scope for today's harvest schedule
     */
    public function scopeHarvestToday($query)
    {
        return $query->whereDate('expected_harvest_date', today())
                    ->whereIn('status', ['planted', 'growing']);
    }

    /**
     * Get days remaining until harvest
     */
    public function getDaysUntilHarvestAttribute(): int
    {
        if ($this->expected_harvest_date->isPast()) {
            return 0;
        }
        return now()->diffInDays($this->expected_harvest_date);
    }

    /**
     * Get progress percentage (how far along the growing cycle)
     */
    public function getProgressPercentageAttribute(): float
    {
        $totalDays = $this->planting_date->diffInDays($this->expected_harvest_date);
        $daysPassed = $this->planting_date->diffInDays(now());
        
        if ($totalDays <= 0) {
            return 100;
        }
        
        $percentage = ($daysPassed / $totalDays) * 100;
        return min(100, max(0, round($percentage, 1)));
    }

    /**
     * Check if harvest is due today
     */
    public function getIsHarvestDueAttribute(): bool
    {
        return $this->expected_harvest_date->isToday();
    }

    /**
     * Check if planting is due today
     */
    public function getIsPlantingDueAttribute(): bool
    {
        return $this->planting_date->isToday() && $this->status === 'planned';
    }

    /**
     * Get formatted production prediction
     */
    public function getFormattedProductionAttribute(): string
    {
        if (!$this->predicted_production) {
            return 'N/A';
        }
        return number_format($this->predicted_production, 2) . ' MT';
    }

    /**
     * Convert to calendar event format
     */
    public function toPlantingEvent(): array
    {
        return [
            'id' => $this->id,
            'title' => "Plant {$this->crop_name}",
            'type' => 'plant',
            'description' => "Plant {$this->crop_name} on {$this->area_hectares} hectares. Expected harvest: {$this->expected_harvest_date->format('M d, Y')}",
            'crop_plan_id' => $this->id,
            'area' => $this->area_hectares,
            'predicted_production' => $this->predicted_production,
        ];
    }

    /**
     * Convert to harvest calendar event format
     */
    public function toHarvestEvent(): array
    {
        return [
            'id' => $this->id,
            'title' => "Harvest {$this->crop_name}",
            'type' => 'harvest',
            'description' => "Expected harvest of {$this->crop_name} from {$this->area_hectares} ha. Predicted production: {$this->formatted_production}",
            'crop_plan_id' => $this->id,
            'area' => $this->area_hectares,
            'predicted_production' => $this->predicted_production,
        ];
    }
}
