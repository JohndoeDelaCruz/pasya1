<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $name
 * @property string $category
 * @property string|null $description
 * @property string|null $image
 * @property int $days_to_harvest
 * @property float $average_yield_per_hectare
 * @property bool $is_active
 * @property string $name_display
 * @property string $category_display
 * @property int $days_to_harvest_value
 * @property float $average_yield_value
 */
class CropType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'category',
        'description',
        'image',
        'days_to_harvest',
        'average_yield_per_hectare',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'days_to_harvest' => 'integer',
        'average_yield_per_hectare' => 'decimal:2',
    ];

    protected $appends = [
        'days_to_harvest_value',
        'average_yield_value',
        'seedling_days_value',
        'supports_seedling_material',
        'available_planting_material_types',
    ];

    /**
     * Default days to harvest for common crops in Benguet
     * Used when database value is not set
     */
    public const DEFAULT_HARVEST_DAYS = [
        'CABBAGE' => 90,
        'CHINESE CABBAGE' => 60,
        'CARROTS' => 75,
        'POTATOES' => 100,
        'BROCCOLI' => 80,
        'CAULIFLOWER' => 85,
        'LETTUCE' => 45,
        'CELERY' => 100,
        'TOMATOES' => 75,
        'BEANS' => 60,
        'STRING BEANS' => 55,
        'SNAP BEANS' => 55,
        'PEAS' => 65,
        'SWEET PEAS' => 70,
        'ONION' => 90,
        'GARLIC' => 120,
        'BELL PEPPER' => 70,
        'RADISH' => 30,
        'SAYOTE' => 90,
        'STRAWBERRY' => 90,
        'DEFAULT' => 75,
    ];

    /**
     * Default average yield (MT/hectare) for common crops
     */
    public const DEFAULT_YIELD_PER_HECTARE = [
        'CABBAGE' => 25.0,
        'CHINESE CABBAGE' => 20.0,
        'CARROTS' => 18.0,
        'POTATOES' => 15.0,
        'BROCCOLI' => 12.0,
        'CAULIFLOWER' => 12.0,
        'LETTUCE' => 15.0,
        'CELERY' => 20.0,
        'TOMATOES' => 30.0,
        'BEANS' => 8.0,
        'STRING BEANS' => 10.0,
        'SNAP BEANS' => 10.0,
        'PEAS' => 6.0,
        'SWEET PEAS' => 8.0,
        'ONION' => 15.0,
        'GARLIC' => 8.0,
        'BELL PEPPER' => 15.0,
        'RADISH' => 12.0,
        'SAYOTE' => 35.0,
        'STRAWBERRY' => 15.0,
        'DEFAULT' => 12.0,
    ];

    /**
     * Typical nursery/transplant age in days for crops commonly started as seedlings.
     *
     * These values are used only to distinguish seed vs seedling planning. For crops
     * typically started from transplants, the stored maturity days are treated as
     * transplant-to-harvest. Selecting SEED adds the nursery period; selecting
     * SEEDLING uses the base maturity directly.
     */
    public const DEFAULT_SEEDLING_STAGE_DAYS = [
        'BROCCOLI' => 35,
        'CABBAGE' => 35,
        'CHINESECABBAGE' => 28,
        'CAULIFLOWER' => 35,
        'LETTUCE' => 24,
        'CELERY' => 63,
        'TOMATOES' => 39,
        'TOMATO' => 39,
        'BELLPEPPER' => 56,
        'SWEETPEPPER' => 56,
        'ONION' => 35,
    ];

    /**
     * Scope to get only active crop types
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get the crop name in Title Case format
     */
    public function getNameDisplayAttribute(): string
    {
        return ucwords(strtolower($this->name ?? ''));
    }

    /**
     * Get the category in Title Case format
     */
    public function getCategoryDisplayAttribute(): string
    {
        return ucwords(strtolower($this->category ?? ''));
    }

    /**
     * Get crop plans for this crop type
     */
    public function cropPlans(): HasMany
    {
        return $this->hasMany(\App\Models\CropPlan::class);
    }

    /**
     * Get the days to harvest, with fallback to defaults
     */
    public function getDaysToHarvestValueAttribute(): int
    {
        if ($this->days_to_harvest) {
            return $this->days_to_harvest;
        }

        $cropName = strtoupper($this->name);
        return self::DEFAULT_HARVEST_DAYS[$cropName] ?? self::DEFAULT_HARVEST_DAYS['DEFAULT'];
    }

    /**
     * Get the average yield per hectare, with fallback to defaults
     */
    public function getAverageYieldValueAttribute(): float
    {
        if ($this->average_yield_per_hectare) {
            return (float) $this->average_yield_per_hectare;
        }

        $cropName = strtoupper($this->name);
        return self::DEFAULT_YIELD_PER_HECTARE[$cropName] ?? self::DEFAULT_YIELD_PER_HECTARE['DEFAULT'];
    }

    /**
     * Get the typical seedling-stage duration before transplanting.
     */
    public function getSeedlingDaysValueAttribute(): int
    {
        $cropKey = self::normalizeCropKey($this->name ?? '');

        return self::DEFAULT_SEEDLING_STAGE_DAYS[$cropKey] ?? 0;
    }

    public function getSupportsSeedlingMaterialAttribute(): bool
    {
        return $this->seedling_days_value > 0;
    }

    public function getAvailablePlantingMaterialTypesAttribute(): array
    {
        return $this->supports_seedling_material
            ? ['SEED', 'SEEDLING']
            : ['SEED'];
    }

    public function supportsPlantingMaterialType(?string $plantingMaterialType = null): bool
    {
        $materialType = strtoupper((string) $plantingMaterialType);

        if ($materialType === '' || $materialType === 'SEED') {
            return true;
        }

        if ($materialType === 'SEEDLING') {
            return $this->supports_seedling_material;
        }

        return false;
    }

    /**
     * Get harvest days adjusted for the selected planting material type.
     */
    public function getDaysToHarvestForMaterial(?string $plantingMaterialType = null): int
    {
        $daysToHarvest = $this->days_to_harvest_value;

        if (strtoupper((string) $plantingMaterialType) === 'SEED') {
            return $daysToHarvest + $this->seedling_days_value;
        }

        return $daysToHarvest;
    }

    /**
     * Calculate expected harvest date from planting date
     */
    public function calculateHarvestDate(\DateTime|string $plantingDate, ?string $plantingMaterialType = null): \Carbon\Carbon
    {
        $date = $plantingDate instanceof \DateTime
            ? \Carbon\Carbon::instance($plantingDate)
            : \Carbon\Carbon::parse($plantingDate);

        return $date->copy()->addDays($this->getDaysToHarvestForMaterial($plantingMaterialType));
    }

    /**
     * Calculate predicted production based on area
     */
    public function calculatePredictedProduction(float $areaHectares): float
    {
        return round($areaHectares * $this->average_yield_value, 2);
    }

    /**
     * Get days to harvest by crop name (static helper)
     */
    public static function getHarvestDays(string $cropName): int
    {
        $cropName = strtoupper(trim($cropName));

        // Try to find in database first
        $cropType = self::where('name', 'LIKE', "%{$cropName}%")->first();
        if ($cropType && $cropType->days_to_harvest) {
            return $cropType->days_to_harvest;
        }

        // Fall back to defaults
        return self::DEFAULT_HARVEST_DAYS[$cropName] ?? self::DEFAULT_HARVEST_DAYS['DEFAULT'];
    }

    /**
     * Get average yield by crop name (static helper)
     */
    public static function getAverageYield(string $cropName): float
    {
        $cropName = strtoupper(trim($cropName));

        // Try to find in database first
        $cropType = self::where('name', 'LIKE', "%{$cropName}%")->first();
        if ($cropType && $cropType->average_yield_per_hectare) {
            return (float) $cropType->average_yield_per_hectare;
        }

        // Fall back to defaults
        return self::DEFAULT_YIELD_PER_HECTARE[$cropName] ?? self::DEFAULT_YIELD_PER_HECTARE['DEFAULT'];
    }

    private static function normalizeCropKey(string $cropName): string
    {
        $normalized = preg_replace('/[^A-Z]/', '', strtoupper(trim($cropName)));

        return $normalized !== '' ? $normalized : 'DEFAULT';
    }
}
