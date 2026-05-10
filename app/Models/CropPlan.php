<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;
use Illuminate\Support\Str;

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
 * @property string|null $planting_material_type
 * @property string $status
 * @property string|null $notes
 * @property string $formatted_production
 * @property string|null $planting_material_label
 * @property int $days_until_harvest
 * @property float $progress_percentage
 */
class CropPlan extends Model
{
    use HasFactory;

    public const DAMAGE_CAUSE_LABELS = [
        'typhoon' => 'Typhoon',
        'flood' => 'Flood',
        'landslide' => 'Landslide',
        'drought' => 'Drought',
        'earthquake' => 'Earthquake',
        'volcanic_ashfall' => 'Volcanic Ashfall',
        'storm_surge' => 'Storm Surge',
        'other' => 'Other Natural Disaster',
    ];

    protected $fillable = [
        'farmer_id',
        'crop_type_id',
        'crop_name',
        'planting_date',
        'expected_harvest_date',
        'area_hectares',
        'damaged_area_hectares',
        'predicted_production',
        'municipality',
        'farm_type',
        'planting_material_type',
        'status',
        'damage_cause',
        'damage_notes',
        'damage_reported_at',
        'notes',
        'actual_harvest_date',
    ];

    protected $casts = [
        'planting_date' => 'date',
        'expected_harvest_date' => 'date',
        'actual_harvest_date' => 'date',
        'area_hectares' => 'decimal:2',
        'damaged_area_hectares' => 'decimal:2',
        'predicted_production' => 'decimal:2',
        'damage_reported_at' => 'datetime',
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
        if ($this->predicted_production === null) {
            return 'N/A';
        }

        return number_format($this->adjusted_predicted_production, 2) . ' MT';
    }

    public function getFormattedAdjustedProductionAttribute(): string
    {
        return $this->formatted_production;
    }

    public function getHasDamageReportAttribute(): bool
    {
        return $this->damage_reported_at !== null && (float) ($this->damaged_area_hectares ?? 0) > 0;
    }

    public function getAdjustedAreaHectaresAttribute(): float
    {
        $totalArea = (float) $this->area_hectares;
        $damagedArea = min((float) ($this->damaged_area_hectares ?? 0), $totalArea);

        return max(0.0, round($totalArea - $damagedArea, 2));
    }

    public function getAdjustedPredictedProductionAttribute(): float
    {
        if ($this->predicted_production === null) {
            return 0.0;
        }

        $originalProduction = (float) $this->predicted_production;

        if (!$this->has_damage_report) {
            return round($originalProduction, 2);
        }

        $totalArea = (float) $this->area_hectares;
        if ($totalArea <= 0) {
            return round($originalProduction, 2);
        }

        return round($originalProduction * ($this->adjusted_area_hectares / $totalArea), 2);
    }

    public function getProductionLossMtAttribute(): float
    {
        if ($this->predicted_production === null) {
            return 0.0;
        }

        return max(0.0, round((float) $this->predicted_production - $this->adjusted_predicted_production, 2));
    }

    public function getDisplayStatusAttribute(): string
    {
        if ($this->has_damage_report) {
            return 'damaged';
        }

        return $this->status;
    }

    public function getPlantingReportStatusAttribute(): string
    {
        $status = $this->display_status;

        return $status === 'planned' ? 'planted' : $status;
    }

    public function getDamageCauseLabelAttribute(): ?string
    {
        if (!$this->damage_cause) {
            return null;
        }

        return self::DAMAGE_CAUSE_LABELS[$this->damage_cause]
            ?? Str::headline(str_replace('_', ' ', $this->damage_cause));
    }

    /**
     * Get formatted planting material label.
     */
    public function getPlantingMaterialLabelAttribute(): ?string
    {
        return match ($this->planting_material_type) {
            'SEED' => 'Seed',
            'SEEDLING' => 'Seedling',
            default => null,
        };
    }

    /**
     * Get planting material sentence fragment for event descriptions.
     */
    public function getPlantingMaterialDescriptionAttribute(): string
    {
        return $this->planting_material_label
            ? " Planting material: {$this->planting_material_label}."
            : '';
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
            'description' => "Plant {$this->crop_name} on {$this->area_hectares} hectares.{$this->planting_material_description} Apply basal fertilizer at planting. Expected harvest: {$this->expected_harvest_date->format('M d, Y')}. Projected harvest: {$this->formatted_production}{$this->damageDescriptionSuffix()}",
        ] + $this->eventContext();
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
            'description' => "Expected harvest of {$this->crop_name} from {$this->area_hectares} ha.{$this->planting_material_description} Projected harvest: {$this->formatted_production}{$this->damageDescriptionSuffix()}",
        ] + $this->eventContext();
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
                'crop_name' => $this->crop_name,
                'description' => "{$stage['desc']} for {$this->crop_name} ({$this->area_hectares} ha).{$this->damageDescriptionSuffix()}",
            ] + $this->eventContext();
        }

        return $events;
    }

    private function eventContext(): array
    {
        return [
            'crop_plan_id' => $this->id,
            'crop_name' => $this->crop_name,
            'area' => (float) $this->area_hectares,
            'adjusted_area_hectares' => $this->adjusted_area_hectares,
            'damaged_area_hectares' => (float) ($this->damaged_area_hectares ?? 0),
            'predicted_production' => $this->adjusted_predicted_production,
            'original_predicted_production' => (float) ($this->predicted_production ?? 0),
            'adjusted_predicted_production' => $this->adjusted_predicted_production,
            'production_loss_mt' => $this->production_loss_mt,
            'planting_material_type' => $this->planting_material_type,
            'planting_material_label' => $this->planting_material_label,
            'display_status' => $this->display_status,
            'raw_status' => $this->status,
            'has_damage_report' => $this->has_damage_report,
            'damage_cause' => $this->damage_cause,
            'damage_cause_label' => $this->damage_cause_label,
            'damage_notes' => $this->damage_notes,
            'damage_reported_at' => $this->damage_reported_at?->toIso8601String(),
            'damage_reported_at_formatted' => $this->damage_reported_at?->format('M d, Y h:i A'),
            'can_report_damage' => !in_array($this->status, ['harvested', 'cancelled'], true),
        ];
    }

    private function damageDescriptionSuffix(): string
    {
        if (!$this->has_damage_report) {
            return '';
        }

        $reportedAt = $this->damage_reported_at?->format('M d, Y');
        $summary = " Damage reported: {$this->damaged_area_hectares} ha affected";

        if ($this->damage_cause_label) {
            $summary .= " by {$this->damage_cause_label}";
        }

        if ($reportedAt) {
            $summary .= " on {$reportedAt}";
        }

        $summary .= ". Estimated loss: " . number_format($this->production_loss_mt, 2) . ' MT.';

        return $summary;
    }
}
