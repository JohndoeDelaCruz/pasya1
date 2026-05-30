<?php

namespace Tests\Feature;

use App\Models\CropPlan;
use App\Models\CropPlanDamageReport;
use App\Models\CropPlanHarvestReport;
use App\Models\CropType;
use App\Models\Farmer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LguValidatorWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_login_redirects_lgu_validator_to_queue(): void
    {
        $validator = User::factory()->create([
            'role' => User::ROLE_LGU_VALIDATOR,
            'municipality' => 'BUGUIAS',
            'is_active' => true,
        ]);

        $response = $this->post('/login', [
            'login_mode' => 'staff',
            'email' => $validator->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('lgu.dashboard', absolute: false));
        $this->assertAuthenticatedAs($validator);
    }

    public function test_admin_can_create_lgu_validator_account(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->post(route('admin.lgu-validators.store'), [
            'name' => 'Buguias Validator',
            'username' => 'buguias-validator',
            'email' => 'buguias.validator@example.com',
            'municipality' => 'BUGUIAS',
            'barangay' => 'ABATAN',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('admin.lgu-validators.index'));
        $this->assertDatabaseHas('users', [
            'email' => 'buguias.validator@example.com',
            'role' => User::ROLE_LGU_VALIDATOR,
            'municipality' => 'BUGUIAS',
            'barangay' => 'ABATAN',
            'is_active' => true,
        ]);
    }

    public function test_lgu_queue_is_scoped_to_validator_municipality(): void
    {
        $validator = $this->createValidator('BUGUIAS');
        $buguiasPlan = $this->createCropPlan(['municipality' => 'BUGUIAS', 'crop_name' => 'Buguias Cabbage']);
        $atokPlan = $this->createCropPlan(['municipality' => 'ATOK', 'crop_name' => 'Atok Potato']);

        $response = $this->actingAs($validator)
            ->get(route('lgu.dashboard'));

        $response->assertOk();
        $response->assertSee($buguiasPlan->crop_name);
        $response->assertDontSee($atokPlan->crop_name);
    }

    public function test_municipality_lgu_queue_includes_barangay_encoded_farmer_locations(): void
    {
        $validator = $this->createValidator('LA TRINIDAD');
        $alapangPlan = $this->createCropPlan(['municipality' => 'ALAPANG', 'crop_name' => 'Alapang Lettuce']);
        $shilanPlan = $this->createCropPlan(['municipality' => 'SHILAN', 'crop_name' => 'Shilan Strawberry']);
        $buguiasPlan = $this->createCropPlan(['municipality' => 'BUGUIAS', 'crop_name' => 'Buguias Cabbage']);

        $response = $this->actingAs($validator)
            ->get(route('lgu.dashboard'));

        $response->assertOk();
        $response->assertSee($alapangPlan->crop_name);
        $response->assertSee($shilanPlan->crop_name);
        $response->assertDontSee($buguiasPlan->crop_name);
    }

    public function test_barangay_scoped_lgu_queue_only_shows_that_barangay(): void
    {
        $validator = $this->createValidator('LA TRINIDAD', 'ALAPANG');
        $alapangPlan = $this->createCropPlan(['municipality' => 'ALAPANG', 'crop_name' => 'Alapang Lettuce']);
        $shilanPlan = $this->createCropPlan(['municipality' => 'SHILAN', 'crop_name' => 'Shilan Strawberry']);

        $response = $this->actingAs($validator)
            ->get(route('lgu.dashboard'));

        $response->assertOk();
        $response->assertSee($alapangPlan->crop_name);
        $response->assertDontSee($shilanPlan->crop_name);
    }

    public function test_lgu_can_approve_reject_and_farmer_can_resubmit_crop_plan(): void
    {
        $validator = $this->createValidator('BUGUIAS');
        $plan = $this->createCropPlan(['municipality' => 'BUGUIAS']);

        $this->actingAs($validator)
            ->post(route('lgu.crop-plans.reject', $plan), [
                'notes' => 'Please correct the planting area.',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('crop_plans', [
            'id' => $plan->id,
            'lgu_validation_status' => CropPlan::VALIDATION_REJECTED,
            'lgu_validation_notes' => 'Please correct the planting area.',
        ]);

        $farmer = $plan->farmer;
        $cropType = $plan->cropType;

        $this->actingAs($farmer, 'farmer')
            ->patchJson(route('farmers.api.crop-plans.update', $plan), [
                'crop_type_id' => $cropType->id,
                'planting_date' => '2026-05-10',
                'area_hectares' => 4,
                'farm_type' => 'IRRIGATED',
                'planting_material_type' => 'SEED',
                'notes' => 'Corrected area.',
            ])
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('crop_plans', [
            'id' => $plan->id,
            'lgu_validation_status' => CropPlan::VALIDATION_PENDING,
            'lgu_validation_revision' => 1,
            'lgu_validation_notes' => null,
        ]);
    }

    public function test_damage_report_approval_applies_to_official_crop_plan_totals(): void
    {
        $validator = $this->createValidator('BUGUIAS');
        $plan = $this->createCropPlan([
            'municipality' => 'BUGUIAS',
            'area_hectares' => 5,
            'predicted_production' => 50,
            'lgu_validation_status' => CropPlan::VALIDATION_APPROVED,
        ]);

        $damageReport = CropPlanDamageReport::create([
            'crop_plan_id' => $plan->id,
            'farmer_id' => $plan->farmer_id,
            'damaged_area_hectares' => 2,
            'damage_cause' => 'typhoon',
            'damage_occurred_on' => '2026-05-01',
            'damage_notes' => 'Wind damage.',
            'lgu_validation_status' => CropPlanDamageReport::VALIDATION_PENDING,
        ]);

        $this->actingAs($validator)
            ->post(route('lgu.damage-reports.approve', $damageReport))
            ->assertRedirect();

        $this->assertDatabaseHas('crop_plan_damage_reports', [
            'id' => $damageReport->id,
            'lgu_validation_status' => CropPlanDamageReport::VALIDATION_APPROVED,
            'lgu_validated_by' => $validator->id,
        ]);

        $this->assertDatabaseHas('crop_plans', [
            'id' => $plan->id,
            'damaged_area_hectares' => 2,
            'damage_cause' => 'typhoon',
        ]);
    }

    public function test_damage_report_filter_defaults_to_all_statuses(): void
    {
        $validator = $this->createValidator('BUGUIAS');
        $plan = $this->createCropPlan([
            'municipality' => 'BUGUIAS',
            'crop_name' => 'Approved Damage Crop',
            'lgu_validation_status' => CropPlan::VALIDATION_APPROVED,
        ]);

        CropPlanDamageReport::create([
            'crop_plan_id' => $plan->id,
            'farmer_id' => $plan->farmer_id,
            'damaged_area_hectares' => 1,
            'damage_cause' => 'typhoon',
            'damage_occurred_on' => '2026-05-01',
            'damage_notes' => 'Approved damage report.',
            'lgu_validation_status' => CropPlanDamageReport::VALIDATION_APPROVED,
            'lgu_validated_by' => $validator->id,
            'lgu_validated_at' => now(),
        ]);

        $response = $this->actingAs($validator)
            ->get(route('lgu.dashboard', ['type' => 'damage_reports']));

        $response->assertOk();
        $response->assertSee('Approved Damage Crop');
        $response->assertSee('value="all" selected', false);
    }

    public function test_farmer_harvest_report_waits_for_lgu_approval(): void
    {
        $plan = $this->createCropPlan([
            'municipality' => 'BUGUIAS',
            'lgu_validation_status' => CropPlan::VALIDATION_APPROVED,
        ]);

        $this->actingAs($plan->farmer, 'farmer')
            ->postJson(route('farmers.api.crop-plans.harvest-report', $plan), [
                'actual_harvest_date' => '2026-05-30',
                'actual_harvest_quantity_kg' => 1250,
                'harvest_notes' => 'Actual weighed harvest.',
            ])
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('crop_plan_harvest_reports', [
            'crop_plan_id' => $plan->id,
            'farmer_id' => $plan->farmer_id,
            'actual_production_mt' => 1.25,
            'lgu_validation_status' => CropPlanHarvestReport::VALIDATION_PENDING,
        ]);

        $this->assertDatabaseHas('crop_plans', [
            'id' => $plan->id,
            'status' => 'planned',
            'actual_harvest_production_mt' => null,
        ]);
    }

    public function test_lgu_approves_harvest_report_as_official_actual_harvest(): void
    {
        $validator = $this->createValidator('BUGUIAS');
        $plan = $this->createCropPlan([
            'municipality' => 'BUGUIAS',
            'lgu_validation_status' => CropPlan::VALIDATION_APPROVED,
            'predicted_production' => 36,
        ]);

        $harvestReport = CropPlanHarvestReport::create([
            'crop_plan_id' => $plan->id,
            'farmer_id' => $plan->farmer_id,
            'actual_harvest_date' => '2026-05-30',
            'actual_production_mt' => 1.25,
            'harvest_notes' => 'Actual weighed harvest.',
            'lgu_validation_status' => CropPlanHarvestReport::VALIDATION_PENDING,
        ]);

        $this->actingAs($validator)
            ->post(route('lgu.harvest-reports.approve', $harvestReport))
            ->assertRedirect();

        $this->assertDatabaseHas('crop_plan_harvest_reports', [
            'id' => $harvestReport->id,
            'lgu_validation_status' => CropPlanHarvestReport::VALIDATION_APPROVED,
            'lgu_validated_by' => $validator->id,
        ]);

        $this->assertDatabaseHas('crop_plans', [
            'id' => $plan->id,
            'status' => 'harvested',
            'actual_harvest_production_mt' => 1.25,
            'actual_harvest_report_id' => $harvestReport->id,
        ]);
    }

    public function test_da_planting_report_defaults_to_approved_records(): void
    {
        $admin = $this->createAdmin();
        $approved = $this->createCropPlan([
            'crop_name' => 'Approved Cabbage',
            'lgu_validation_status' => CropPlan::VALIDATION_APPROVED,
        ]);
        $pending = $this->createCropPlan([
            'crop_name' => 'Pending Potato',
            'lgu_validation_status' => CropPlan::VALIDATION_PENDING,
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.planting-report'));

        $response->assertOk();
        $response->assertSee($approved->crop_name);
        $response->assertDontSee($pending->crop_name);
    }

    private function createAdmin(): User
    {
        return User::factory()->create([
            'role' => User::ROLE_DA_ADMIN,
            'is_active' => true,
        ]);
    }

    private function createValidator(string $municipality, ?string $barangay = null): User
    {
        return User::factory()->create([
            'role' => User::ROLE_LGU_VALIDATOR,
            'municipality' => $municipality,
            'barangay' => $barangay,
            'is_active' => true,
        ]);
    }

    private function createCropPlan(array $overrides = []): CropPlan
    {
        $sequence = CropPlan::count() + 1;
        $municipality = $overrides['municipality'] ?? 'BUGUIAS';

        $farmer = Farmer::create([
            'farmer_id' => sprintf('FMR-LGU-%03d', $sequence),
            'first_name' => 'LGU',
            'middle_name' => null,
            'last_name' => 'Farmer' . $sequence,
            'suffix' => null,
            'municipality' => $municipality,
            'cooperative' => null,
            'contact_info' => null,
            'email' => 'lgu-farmer-' . $sequence . '@example.com',
            'mobile_number' => '0912345' . str_pad((string) $sequence, 4, '0', STR_PAD_LEFT),
            'password' => 'password',
            'created_by' => null,
        ]);

        $cropType = CropType::create([
            'name' => 'LGU Test Crop ' . $sequence,
            'category' => 'Vegetable',
            'description' => 'Test crop type',
            'days_to_harvest' => 80,
            'average_yield_per_hectare' => 12,
            'is_active' => true,
        ]);

        return CropPlan::create(array_merge([
            'farmer_id' => $farmer->id,
            'crop_type_id' => $cropType->id,
            'crop_name' => 'LGU Test Crop ' . $sequence,
            'planting_date' => '2026-04-25',
            'expected_harvest_date' => '2026-07-14',
            'area_hectares' => 3,
            'predicted_production' => 36,
            'municipality' => $municipality,
            'farm_type' => 'IRRIGATED',
            'planting_material_type' => 'SEED',
            'status' => 'planned',
            'lgu_validation_status' => CropPlan::VALIDATION_PENDING,
            'notes' => null,
        ], $overrides));
    }
}
