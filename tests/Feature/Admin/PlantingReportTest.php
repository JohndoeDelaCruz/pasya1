<?php

namespace Tests\Feature\Admin;

use App\Models\CropPlan;
use App\Models\CropType;
use App\Models\Farmer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlantingReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_farmer_calendar_records_in_planting_report(): void
    {
        $admin = User::factory()->create();

        $this->createPlantingRecord(
            $admin,
            [
                'farmer_id' => 'FMR260001',
                'first_name' => 'Maria',
                'last_name' => 'Santos',
                'municipality' => 'BUGUIAS',
                'email' => 'maria.santos@example.com',
                'mobile_number' => '09123456781',
            ],
            [
                'crop_name' => 'Broccoli',
                'municipality' => 'BUGUIAS',
                'status' => 'planned',
                'planting_material_type' => 'SEEDLING',
            ],
        );

        $response = $this->actingAs($admin)
            ->get(route('admin.planting-report'));

        $response->assertOk();
        $response->assertSee('Planting Records');
        $response->assertSee('Maria Santos');
        $response->assertSee('FMR260001');
        $response->assertSee('Broccoli');
        $response->assertSee('09123456781');
        $response->assertSee('Seedling');
    }

    public function test_admin_can_export_filtered_planting_report_as_csv(): void
    {
        $admin = User::factory()->create();

        $this->createPlantingRecord(
            $admin,
            [
                'first_name' => 'Maria',
                'last_name' => 'Santos',
                'municipality' => 'BUGUIAS',
                'email' => 'maria.santos@example.com',
                'mobile_number' => '09123456781',
            ],
            [
                'crop_name' => 'Broccoli',
                'municipality' => 'BUGUIAS',
                'status' => 'planned',
            ],
        );

        $this->createPlantingRecord(
            $admin,
            [
                'first_name' => 'Jose',
                'last_name' => 'Reyes',
                'municipality' => 'ATOK',
                'email' => 'jose.reyes@example.com',
                'mobile_number' => '09123456782',
            ],
            [
                'crop_name' => 'Cabbage',
                'municipality' => 'ATOK',
                'status' => 'harvested',
            ],
        );

        $response = $this->actingAs($admin)->get(route('admin.planting-report.export.csv', [
            'status' => 'planned',
            'municipality' => 'BUGUIAS',
        ]));

        $response->assertOk();
        $this->assertStringContainsString('text/csv', (string) $response->headers->get('content-type'));
        $this->assertStringContainsString('.csv', (string) $response->headers->get('content-disposition'));

        $csv = $response->streamedContent();

        $this->assertStringContainsString('Maria Santos', $csv);
        $this->assertStringContainsString('Broccoli', $csv);
        $this->assertStringNotContainsString('Jose Reyes', $csv);
        $this->assertStringNotContainsString('Cabbage', $csv);
    }

    public function test_admin_can_export_filtered_planting_report_as_pdf(): void
    {
        $admin = User::factory()->create();

        $this->createPlantingRecord(
            $admin,
            [
                'first_name' => 'Maria',
                'last_name' => 'Santos',
                'municipality' => 'BUGUIAS',
                'email' => 'maria.santos@example.com',
                'mobile_number' => '09123456781',
            ],
            [
                'crop_name' => 'Broccoli',
                'municipality' => 'BUGUIAS',
                'status' => 'planned',
            ],
        );

        $response = $this->actingAs($admin)->get(route('admin.planting-report.export.pdf', [
            'status' => 'planned',
            'municipality' => 'BUGUIAS',
        ]));

        $response->assertOk();
        $this->assertStringContainsString('application/pdf', (string) $response->headers->get('content-type'));
        $this->assertStringContainsString('.pdf', (string) $response->headers->get('content-disposition'));
    }

    private function createPlantingRecord(User $admin, array $farmerOverrides = [], array $cropPlanOverrides = []): CropPlan
    {
        $sequence = CropPlan::count() + 1;
        $cropName = $cropPlanOverrides['crop_name'] ?? 'Crop ' . $sequence;

        $farmer = Farmer::create(array_merge([
            'farmer_id' => sprintf('FMR260%03d', $sequence),
            'first_name' => 'Farmer',
            'middle_name' => null,
            'last_name' => 'Test' . $sequence,
            'suffix' => null,
            'municipality' => 'BUGUIAS',
            'cooperative' => 'Benguet Growers',
            'contact_info' => null,
            'email' => 'farmer' . $sequence . '@example.com',
            'mobile_number' => '0912345' . str_pad((string) $sequence, 4, '0', STR_PAD_LEFT),
            'password' => 'password',
            'created_by' => $admin->id,
        ], $farmerOverrides));

        $cropType = CropType::create([
            'name' => $cropName . ' Type ' . $sequence,
            'category' => 'Vegetable',
            'description' => 'Test crop type',
            'days_to_harvest' => 80,
            'average_yield_per_hectare' => 12,
            'is_active' => true,
        ]);

        return CropPlan::create(array_merge([
            'farmer_id' => $farmer->id,
            'crop_type_id' => $cropType->id,
            'crop_name' => $cropName,
            'planting_date' => '2026-04-25',
            'expected_harvest_date' => '2026-07-14',
            'area_hectares' => 8,
            'predicted_production' => 88.23,
            'municipality' => $farmer->municipality,
            'farm_type' => 'IRRIGATED',
            'planting_material_type' => 'SEEDLING',
            'status' => 'planned',
            'notes' => 'First wet-season batch',
        ], $cropPlanOverrides));
    }
}