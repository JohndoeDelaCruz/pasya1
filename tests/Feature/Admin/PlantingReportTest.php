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

        $farmer = Farmer::create([
            'farmer_id' => 'FMR260001',
            'first_name' => 'Maria',
            'middle_name' => null,
            'last_name' => 'Santos',
            'suffix' => null,
            'municipality' => 'BUGUIAS',
            'cooperative' => 'Benguet Growers',
            'contact_info' => null,
            'email' => 'maria.santos@example.com',
            'mobile_number' => '09123456781',
            'password' => 'password',
            'created_by' => $admin->id,
        ]);

        $cropType = CropType::create([
            'name' => 'Broccoli',
            'category' => 'Vegetable',
            'description' => 'Test crop type',
            'days_to_harvest' => 80,
            'average_yield_per_hectare' => 12,
            'is_active' => true,
        ]);

        CropPlan::create([
            'farmer_id' => $farmer->id,
            'crop_type_id' => $cropType->id,
            'crop_name' => 'Broccoli',
            'planting_date' => '2026-04-25',
            'expected_harvest_date' => '2026-07-14',
            'area_hectares' => 8,
            'predicted_production' => 88.23,
            'municipality' => 'BUGUIAS',
            'farm_type' => 'IRRIGATED',
            'planting_material_type' => 'SEEDLING',
            'status' => 'planned',
            'notes' => 'First wet-season batch',
        ]);

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
}