<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class CropPlanHarvestReport extends Model
{
    use HasFactory;

    public const VALIDATION_PENDING = 'pending';
    public const VALIDATION_APPROVED = 'approved';
    public const VALIDATION_REJECTED = 'rejected';

    protected $fillable = [
        'crop_plan_id',
        'farmer_id',
        'actual_harvest_date',
        'actual_production_mt',
        'harvest_notes',
        'lgu_validation_status',
        'lgu_validated_by',
        'lgu_validated_at',
        'lgu_validation_notes',
        'lgu_validation_revision',
        'submitted_to_da_at',
        'applied_at',
    ];

    protected $casts = [
        'actual_harvest_date' => 'date',
        'actual_production_mt' => 'decimal:4',
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

    public function getLguValidationStatusLabelAttribute(): string
    {
        return CropPlan::VALIDATION_STATUS_LABELS[$this->lgu_validation_status]
            ?? Str::headline(str_replace('_', ' ', (string) $this->lgu_validation_status));
    }

    public function getActualProductionKgAttribute(): float
    {
        return round((float) $this->actual_production_mt * 1000, 2);
    }

    public function getVarianceMtAttribute(): ?float
    {
        $cropPlan = $this->cropPlan;

        if (! $cropPlan || $cropPlan->predicted_production === null) {
            return null;
        }

        return round((float) $this->actual_production_mt - (float) $cropPlan->adjusted_predicted_production, 4);
    }

    public function toFarmerPayload(): array
    {
        return [
            'id' => $this->id,
            'crop_plan_id' => $this->crop_plan_id,
            'actual_harvest_date' => $this->actual_harvest_date?->format('Y-m-d'),
            'actual_harvest_date_formatted' => $this->actual_harvest_date?->format('M d, Y'),
            'actual_production_mt' => (float) $this->actual_production_mt,
            'actual_production_kg' => $this->actual_production_kg,
            'harvest_notes' => $this->harvest_notes,
            'lgu_validation_status' => $this->lgu_validation_status,
            'lgu_validation_status_label' => $this->lgu_validation_status_label,
            'lgu_validation_notes' => $this->lgu_validation_notes,
            'lgu_validation_revision' => (int) ($this->lgu_validation_revision ?? 0),
            'variance_mt' => $this->variance_mt,
        ];
    }
}
