<?php

namespace App\Console\Commands;

use App\Models\CropPlan;
use App\Models\CropPlanHarvestReport;
use App\Models\CropType;
use App\Models\Farmer;
use App\Models\Municipality;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SeedAlphaDemoData extends Command
{
    use ConfirmableTrait;

    protected $signature = 'pasya:seed-alpha-demo
                            {--municipality=KAPANGAN : Municipality to seed into}
                            {--crop=CABBAGE : Crop name to match in Crop Trends Alpha}
                            {--farm-type=IRRIGATED : Farm type to match in Crop Trends Alpha}
                            {--farmers=10 : Number of tagged demo farmers to create}
                            {--plans=10 : Number of approved demo crop plans to create}
                            {--actual-months=1 : Number of earliest plans with approved actual harvests}
                            {--area=1.5 : Base area in hectares per plan}
                            {--actual-mt=12.5 : Base approved actual harvest in metric tons}
                            {--refresh : Delete existing demo rows before seeding}
                            {--cleanup : Delete tagged demo rows and exit}
                            {--dry-run : Preview the action without writing to the database}
                            {--force : Required to run writes in production}';

    protected $description = 'Seed or clean temporary alpha demo farmers, approved plans, and approved harvests for Crop Trends Alpha QA.';

    private const TAG = 'ALPHA-TEST';

    public function handle(): int
    {
        $municipality = Municipality::normalizeLocationName((string) $this->option('municipality')) ?? 'KAPANGAN';
        $crop = strtoupper(trim((string) $this->option('crop'))) ?: 'CABBAGE';
        $farmType = str_replace(' ', '', strtoupper(trim((string) $this->option('farm-type')))) ?: 'IRRIGATED';
        $farmerCount = $this->boundedInteger('farmers', 10, 1, 500);
        $planCount = $this->boundedInteger('plans', $farmerCount, 1, 37);
        $actualMonths = $this->boundedInteger('actual-months', 1, 0, $planCount);
        $baseArea = $this->positiveFloat('area', 1.5);
        $baseActualMt = $this->positiveFloat('actual-mt', 12.5);

        if ($this->option('cleanup')) {
            return $this->cleanup((bool) $this->option('dry-run'));
        }

        if ((bool) $this->option('dry-run')) {
            $this->previewSeed($municipality, $crop, $farmType, $farmerCount, $planCount, $actualMonths, $baseArea, $baseActualMt);

            return self::SUCCESS;
        }

        if (! $this->confirmToProceed('This will write tagged alpha demo data to the configured database.')) {
            return self::FAILURE;
        }

        if ($this->option('refresh')) {
            $this->cleanup(false, quiet: true);
        }

        DB::transaction(function () use ($municipality, $crop, $farmType, $farmerCount, $planCount, $actualMonths, $baseArea, $baseActualMt) {
            $cropType = $this->cropTypeFor($crop);
            $validatorId = $this->validatorIdFor($municipality);
            $farmers = $this->seedFarmers($municipality, $farmerCount);
            $plans = $this->seedPlans($farmers, $cropType, $municipality, $crop, $farmType, $planCount, $baseArea, $validatorId);
            $harvestReports = $this->seedHarvestReports($plans, $actualMonths, $baseActualMt, $validatorId);

            $this->newLine();
            $this->info('Alpha demo data ready.');
            $this->table(
                ['Scope', 'Value'],
                [
                    ['Municipality', $municipality],
                    ['Crop', $crop],
                    ['Farm Type', $farmType],
                    ['Demo Farmers', (string) $farmers->count()],
                    ['Approved Crop Plans', (string) $plans->count()],
                    ['Approved Harvest Reports', (string) $harvestReports->count()],
                ]
            );
        });

        $this->line('Open Crop Trends Alpha with the same filters to compare approved actual harvests with ML predictions.');
        $this->line('Cleanup command: php artisan pasya:seed-alpha-demo --cleanup --force');

        return self::SUCCESS;
    }

    private function cleanup(bool $dryRun, bool $quiet = false): int
    {
        $planIds = CropPlan::query()
            ->where('notes', 'like', self::TAG . ':%')
            ->pluck('id');

        $reportCount = CropPlanHarvestReport::query()
            ->where(function ($query) use ($planIds) {
                $query->where('harvest_notes', 'like', self::TAG . ':%')
                    ->when($planIds->isNotEmpty(), fn ($query) => $query->orWhereIn('crop_plan_id', $planIds));
            })
            ->count();
        $planCount = $planIds->count();
        $farmerCount = Farmer::withTrashed()
            ->where('farmer_id', 'like', self::TAG . '-%')
            ->count();

        if ($dryRun) {
            $this->table(
                ['Tagged Rows', 'Would Delete'],
                [
                    ['Harvest reports', (string) $reportCount],
                    ['Crop plans', (string) $planCount],
                    ['Farmers', (string) $farmerCount],
                ]
            );

            return self::SUCCESS;
        }

        if (! $this->confirmToProceed('This will delete tagged alpha demo data from the configured database.')) {
            return self::FAILURE;
        }

        DB::transaction(function () use ($planIds) {
            if ($planIds->isNotEmpty()) {
                CropPlan::query()
                    ->whereIn('id', $planIds)
                    ->update([
                        'actual_harvest_report_id' => null,
                        'actual_harvest_production_mt' => null,
                        'actual_harvest_reported_at' => null,
                    ]);
            }

            CropPlanHarvestReport::query()
                ->where(function ($query) use ($planIds) {
                    $query->where('harvest_notes', 'like', self::TAG . ':%')
                        ->when($planIds->isNotEmpty(), fn ($query) => $query->orWhereIn('crop_plan_id', $planIds));
                })
                ->delete();

            if ($planIds->isNotEmpty()) {
                CropPlan::query()
                    ->whereIn('id', $planIds)
                    ->delete();
            }

            Farmer::withTrashed()
                ->where('farmer_id', 'like', self::TAG . '-%')
                ->forceDelete();
        });

        if (! $quiet) {
            $this->info('Tagged alpha demo data cleaned up.');
        }

        return self::SUCCESS;
    }

    private function previewSeed(string $municipality, string $crop, string $farmType, int $farmerCount, int $planCount, int $actualMonths, float $baseArea, float $baseActualMt): void
    {
        $this->table(
            ['Seed Preview', 'Value'],
            [
                ['Municipality', $municipality],
                ['Crop', $crop],
                ['Farm Type', $farmType],
                ['Tagged Farmers', (string) $farmerCount],
                ['Approved Crop Plans', (string) $planCount],
                ['Approved Actual Harvest Reports', (string) $actualMonths],
                ['Base Area', number_format($baseArea, 4) . ' ha'],
                ['Base Actual Harvest', number_format($baseActualMt, 4) . ' MT'],
            ]
        );
    }

    private function cropTypeFor(string $crop): CropType
    {
        $cropType = CropType::withTrashed()
            ->whereRaw('UPPER(name) = ?', [$crop])
            ->first();

        if (! $cropType) {
            return CropType::create([
                'name' => Str::headline(strtolower($crop)),
                'category' => 'Vegetable',
                'description' => self::TAG . ': temporary crop type created for alpha QA.',
                'days_to_harvest' => CropType::DEFAULT_HARVEST_DAYS[$crop] ?? CropType::DEFAULT_HARVEST_DAYS['DEFAULT'],
                'average_yield_per_hectare' => CropType::DEFAULT_YIELD_PER_HECTARE[$crop] ?? CropType::DEFAULT_YIELD_PER_HECTARE['DEFAULT'],
                'is_active' => true,
            ]);
        }

        if (method_exists($cropType, 'trashed') && $cropType->trashed()) {
            $cropType->restore();
        }

        if (! $cropType->is_active) {
            $cropType->forceFill(['is_active' => true])->save();
        }

        return $cropType;
    }

    private function validatorIdFor(string $municipality): ?int
    {
        $validator = User::query()
            ->where('role', User::ROLE_LGU_VALIDATOR)
            ->where('is_active', true)
            ->get()
            ->first(fn (User $user) => Municipality::normalizeLocationName($user->municipality) === $municipality);

        if ($validator) {
            return $validator->id;
        }

        return User::query()
            ->where('role', User::ROLE_DA_ADMIN)
            ->where('is_active', true)
            ->value('id');
    }

    private function seedFarmers(string $municipality, int $count)
    {
        return collect(range(1, $count))->map(function (int $index) use ($municipality) {
            $sequence = str_pad((string) $index, 3, '0', STR_PAD_LEFT);

            $farmer = Farmer::withTrashed()->updateOrCreate(
                ['farmer_id' => self::TAG . '-' . $sequence],
                [
                    'first_name' => 'Alpha',
                    'middle_name' => null,
                    'last_name' => 'Farmer ' . $sequence,
                    'suffix' => null,
                    'municipality' => $municipality,
                    'cooperative' => self::TAG,
                    'contact_info' => 'Temporary Crop Trends Alpha QA account',
                    'email' => 'alpha-test-' . $sequence . '@pasya.test',
                    'mobile_number' => '0999000' . str_pad((string) $index, 4, '0', STR_PAD_LEFT),
                    'password' => Str::password(16),
                    'created_by' => null,
                ]
            );

            if ($farmer->trashed()) {
                $farmer->restore();
            }

            return $farmer;
        });
    }

    private function seedPlans($farmers, CropType $cropType, string $municipality, string $crop, string $farmType, int $planCount, float $baseArea, ?int $validatorId)
    {
        $start = now()->startOfMonth();

        return collect(range(1, $planCount))->map(function (int $index) use ($farmers, $cropType, $municipality, $crop, $farmType, $baseArea, $validatorId, $start) {
            $farmer = $farmers->get(($index - 1) % $farmers->count());
            $expectedHarvestDate = $start->copy()->addMonthsNoOverflow($index - 1)->day(15);
            $plantingMaterialType = 'SEED';
            $plantingDate = $expectedHarvestDate->copy()->subDays($cropType->getDaysToHarvestForMaterial($plantingMaterialType));
            $area = round($baseArea + ((($index - 1) % 4) * 0.25), 4);
            $planKey = self::TAG . ':PLAN:' . str_pad((string) $index, 3, '0', STR_PAD_LEFT);

            return CropPlan::query()->updateOrCreate(
                ['notes' => $planKey],
                [
                    'farmer_id' => $farmer->id,
                    'crop_type_id' => $cropType->id,
                    'crop_name' => $crop,
                    'planting_date' => $plantingDate->toDateString(),
                    'expected_harvest_date' => $expectedHarvestDate->toDateString(),
                    'area_hectares' => $area,
                    'damaged_area_hectares' => 0,
                    'predicted_production' => $cropType->calculatePredictedProduction($area),
                    'municipality' => $municipality,
                    'farm_type' => $farmType,
                    'planting_material_type' => $plantingMaterialType,
                    'status' => 'planted',
                    'lgu_validation_status' => CropPlan::VALIDATION_APPROVED,
                    'lgu_validated_by' => $validatorId,
                    'lgu_validated_at' => now(),
                    'lgu_validation_notes' => self::TAG . ': approved demo crop plan for Crop Trends Alpha QA.',
                    'lgu_validation_revision' => 0,
                    'submitted_to_da_at' => now(),
                ]
            );
        });
    }

    private function seedHarvestReports($plans, int $actualMonths, float $baseActualMt, ?int $validatorId)
    {
        return $plans->take($actualMonths)->values()->map(function (CropPlan $plan, int $index) use ($baseActualMt, $validatorId) {
            $actualMt = round($baseActualMt + ($index * 0.75), 4);
            $reportKey = self::TAG . ':HARVEST:' . str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT);

            $report = CropPlanHarvestReport::query()->updateOrCreate(
                [
                    'crop_plan_id' => $plan->id,
                    'harvest_notes' => $reportKey,
                ],
                [
                    'farmer_id' => $plan->farmer_id,
                    'actual_harvest_date' => Carbon::parse($plan->expected_harvest_date)->toDateString(),
                    'actual_production_mt' => $actualMt,
                    'lgu_validation_status' => CropPlanHarvestReport::VALIDATION_APPROVED,
                    'lgu_validated_by' => $validatorId,
                    'lgu_validated_at' => now(),
                    'lgu_validation_notes' => self::TAG . ': approved demo harvest for Crop Trends Alpha QA.',
                    'lgu_validation_revision' => 0,
                    'submitted_to_da_at' => now(),
                    'applied_at' => now(),
                ]
            );

            $plan->forceFill([
                'status' => 'harvested',
                'actual_harvest_date' => $report->actual_harvest_date,
                'actual_harvest_production_mt' => $report->actual_production_mt,
                'actual_harvest_report_id' => $report->id,
                'actual_harvest_reported_at' => now(),
            ])->save();

            return $report;
        });
    }

    private function boundedInteger(string $option, int $default, int $min, int $max): int
    {
        $value = filter_var($this->option($option), FILTER_VALIDATE_INT);
        $value = $value === false ? $default : $value;

        return min($max, max($min, $value));
    }

    private function positiveFloat(string $option, float $default): float
    {
        $value = filter_var($this->option($option), FILTER_VALIDATE_FLOAT);
        $value = $value === false ? $default : (float) $value;

        return max(0.0001, $value);
    }
}
