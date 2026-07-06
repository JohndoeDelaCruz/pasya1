<?php

namespace App\Http\Controllers\Lgu;

use App\Http\Controllers\Controller;
use App\Models\CropPlan;
use App\Models\CropPlanDamageReport;
use App\Models\CropPlanHarvestReport;
use App\Models\FarmerNotification;
use App\Models\Municipality;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class LguDashboardController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'status' => ['nullable', Rule::in([
                CropPlan::VALIDATION_PENDING,
                CropPlan::VALIDATION_APPROVED,
                CropPlan::VALIDATION_REJECTED,
                'all',
            ])],
            'type' => ['nullable', Rule::in(['all', 'crop_plans', 'damage_reports', 'harvest_reports'])],
            'search' => ['nullable', 'string', 'max:255'],
        ]);

        $validator = Auth::guard('web')->user();
        $municipality = $validator->normalizedMunicipality();
        $barangay = $validator->normalizedBarangay();
        $locationNames = $this->validatorLocationNames();
        $type = $validated['type'] ?? 'all';
        $status = $validated['status'] ?? (in_array($type, ['damage_reports', 'harvest_reports'], true) ? 'all' : CropPlan::VALIDATION_PENDING);
        $search = trim((string) ($validated['search'] ?? ''));

        $cropPlansQuery = CropPlan::query()
            ->with(['farmer', 'cropType', 'lguValidator'])
            ->whereIn('municipality', $locationNames)
            ->when($status !== 'all', fn ($query) => $query->where('lgu_validation_status', $status))
            ->when($search !== '', fn ($query) => $this->applyCropPlanSearch($query, $search))
            ->latest('created_at');

        $damageReportsQuery = CropPlanDamageReport::query()
            ->with(['farmer', 'cropPlan.cropType', 'lguValidator'])
            ->whereHas('cropPlan', fn ($query) => $query->whereIn('municipality', $locationNames))
            ->when($status !== 'all', fn ($query) => $query->where('lgu_validation_status', $status))
            ->when($search !== '', fn ($query) => $this->applyDamageReportSearch($query, $search))
            ->latest('created_at');

        $harvestReportsQuery = CropPlanHarvestReport::query()
            ->with(['farmer', 'cropPlan.cropType', 'lguValidator'])
            ->whereHas('cropPlan', fn ($query) => $query->whereIn('municipality', $locationNames))
            ->when($status !== 'all', fn ($query) => $query->where('lgu_validation_status', $status))
            ->when($search !== '', fn ($query) => $this->applyHarvestReportSearch($query, $search))
            ->latest('created_at');

        $cropPlans = in_array($type, ['all', 'crop_plans'], true)
            ? $cropPlansQuery->paginate(10, ['*'], 'crop_plan_page')->withQueryString()
            : collect();

        $damageReports = in_array($type, ['all', 'damage_reports'], true)
            ? $damageReportsQuery->paginate(10, ['*'], 'damage_report_page')->withQueryString()
            : collect();

        $harvestReports = in_array($type, ['all', 'harvest_reports'], true)
            ? $harvestReportsQuery->paginate(10, ['*'], 'harvest_report_page')->withQueryString()
            : collect();

        $stats = [
            'crop_plans_pending' => CropPlan::whereIn('municipality', $locationNames)->where('lgu_validation_status', CropPlan::VALIDATION_PENDING)->count(),
            'crop_plans_approved' => CropPlan::whereIn('municipality', $locationNames)->where('lgu_validation_status', CropPlan::VALIDATION_APPROVED)->count(),
            'crop_plans_rejected' => CropPlan::whereIn('municipality', $locationNames)->where('lgu_validation_status', CropPlan::VALIDATION_REJECTED)->count(),
            'damage_pending' => CropPlanDamageReport::whereHas('cropPlan', fn ($query) => $query->whereIn('municipality', $locationNames))->where('lgu_validation_status', CropPlanDamageReport::VALIDATION_PENDING)->count(),
            'damage_approved' => CropPlanDamageReport::whereHas('cropPlan', fn ($query) => $query->whereIn('municipality', $locationNames))->where('lgu_validation_status', CropPlanDamageReport::VALIDATION_APPROVED)->count(),
            'damage_rejected' => CropPlanDamageReport::whereHas('cropPlan', fn ($query) => $query->whereIn('municipality', $locationNames))->where('lgu_validation_status', CropPlanDamageReport::VALIDATION_REJECTED)->count(),
            'harvest_pending' => CropPlanHarvestReport::whereHas('cropPlan', fn ($query) => $query->whereIn('municipality', $locationNames))->where('lgu_validation_status', CropPlanHarvestReport::VALIDATION_PENDING)->count(),
            'harvest_approved' => CropPlanHarvestReport::whereHas('cropPlan', fn ($query) => $query->whereIn('municipality', $locationNames))->where('lgu_validation_status', CropPlanHarvestReport::VALIDATION_APPROVED)->count(),
            'harvest_rejected' => CropPlanHarvestReport::whereHas('cropPlan', fn ($query) => $query->whereIn('municipality', $locationNames))->where('lgu_validation_status', CropPlanHarvestReport::VALIDATION_REJECTED)->count(),
        ];

        return view('lgu.dashboard', [
            'validator' => $validator,
            'municipality' => $municipality,
            'barangay' => $barangay,
            'cropPlans' => $cropPlans,
            'damageReports' => $damageReports,
            'harvestReports' => $harvestReports,
            'stats' => $stats,
            'filters' => [
                'status' => $status,
                'type' => $type,
                'search' => $search,
            ],
        ]);
    }

    public function records(Request $request)
    {
        $validated = $request->validate([
            'status' => ['nullable', Rule::in([
                CropPlan::VALIDATION_APPROVED,
                CropPlan::VALIDATION_REJECTED,
                'all',
            ])],
            'type' => ['nullable', Rule::in(['all', 'crop_plans', 'damage_reports', 'harvest_reports'])],
            'search' => ['nullable', 'string', 'max:255'],
        ]);

        $validator = Auth::guard('web')->user();
        $municipality = $validator->normalizedMunicipality();
        $barangay = $validator->normalizedBarangay();
        $locationNames = $this->validatorLocationNames();
        $type = $validated['type'] ?? 'all';
        $status = $validated['status'] ?? 'all';
        $search = trim((string) ($validated['search'] ?? ''));

        $statuses = match ($status) {
            CropPlan::VALIDATION_APPROVED => [CropPlan::VALIDATION_APPROVED],
            CropPlan::VALIDATION_REJECTED => [CropPlan::VALIDATION_REJECTED],
            default => [CropPlan::VALIDATION_APPROVED, CropPlan::VALIDATION_REJECTED],
        };

        $cropPlansQuery = CropPlan::query()
            ->with(['farmer', 'cropType', 'lguValidator'])
            ->whereIn('municipality', $locationNames)
            ->whereIn('lgu_validation_status', $statuses)
            ->when($search !== '', fn ($query) => $this->applyCropPlanSearch($query, $search))
            ->latest('lgu_validated_at');

        $damageReportsQuery = CropPlanDamageReport::query()
            ->with(['farmer', 'cropPlan.cropType', 'lguValidator'])
            ->whereHas('cropPlan', fn ($query) => $query->whereIn('municipality', $locationNames))
            ->whereIn('lgu_validation_status', $statuses)
            ->when($search !== '', fn ($query) => $this->applyDamageReportSearch($query, $search))
            ->latest('lgu_validated_at');

        $harvestReportsQuery = CropPlanHarvestReport::query()
            ->with(['farmer', 'cropPlan.cropType', 'lguValidator'])
            ->whereHas('cropPlan', fn ($query) => $query->whereIn('municipality', $locationNames))
            ->whereIn('lgu_validation_status', $statuses)
            ->when($search !== '', fn ($query) => $this->applyHarvestReportSearch($query, $search))
            ->latest('lgu_validated_at');

        $cropPlans = in_array($type, ['all', 'crop_plans'], true)
            ? $cropPlansQuery->paginate(10, ['*'], 'crop_plan_page')->withQueryString()
            : collect();

        $damageReports = in_array($type, ['all', 'damage_reports'], true)
            ? $damageReportsQuery->paginate(10, ['*'], 'damage_report_page')->withQueryString()
            : collect();

        $harvestReports = in_array($type, ['all', 'harvest_reports'], true)
            ? $harvestReportsQuery->paginate(10, ['*'], 'harvest_report_page')->withQueryString()
            : collect();

        $stats = [
            'crop_plans_approved' => CropPlan::whereIn('municipality', $locationNames)->where('lgu_validation_status', CropPlan::VALIDATION_APPROVED)->count(),
            'crop_plans_rejected' => CropPlan::whereIn('municipality', $locationNames)->where('lgu_validation_status', CropPlan::VALIDATION_REJECTED)->count(),
            'damage_approved' => CropPlanDamageReport::whereHas('cropPlan', fn ($query) => $query->whereIn('municipality', $locationNames))->where('lgu_validation_status', CropPlanDamageReport::VALIDATION_APPROVED)->count(),
            'damage_rejected' => CropPlanDamageReport::whereHas('cropPlan', fn ($query) => $query->whereIn('municipality', $locationNames))->where('lgu_validation_status', CropPlanDamageReport::VALIDATION_REJECTED)->count(),
            'harvest_approved' => CropPlanHarvestReport::whereHas('cropPlan', fn ($query) => $query->whereIn('municipality', $locationNames))->where('lgu_validation_status', CropPlanHarvestReport::VALIDATION_APPROVED)->count(),
            'harvest_rejected' => CropPlanHarvestReport::whereHas('cropPlan', fn ($query) => $query->whereIn('municipality', $locationNames))->where('lgu_validation_status', CropPlanHarvestReport::VALIDATION_REJECTED)->count(),
        ];

        return view('lgu.records', [
            'validator' => $validator,
            'municipality' => $municipality,
            'barangay' => $barangay,
            'cropPlans' => $cropPlans,
            'damageReports' => $damageReports,
            'harvestReports' => $harvestReports,
            'stats' => $stats,
            'filters' => [
                'status' => $status,
                'type' => $type,
                'search' => $search,
            ],
        ]);
    }

    public function approveCropPlan(Request $request, CropPlan $cropPlan)
    {
        $this->ensureCropPlanInMunicipality($cropPlan);

        $validated = $request->validate([
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $cropPlan->update([
            'lgu_validation_status' => CropPlan::VALIDATION_APPROVED,
            'lgu_validated_by' => Auth::id(),
            'lgu_validated_at' => now(),
            'lgu_validation_notes' => $validated['notes'] ?? null,
            'submitted_to_da_at' => now(),
        ]);

        $this->notifyFarmer(
            $cropPlan->farmer,
            'Crop Plan LGU Approved',
            "Your {$cropPlan->crop_name} crop plan has been approved by the LGU validator.",
            $cropPlan->id,
            'green'
        );

        return back()->with('success', 'Crop plan approved and submitted to DA reports.');
    }

    public function rejectCropPlan(Request $request, CropPlan $cropPlan)
    {
        $this->ensureCropPlanInMunicipality($cropPlan);

        $validated = $request->validate([
            'notes' => ['required', 'string', 'max:1000'],
        ]);

        $cropPlan->update([
            'lgu_validation_status' => CropPlan::VALIDATION_REJECTED,
            'lgu_validated_by' => Auth::id(),
            'lgu_validated_at' => now(),
            'lgu_validation_notes' => $validated['notes'],
            'submitted_to_da_at' => null,
        ]);

        $this->notifyFarmer(
            $cropPlan->farmer,
            'Crop Plan Needs Revision',
            "Your {$cropPlan->crop_name} crop plan needs revision. LGU note: {$validated['notes']}",
            $cropPlan->id,
            'orange'
        );

        return back()->with('success', 'Crop plan rejected with LGU revision notes.');
    }

    public function approveDamageReport(Request $request, CropPlanDamageReport $damageReport)
    {
        $this->ensureDamageReportInMunicipality($damageReport);

        $validated = $request->validate([
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($damageReport, $validated): void {
            $damageReport->update([
                'lgu_validation_status' => CropPlanDamageReport::VALIDATION_APPROVED,
                'lgu_validated_by' => Auth::id(),
                'lgu_validated_at' => now(),
                'lgu_validation_notes' => $validated['notes'] ?? null,
                'submitted_to_da_at' => now(),
                'applied_at' => now(),
            ]);

            $damageReport->cropPlan->update([
                'damaged_area_hectares' => $damageReport->damaged_area_hectares,
                'damage_cause' => $damageReport->damage_cause,
                'damage_notes' => $damageReport->damage_notes,
                'damage_occurred_on' => $damageReport->damage_occurred_on,
                'damage_reported_at' => $damageReport->created_at ?? now(),
            ]);
        });

        $this->notifyFarmer(
            $damageReport->farmer,
            'Damage Report LGU Approved',
            "Your damage report for {$damageReport->cropPlan?->crop_name} has been approved and applied to DA records.",
            $damageReport->crop_plan_id,
            'green'
        );

        return back()->with('success', 'Damage report approved and applied to DA-visible totals.');
    }

    public function rejectDamageReport(Request $request, CropPlanDamageReport $damageReport)
    {
        $this->ensureDamageReportInMunicipality($damageReport);

        $validated = $request->validate([
            'notes' => ['required', 'string', 'max:1000'],
        ]);

        $damageReport->update([
            'lgu_validation_status' => CropPlanDamageReport::VALIDATION_REJECTED,
            'lgu_validated_by' => Auth::id(),
            'lgu_validated_at' => now(),
            'lgu_validation_notes' => $validated['notes'],
            'submitted_to_da_at' => null,
            'applied_at' => null,
        ]);

        $this->notifyFarmer(
            $damageReport->farmer,
            'Damage Report Needs Revision',
            "Your damage report for {$damageReport->cropPlan?->crop_name} needs revision. LGU note: {$validated['notes']}",
            $damageReport->crop_plan_id,
            'orange'
        );

        return back()->with('success', 'Damage report rejected with LGU revision notes.');
    }

    public function approveHarvestReport(Request $request, CropPlanHarvestReport $harvestReport)
    {
        $this->ensureHarvestReportInMunicipality($harvestReport);

        $validated = $request->validate([
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($harvestReport, $validated): void {
            $harvestReport->update([
                'lgu_validation_status' => CropPlanHarvestReport::VALIDATION_APPROVED,
                'lgu_validated_by' => Auth::id(),
                'lgu_validated_at' => now(),
                'lgu_validation_notes' => $validated['notes'] ?? null,
                'submitted_to_da_at' => now(),
                'applied_at' => now(),
            ]);

            $harvestReport->cropPlan->update([
                'status' => 'harvested',
                'actual_harvest_date' => $harvestReport->actual_harvest_date,
                'actual_harvest_production_mt' => $harvestReport->actual_production_mt,
                'actual_harvest_report_id' => $harvestReport->id,
                'actual_harvest_reported_at' => now(),
            ]);
        });

        $this->notifyFarmer(
            $harvestReport->farmer,
            'Actual Harvest LGU Approved',
            "Your actual harvest report for {$harvestReport->cropPlan?->crop_name} has been approved and submitted to DA records.",
            $harvestReport->crop_plan_id,
            'green'
        );

        return back()->with('success', 'Actual harvest approved and applied to DA-visible totals.');
    }

    public function rejectHarvestReport(Request $request, CropPlanHarvestReport $harvestReport)
    {
        $this->ensureHarvestReportInMunicipality($harvestReport);

        $validated = $request->validate([
            'notes' => ['required', 'string', 'max:1000'],
        ]);

        $harvestReport->update([
            'lgu_validation_status' => CropPlanHarvestReport::VALIDATION_REJECTED,
            'lgu_validated_by' => Auth::id(),
            'lgu_validated_at' => now(),
            'lgu_validation_notes' => $validated['notes'],
            'submitted_to_da_at' => null,
            'applied_at' => null,
        ]);

        $this->notifyFarmer(
            $harvestReport->farmer,
            'Actual Harvest Needs Revision',
            "Your actual harvest report for {$harvestReport->cropPlan?->crop_name} needs revision. LGU note: {$validated['notes']}",
            $harvestReport->crop_plan_id,
            'orange'
        );

        return back()->with('success', 'Actual harvest report rejected with LGU revision notes.');
    }

    private function ensureCropPlanInMunicipality(CropPlan $cropPlan): void
    {
        $locationNames = $this->validatorLocationNames();

        abort_unless(in_array(Municipality::normalizeLocationName($cropPlan->municipality), $locationNames, true), 403);
    }

    private function ensureDamageReportInMunicipality(CropPlanDamageReport $damageReport): void
    {
        $damageReport->loadMissing('cropPlan');
        $this->ensureCropPlanInMunicipality($damageReport->cropPlan);
    }

    private function ensureHarvestReportInMunicipality(CropPlanHarvestReport $harvestReport): void
    {
        $harvestReport->loadMissing('cropPlan');
        $this->ensureCropPlanInMunicipality($harvestReport->cropPlan);
    }

    private function validatorLocationNames(): array
    {
        $validator = Auth::guard('web')->user();
        $barangay = $validator->normalizedBarangay();

        if ($barangay) {
            return [$barangay];
        }

        return Municipality::locationNamesForMunicipality($validator->normalizedMunicipality());
    }

    private function applyCropPlanSearch(Builder $query, string $search): void
    {
        $searchTerm = '%' . str_replace(['%', '_'], ['\%', '\_'], strtolower($search)) . '%';

        $query->where(function ($searchQuery) use ($searchTerm) {
            $searchQuery->whereRaw('LOWER(crop_name) LIKE ?', [$searchTerm])
                ->orWhereRaw('LOWER(farm_type) LIKE ?', [$searchTerm])
                ->orWhereHas('farmer', function ($farmerQuery) use ($searchTerm) {
                    $farmerQuery->whereRaw('LOWER(farmer_id) LIKE ?', [$searchTerm])
                        ->orWhereRaw('LOWER(first_name) LIKE ?', [$searchTerm])
                        ->orWhereRaw('LOWER(last_name) LIKE ?', [$searchTerm]);
                });
        });
    }

    private function applyDamageReportSearch(Builder $query, string $search): void
    {
        $searchTerm = '%' . str_replace(['%', '_'], ['\%', '\_'], strtolower($search)) . '%';

        $query->where(function ($searchQuery) use ($searchTerm) {
            $searchQuery->whereRaw('LOWER(damage_cause) LIKE ?', [$searchTerm])
                ->orWhereRaw('LOWER(damage_notes) LIKE ?', [$searchTerm])
                ->orWhereHas('cropPlan', function ($cropPlanQuery) use ($searchTerm) {
                    $cropPlanQuery->whereRaw('LOWER(crop_name) LIKE ?', [$searchTerm]);
                })
                ->orWhereHas('farmer', function ($farmerQuery) use ($searchTerm) {
                    $farmerQuery->whereRaw('LOWER(farmer_id) LIKE ?', [$searchTerm])
                        ->orWhereRaw('LOWER(first_name) LIKE ?', [$searchTerm])
                        ->orWhereRaw('LOWER(last_name) LIKE ?', [$searchTerm]);
                });
        });
    }

    private function applyHarvestReportSearch(Builder $query, string $search): void
    {
        $searchTerm = '%' . str_replace(['%', '_'], ['\%', '\_'], strtolower($search)) . '%';

        $query->where(function ($searchQuery) use ($searchTerm) {
            $searchQuery->whereRaw('LOWER(harvest_notes) LIKE ?', [$searchTerm])
                ->orWhereHas('cropPlan', function ($cropPlanQuery) use ($searchTerm) {
                    $cropPlanQuery->whereRaw('LOWER(crop_name) LIKE ?', [$searchTerm])
                        ->orWhereRaw('LOWER(farm_type) LIKE ?', [$searchTerm]);
                })
                ->orWhereHas('farmer', function ($farmerQuery) use ($searchTerm) {
                    $farmerQuery->whereRaw('LOWER(farmer_id) LIKE ?', [$searchTerm])
                        ->orWhereRaw('LOWER(first_name) LIKE ?', [$searchTerm])
                        ->orWhereRaw('LOWER(last_name) LIKE ?', [$searchTerm]);
                });
        });
    }

    private function notifyFarmer($farmer, string $title, string $message, int $cropPlanId, string $color): void
    {
        if (! $farmer) {
            return;
        }

        FarmerNotification::create([
            'farmer_id' => $farmer->id,
            'type' => FarmerNotification::TYPE_LGU_VALIDATION,
            'title' => $title,
            'message' => $message,
            'icon' => 'shield',
            'icon_color' => $color,
            'link' => route('farmers.calendar'),
            'data' => [
                'crop_plan_id' => $cropPlanId,
            ],
        ]);
    }
}
