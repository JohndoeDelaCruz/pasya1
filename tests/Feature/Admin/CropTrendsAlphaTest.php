<?php

namespace Tests\Feature\Admin;

use App\Models\Crop;
use App\Models\CropPlan;
use App\Models\CropType;
use App\Models\Farmer;
use App\Models\User;
use App\Services\PredictionService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CropTrendsAlphaTest extends TestCase
{
    use RefreshDatabase;

    public function test_alpha_page_blocks_predictions_below_ten_percent_participation(): void
    {
        $admin = $this->createAdmin();
        $this->createHistoricalCrop();
        $this->createFarmers(10);

        $this->mock(PredictionService::class, function ($mock): void {
            $mock->shouldNotReceive('checkHealth');
            $mock->shouldNotReceive('predictProduction');
        });

        $response = $this->actingAs($admin)->get(route('admin.crop-trends-alpha', [
            'municipality' => 'BUGUIAS',
            'crop' => 'CABBAGE',
            'farm_type' => 'IRRIGATED',
        ]));

        $response->assertOk();
        $response->assertSee('Insufficient farmer participation');
    }

    public function test_alpha_page_calculates_predictions_at_ten_percent_participation(): void
    {
        $admin = $this->createAdmin();
        $this->createHistoricalCrop();
        $farmers = $this->createFarmers(10);
        $this->createApprovedPlan($farmers->first());

        $this->mock(PredictionService::class, function ($mock): void {
            $mock->shouldReceive('checkHealth')->andReturn(true);
            $mock->shouldReceive('predictProduction')->andReturn([
                'success' => true,
                'prediction' => [
                    'production_mt' => 10,
                    'confidence_score' => 90,
                ],
            ]);
        });

        $response = $this->actingAs($admin)->get(route('admin.crop-trends-alpha', [
            'municipality' => 'BUGUIAS',
            'crop' => 'CABBAGE',
            'farm_type' => 'IRRIGATED',
        ]));

        $response->assertOk();
        $response->assertSee('10.00% plan coverage');
        $response->assertSee('ML forecast');
    }

    public function test_alpha_page_shows_actual_reporting_rate_for_harvest_ready_plans(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-08-01 08:00:00'));

        try {
            $admin = $this->createAdmin();
            $this->createHistoricalCrop();
            $farmers = $this->createFarmers(10);
            $this->createApprovedPlan($farmers[0], [
                'expected_harvest_date' => '2026-07-20',
                'actual_harvest_date' => '2026-07-22',
                'actual_harvest_production_mt' => 8.5,
                'status' => 'harvested',
            ]);
            $this->createApprovedPlan($farmers[1], [
                'expected_harvest_date' => '2026-07-20',
            ]);

            $this->mock(PredictionService::class, function ($mock): void {
                $mock->shouldReceive('checkHealth')->andReturn(true);
                $mock->shouldReceive('predictProduction')->andReturn([
                    'success' => true,
                    'prediction' => [
                        'production_mt' => 10,
                        'confidence_score' => 90,
                    ],
                ]);
            });

            $response = $this->actingAs($admin)->get(route('admin.crop-trends-alpha', [
                'municipality' => 'BUGUIAS',
                'crop' => 'CABBAGE',
                'farm_type' => 'IRRIGATED',
            ]));

            $response->assertOk();
            $response->assertSee('Actual Reporting Rate');
            $response->assertSee('50.00%');
            $response->assertSee('1 of 2 harvest-ready farmers');
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_alpha_page_counts_latrinidad_alias_and_barangay_farmers(): void
    {
        $admin = $this->createAdmin();
        $this->createHistoricalCrop([
            'municipality' => 'Latrinidad',
        ]);

        Farmer::create([
            'farmer_id' => 'FMR-LT-001',
            'first_name' => 'La',
            'middle_name' => null,
            'last_name' => 'Trinidad',
            'suffix' => null,
            'municipality' => 'LA TRINIDAD',
            'cooperative' => null,
            'contact_info' => null,
            'email' => 'la-trinidad@example.com',
            'mobile_number' => '09123000001',
            'password' => 'password',
            'created_by' => null,
        ]);
        Farmer::create([
            'farmer_id' => 'FMR-LT-002',
            'first_name' => 'Beckel',
            'middle_name' => null,
            'last_name' => 'Farmer',
            'suffix' => null,
            'municipality' => 'BECKEL',
            'cooperative' => null,
            'contact_info' => null,
            'email' => 'beckel@example.com',
            'mobile_number' => '09123000002',
            'password' => 'password',
            'created_by' => null,
        ]);
        Farmer::create([
            'farmer_id' => 'FMR-LT-003',
            'first_name' => 'Compact',
            'middle_name' => null,
            'last_name' => 'Farmer',
            'suffix' => null,
            'municipality' => 'LATRINIDAD',
            'cooperative' => null,
            'contact_info' => null,
            'email' => 'compact-latrinidad@example.com',
            'mobile_number' => '09123000003',
            'password' => 'password',
            'created_by' => null,
        ]);
        Farmer::create([
            'farmer_id' => 'FMR-BUG-001',
            'first_name' => 'Buguias',
            'middle_name' => null,
            'last_name' => 'Farmer',
            'suffix' => null,
            'municipality' => 'BUGUIAS',
            'cooperative' => null,
            'contact_info' => null,
            'email' => 'buguias-extra@example.com',
            'mobile_number' => '09123000004',
            'password' => 'password',
            'created_by' => null,
        ]);

        $this->mock(PredictionService::class, function ($mock): void {
            $mock->shouldNotReceive('checkHealth');
            $mock->shouldNotReceive('predictProduction');
        });

        $response = $this->actingAs($admin)->get(route('admin.crop-trends-alpha', [
            'municipality' => 'Latrinidad',
            'crop' => 'CABBAGE',
            'farm_type' => 'IRRIGATED',
        ]));

        $response->assertOk();
        $response->assertSee('La Trinidad');
        $response->assertSee('<p class="mt-2 text-2xl font-bold text-gray-900">3</p>', false);
    }

    private function createAdmin(): User
    {
        return User::factory()->create([
            'role' => User::ROLE_DA_ADMIN,
            'is_active' => true,
        ]);
    }

    private function createHistoricalCrop(array $overrides = []): Crop
    {
        return Crop::create(array_merge([
            'municipality' => 'BUGUIAS',
            'farm_type' => 'IRRIGATED',
            'year' => 2025,
            'month' => 'MAY',
            'crop' => 'CABBAGE',
            'area_planted' => 5,
            'area_harvested' => 5,
            'production' => 50,
            'productivity' => 10,
            'uploaded_by' => $this->createAdmin()->id,
        ], $overrides));
    }

    private function createFarmers(int $count)
    {
        return collect(range(1, $count))->map(fn ($index) => Farmer::create([
            'farmer_id' => sprintf('FMR-ALPHA-%03d', $index),
            'first_name' => 'Alpha',
            'middle_name' => null,
            'last_name' => 'Farmer' . $index,
            'suffix' => null,
            'municipality' => 'BUGUIAS',
            'cooperative' => null,
            'contact_info' => null,
            'email' => 'alpha-farmer-' . $index . '@example.com',
            'mobile_number' => '0912345' . str_pad((string) $index, 4, '0', STR_PAD_LEFT),
            'password' => 'password',
            'created_by' => null,
        ]));
    }

    private function createApprovedPlan(Farmer $farmer, array $overrides = []): CropPlan
    {
        $cropType = CropType::firstOrCreate(
            ['name' => 'Cabbage'],
            [
                'category' => 'Vegetable',
                'description' => 'Test crop type',
                'days_to_harvest' => 80,
                'average_yield_per_hectare' => 10,
                'is_active' => true,
            ]
        );

        return CropPlan::create(array_merge([
            'farmer_id' => $farmer->id,
            'crop_type_id' => $cropType->id,
            'crop_name' => 'CABBAGE',
            'planting_date' => '2026-05-01',
            'expected_harvest_date' => '2026-07-20',
            'area_hectares' => 1,
            'predicted_production' => 10,
            'municipality' => 'BUGUIAS',
            'farm_type' => 'IRRIGATED',
            'planting_material_type' => 'SEED',
            'status' => 'planned',
            'lgu_validation_status' => CropPlan::VALIDATION_APPROVED,
            'submitted_to_da_at' => now(),
        ], $overrides));
    }
}
