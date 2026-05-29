<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class CropPlanDamageReport extends Model
{
    use HasFactory;

    public const VALIDATION_PENDING = 'pending';
    public const VALIDATION_APPROVED = 'approved';
    public const VALIDATION_REJECTED = 'rejected';

    protected $fillable = [
        'crop_plan_id',
        'farmer_id',
        'damaged_area_hectares',
        'damage_cause',
        'damage_occurred_on',
        'damage_notes',
        'lgu_validation_status',
        'lgu_validated_by',
        'lgu_validated_at',
        'lgu_validation_notes',
        'lgu_validation_revision',
        'submitted_to_da_at',
        'applied_at',
    ];

    protected $casts = [
        'damaged_area_hectares' => 'decimal:2',
        'damage_occurred_on' => 'date',
        'lgu_validated_at' => 'datetime',
        'submitted_to_da_at' => 'datetime',
        'applied_at' => 'datetime',
    ];

    public function cropPlan(): BelongsTo
    {
        return $this->belongsTo(CropPlan::class);
    }

    public function farmer(): BelongsTo
    {
        return $this->belongsTo(Farmer::class);
    }

    public function lguValidator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'lgu_validated_by');
    }

    public function getDamageCauseLabelAttribute(): string
    {
        return CropPlan::DAMAGE_CAUSE_LABELS[$this->damage_cause]
            ?? Str::headline(str_replace('_', ' ', (string) $this->damage_cause));
    }

    public function getLguValidationStatusLabelAttribute(): string
    {
        return CropPlan::VALIDATION_STATUS_LABELS[$this->lgu_validation_status]
            ?? Str::headline(str_replace('_', ' ', (string) $this->lgu_validation_status));
    }

    public function getEstimatedProductionLossMtAttribute(): float
    {
        $cropPlan = $this->cropPlan;
        $totalArea = (float) ($cropPlan?->area_hectares ?? 0);

        if ($totalArea <= 0 || $cropPlan?->predicted_production === null) {
            return 0.0;
        }

        $damagedArea = min((float) $this->damaged_area_hectares, $totalArea);

        return round((float) $cropPlan->predicted_production * ($damagedArea / $totalArea), 2);
    }

    public function toCalendarPayload(): array
    {
        return [
            'id' => $this->id,
            'crop_plan_id' => $this->crop_plan_id,
            'damaged_area_hectares' => (float) $this->damaged_area_hectares,
            'damage_cause' => $this->damage_cause,
            'damage_cause_label' => $this->damage_cause_label,
            'damage_notes' => $this->damage_notes,
            'damage_occurred_on' => $this->damage_occurred_on?->format('Y-m-d'),
            'damage_occurred_on_formatted' => $this->damage_occurred_on?->format('M d, Y'),
            'lgu_validation_status' => $this->lgu_validation_status,
            'lgu_validation_status_label' => $this->lgu_validation_status_label,
            'lgu_validation_notes' => $this->lgu_validation_notes,
            'lgu_validation_revision' => (int) ($this->lgu_validation_revision ?? 0),
            'estimated_production_loss_mt' => $this->estimated_production_loss_mt,
        ];
    }

    public function toCalendarEvent(): array
    {
        $cropPlan = $this->cropPlan;
        $cropName = $cropPlan?->crop_name ?? 'crop plan';
        $payload = $this->toCalendarPayload();

        return [
            'id' => 'damage-report-' . $this->id,
            'title' => $this->lgu_validation_status === self::VALIDATION_REJECTED
                ? "Revise damage report for {$cropName}"
                : "Damage report pending for {$cropName}",
            'type' => 'damage',
            'description' => "Damage report for {$cropName}: {$this->damaged_area_hectares} ha affected by {$this->damage_cause_label}. Status: {$this->lgu_validation_status_label}.",
            'is_damage_validation_event' => true,
            'crop_name' => $cropName,
            'area' => (float) ($cropPlan?->area_hectares ?? 0),
            'planting_date' => $cropPlan?->planting_date?->format('Y-m-d'),
            'original_predicted_production' => (float) ($cropPlan?->predicted_production ?? 0),
            'can_report_damage' => $cropPlan ? ! in_array($cropPlan->status, ['harvested', 'cancelled'], true) : false,
            'latest_damage_report' => $payload,
        ] + $payload;
    }
}
