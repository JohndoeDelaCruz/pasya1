<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Crop;
use App\Models\CropPlan;
use App\Models\Farmer;
use App\Models\Municipality;
use App\Services\PredictionService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CropTrendsAlphaController extends Controller
{
    public function __construct(private PredictionService $predictionService)
    {
    }

    public function index(Request $request)
    {
        if (function_exists('set_time_limit')) {
            @set_time_limit(180);
        }

        $validated = $request->validate([
            'municipality' => ['nullable', 'string', 'max:255'],
            'crop' => ['nullable', 'string', 'max:255'],
            'farm_type' => ['nullable', 'string', 'max:255'],
        ]);

        $defaults = $this->defaultFilters();
        $selectedMunicipality = Municipality::normalizeLocationName($validated['municipality'] ?? $defaults['municipality']);
        $selectedCrop = strtoupper(trim((string) ($validated['crop'] ?? $defaults['crop'])));
        $selectedFarmType = $this->normalizeFarmType((string) ($validated['farm_type'] ?? $defaults['farm_type']));
        $locationNames = Municipality::locationNamesForMunicipality($selectedMunicipality);

        $start = now()->startOfMonth();
        $end = now()->copy()->addYears(3)->startOfMonth();
        $periods = $this->periods($start, $end);

        $coverage = $this->coverage($locationNames, $selectedCrop, $selectedFarmType);
        $actualsByPeriod = $this->actualHarvestsByPeriod($periods, $locationNames, $selectedCrop, $selectedFarmType);
        $predictionAreas = $this->predictionAreasByPeriod($periods, $locationNames, $selectedMunicipality, $selectedCrop, $selectedFarmType);
        $predictionsByPeriod = $coverage['can_predict']
            ? $this->predictionsByPeriod($periods, $selectedMunicipality, $selectedCrop, $selectedFarmType, $predictionAreas)
            : collect();

        $rows = $periods->map(function (Carbon $period) use ($actualsByPeriod, $predictionsByPeriod, $predictionAreas) {
            $key = $period->format('Y-m');
            $actual = $actualsByPeriod->get($key);
            $prediction = $predictionsByPeriod->get($key);

            return [
                'key' => $key,
                'label' => $period->format('M Y'),
                'month' => strtoupper($period->format('M')),
                'year' => (int) $period->format('Y'),
                'actual_harvest_mt' => $actual !== null ? round((float) $actual, 4) : null,
                'predicted_harvest_mt' => $prediction['production_mt'] ?? null,
                'prediction_area_ha' => round((float) ($predictionAreas->get($key) ?? 0), 4),
                'confidence_score' => $prediction['confidence_score'] ?? null,
                'source' => $prediction['source'] ?? null,
            ];
        })->values();

        $municipalities = Crop::query()
            ->distinct()
            ->orderBy('municipality')
            ->pluck('municipality')
            ->map(fn ($municipality) => Municipality::normalizeLocationName($municipality))
            ->filter()
            ->unique()
            ->sort()
            ->values();
        $crops = Crop::query()->distinct()->orderBy('crop')->pluck('crop');
        $farmTypes = Crop::query()->distinct()->orderBy('farm_type')->pluck('farm_type');

        return view('admin.crop-trends-alpha', [
            'rows' => $rows,
            'labels' => $rows->pluck('label')->all(),
            'actualData' => $rows->pluck('actual_harvest_mt')->all(),
            'predictedData' => $rows->pluck('predicted_harvest_mt')->all(),
            'coverage' => $coverage,
            'selectedMunicipality' => $selectedMunicipality,
            'selectedCrop' => $selectedCrop,
            'selectedFarmType' => $selectedFarmType,
            'municipalities' => $municipalities,
            'crops' => $crops,
            'farmTypes' => $farmTypes,
            'start' => $start,
            'end' => $end,
            'mlApiHealthy' => $coverage['can_predict'] ? $this->predictionService->checkHealth() : null,
        ]);
    }

    private function defaultFilters(): array
    {
        return [
            'municipality' => Crop::select('municipality', DB::raw('SUM(production) as total_production'))
                ->groupBy('municipality')
                ->orderByDesc('total_production')
                ->value('municipality') ?? 'BUGUIAS',
            'crop' => Crop::select('crop', DB::raw('SUM(production) as total_production'))
                ->groupBy('crop')
                ->orderByDesc('total_production')
                ->value('crop') ?? 'CABBAGE',
            'farm_type' => Crop::select('farm_type', DB::raw('SUM(production) as total_production'))
                ->groupBy('farm_type')
                ->orderByDesc('total_production')
                ->value('farm_type') ?? 'IRRIGATED',
        ];
    }

    private function periods(Carbon $start, Carbon $end): Collection
    {
        $periods = collect();
        $cursor = $start->copy();

        while ($cursor->lessThanOrEqualTo($end)) {
            $periods->push($cursor->copy());
            $cursor->addMonthNoOverflow();
        }

        return $periods;
    }

    private function coverage(array $locationNames, string $crop, string $farmType): array
    {
        $farmerQuery = Farmer::query();
        if ($locationNames !== []) {
            $this->applyLocationScope($farmerQuery, 'municipality', $locationNames);
        }

        $registeredFarmers = (clone $farmerQuery)->count();

        $participatingFarmers = CropPlan::query()
            ->join('farmers', 'farmers.id', '=', 'crop_plans.farmer_id')
            ->whereNull('farmers.deleted_at')
            ->where('crop_plans.lgu_validation_status', CropPlan::VALIDATION_APPROVED)
            ->when($locationNames !== [], fn ($query) => $this->applyLocationScope($query, 'crop_plans.municipality', $locationNames))
            ->whereRaw('UPPER(crop_plans.crop_name) = ?', [$crop])
            ->whereRaw('UPPER(crop_plans.farm_type) = ?', [$farmType])
            ->distinct('crop_plans.farmer_id')
            ->count('crop_plans.farmer_id');

        $percentage = $registeredFarmers > 0
            ? round(($participatingFarmers / $registeredFarmers) * 100, 2)
            : 0.0;

        return [
            'registered_farmers' => $registeredFarmers,
            'participating_farmers' => $participatingFarmers,
            'percentage' => $percentage,
            'threshold' => 10,
            'can_predict' => $registeredFarmers > 0 && $percentage >= 10,
        ];
    }

    private function actualHarvestsByPeriod(Collection $periods, array $locationNames, string $crop, string $farmType): Collection
    {
        $start = $periods->first()->copy()->startOfMonth();
        $end = $periods->last()->copy()->endOfMonth();

        return CropPlan::query()
            ->where('lgu_validation_status', CropPlan::VALIDATION_APPROVED)
            ->whereNotNull('actual_harvest_production_mt')
            ->whereBetween('actual_harvest_date', [$start, $end])
            ->when($locationNames !== [], fn ($query) => $this->applyLocationScope($query, 'municipality', $locationNames))
            ->whereRaw('UPPER(crop_name) = ?', [$crop])
            ->whereRaw('UPPER(farm_type) = ?', [$farmType])
            ->selectRaw($this->periodKeyExpression('actual_harvest_date') . ' as period_key')
            ->selectRaw('SUM(actual_harvest_production_mt) as total_actual_harvest')
            ->groupBy('period_key')
            ->pluck('total_actual_harvest', 'period_key');
    }

    private function predictionAreasByPeriod(Collection $periods, array $locationNames, string $municipality, string $crop, string $farmType): Collection
    {
        $start = $periods->first()->copy()->startOfMonth();
        $end = $periods->last()->copy()->endOfMonth();

        $approvedPlanAreas = CropPlan::query()
            ->where('lgu_validation_status', CropPlan::VALIDATION_APPROVED)
            ->whereBetween('expected_harvest_date', [$start, $end])
            ->when($locationNames !== [], fn ($query) => $this->applyLocationScope($query, 'municipality', $locationNames))
            ->whereRaw('UPPER(crop_name) = ?', [$crop])
            ->whereRaw('UPPER(farm_type) = ?', [$farmType])
            ->selectRaw($this->periodKeyExpression('expected_harvest_date') . ' as period_key')
            ->selectRaw('SUM(' . $this->nonNegativeAreaExpression() . ') as total_area')
            ->groupBy('period_key')
            ->pluck('total_area', 'period_key');

        $seasonalAreas = Crop::query()
            ->when($locationNames !== [], fn ($query) => $this->applyLocationScope($query, 'municipality', $locationNames))
            ->whereRaw('UPPER(crop) = ?', [$crop])
            ->whereRaw('UPPER(farm_type) = ?', [$farmType])
            ->selectRaw('month, AVG(area_harvested) as avg_area')
            ->groupBy('month')
            ->get()
            ->mapWithKeys(fn ($row) => [$this->normalizeMonth((string) $row->month) => (float) $row->avg_area]);

        $defaultArea = Crop::query()
            ->when($locationNames !== [], fn ($query) => $this->applyLocationScope($query, 'municipality', $locationNames))
            ->whereRaw('UPPER(crop) = ?', [$crop])
            ->whereRaw('UPPER(farm_type) = ?', [$farmType])
            ->avg('area_harvested') ?: 1;

        return $periods->mapWithKeys(function (Carbon $period) use ($approvedPlanAreas, $seasonalAreas, $defaultArea) {
            $periodKey = $period->format('Y-m');
            $monthKey = strtoupper($period->format('M'));
            $area = (float) ($approvedPlanAreas->get($periodKey) ?: $seasonalAreas->get($monthKey) ?: $defaultArea);

            return [$periodKey => max(0.0001, $area)];
        });
    }

    private function predictionsByPeriod(Collection $periods, string $municipality, string $crop, string $farmType, Collection $areas): Collection
    {
        return $periods->mapWithKeys(function (Carbon $period) use ($municipality, $crop, $farmType, $areas) {
            $periodKey = $period->format('Y-m');
            $prediction = $this->predictionService->predictProduction([
                'municipality' => $municipality,
                'farm_type' => $farmType,
                'month' => strtoupper($period->format('M')),
                'crop' => $crop,
                'area_harvested' => (float) $areas->get($periodKey),
                'year' => (int) $period->format('Y'),
            ]);

            if (($prediction['success'] ?? false) !== true) {
                return [$periodKey => [
                    'production_mt' => null,
                    'confidence_score' => null,
                    'source' => 'unavailable',
                ]];
            }

            $payload = $prediction['prediction'] ?? $prediction;

            return [$periodKey => [
                'production_mt' => isset($payload['production_mt']) ? round((float) $payload['production_mt'], 4) : null,
                'confidence_score' => isset($payload['confidence_score']) ? round((float) $payload['confidence_score'], 2) : null,
                'source' => 'ml',
            ]];
        });
    }

    private function normalizeFarmType(string $farmType): string
    {
        return str_replace(' ', '', strtoupper(trim($farmType)));
    }

    private function normalizeMonth(string $month): string
    {
        $normalized = strtoupper(trim($month));

        return match ($normalized) {
            'JANUARY' => 'JAN',
            'FEBRUARY' => 'FEB',
            'MARCH' => 'MAR',
            'APRIL' => 'APR',
            'JUNE' => 'JUN',
            'JULY' => 'JUL',
            'AUGUST' => 'AUG',
            'SEPTEMBER', 'SEPT' => 'SEP',
            'OCTOBER' => 'OCT',
            'NOVEMBER' => 'NOV',
            'DECEMBER' => 'DEC',
            default => substr($normalized, 0, 3),
        };
    }

    private function applyLocationScope($query, string $column, array $locationNames)
    {
        return $query->whereIn(DB::raw("UPPER(TRIM({$column}))"), $locationNames);
    }

    private function periodKeyExpression(string $column): string
    {
        return match (DB::connection()->getDriverName()) {
            'pgsql' => "TO_CHAR({$column}, 'YYYY-MM')",
            'sqlite' => "strftime('%Y-%m', {$column})",
            'mysql', 'mariadb' => "DATE_FORMAT({$column}, '%Y-%m')",
            default => "TO_CHAR({$column}, 'YYYY-MM')",
        };
    }

    private function nonNegativeAreaExpression(): string
    {
        return match (DB::connection()->getDriverName()) {
            'sqlite' => 'MAX(area_hectares - COALESCE(damaged_area_hectares, 0), 0)',
            default => 'GREATEST(area_hectares - COALESCE(damaged_area_hectares, 0), 0)',
        };
    }
}
