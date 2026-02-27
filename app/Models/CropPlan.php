<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

/**
 * @property int $id
 * @property int $farmer_id
 * @property int $crop_type_id
 * @property string $crop_name
 * @property \Carbon\Carbon $planting_date
 * @property \Carbon\Carbon $expected_harvest_date
 * @property float $area_hectares
 * @property float $predicted_production
 * @property string $municipality
 * @property string $farm_type
 * @property string $status
 * @property string|null $notes
 * @property string $formatted_production
 * @property int $days_until_harvest
 * @property float $progress_percentage
 */
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

    /**
     * Generate fertilizer schedule events based on growth stages.
     *
     * Uses a general formula based on the crop's total growth cycle:
     *  - 25% of cycle: Side Dress 1 (Vegetative growth stage)
     *  - 50% of cycle: Side Dress 2 (Pre-Flowering stage)
     *  - 75% of cycle: Side Dress 3 (Fruiting/Bulking) — only for crops > 60 days
     *
     * Basal fertilizer (at planting) is noted in the planting event instead.
     *
     * @return array<string, array<int, array>> Events keyed by 'Y-m-d' date string
     */
    public function toFertilizerEvents(): array
    {
        $events = [];
        $totalDays = $this->planting_date->diffInDays($this->expected_harvest_date);

        if ($totalDays <= 0) {
            return $events;
        }

        $stages = [
            ['pct' => 0.25, 'label' => 'Side Dress 1 (Vegetative)', 'desc' => 'Apply first side-dress fertilizer during vegetative growth stage'],
            ['pct' => 0.50, 'label' => 'Side Dress 2 (Pre-Flowering)', 'desc' => 'Apply second side-dress fertilizer before flowering stage'],
        ];

        // Add third application only for longer-cycle crops (> 60 days)
        if ($totalDays > 60) {
            $stages[] = ['pct' => 0.75, 'label' => 'Side Dress 3 (Fruiting)', 'desc' => 'Apply third side-dress fertilizer during fruiting/bulking stage'];
        }

        foreach ($stages as $stage) {
            $fertDate = $this->planting_date->copy()->addDays((int) round($totalDays * $stage['pct']));
            $dateKey = $fertDate->format('Y-m-d');

            if (!isset($events[$dateKey])) {
                $events[$dateKey] = [];
            }

            $events[$dateKey][] = [
                'title' => "Fertilize {$this->crop_name} - {$stage['label']}",
                'type' => 'fertilizer',
                'description' => "{$stage['desc']} for {$this->crop_name} ({$this->area_hectares} ha).",
                'crop_plan_id' => $this->id,
                'area' => $this->area_hectares,
            ];
        }

        return $events;
    }
}
