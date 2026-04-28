<?php

namespace Tests\Feature\Farmer;

use App\Models\CropType;
use App\Models\Farmer;
use App\Services\MLApiService;
use App\Services\PredictionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery\MockInterface;
use Tests\TestCase;

class CropPlanPreviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_preview_returns_live_ml_prediction_fields(): void
    {
        $farmer = Farmer::create([
            'farmer_id' => 'FARMER-1001',
            'first_name' => 'Test',
            'last_name' => 'Farmer',
            'municipality' => 'BUGUIAS',
            'mobile_number' => '09123456789',
            'email' => 'farmer-preview@example.com',
            'password' => 'password',
        ]);

        $cropType = CropType::create([
            'name' => 'BROCCOLI',
            'category' => 'Vegetable',
            'days_to_harvest' => 80,
            'average_yield_per_hectare' => 12,
            'seedling_days' => 35,
            'supports_seed_material' => true,
            'supports_seedling_material' => true,
            'is_active' => true,
        ]);

        $this->mock(PredictionService::class, function (MockInterface $mock): void {
            $mock->shouldReceive('predictProduction')
                ->once()
                ->andReturn([
                    'success' => true,
                    'prediction' => [
                        'production_mt' => 97.97,
                        'productivity_mt_ha' => 14.0,
                        'confidence_score' => 81.76,
                    ],
                ]);
        });

        $response = $this
            ->actingAs($farmer, 'farmer')
            ->postJson(route('farmers.api.crop-plans.preview'), [
                'crop_type_id' => $cropType->id,
                'planting_date' => '2026-04-28',
                'area_hectares' => 7,
                'farm_type' => 'IRRIGATED',
                'planting_material_type' => 'SEEDLING',
            ]);

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.predicted_production', 97.97)
            ->assertJsonPath('data.predicted_production_formatted', '97.97 MT')
            ->assertJsonPath('data.productivity_mt_ha', 14)
            ->assertJsonPath('data.productivity_mt_ha_formatted', '14.00 MT/ha')
            ->assertJsonPath('data.productivity_label', 'Predicted productivity')
            ->assertJsonPath('data.prediction_source', 'ml')
            ->assertJsonPath('data.prediction_source_label', 'Live ML API')
            ->assertJsonPath('data.confidence_score', 81.76)
            ->assertJsonPath('data.confidence_score_formatted', '81.76%')
            ->assertJsonPath('data.average_yield_per_hectare', 14);
    }

    public function test_preview_retries_live_ml_when_prediction_service_fails(): void
    {
        $farmer = Farmer::create([
            'farmer_id' => 'FARMER-1002',
            'first_name' => 'Retry',
            'last_name' => 'Farmer',
            'municipality' => 'BUGUIAS',
            'mobile_number' => '09999999999',
            'email' => 'farmer-preview-retry@example.com',
            'password' => 'password',
        ]);

        $cropType = CropType::create([
            'name' => 'CABBAGE',
            'category' => 'Vegetable',
            'days_to_harvest' => 90,
            'average_yield_per_hectare' => 25,
            'seedling_days' => 35,
            'supports_seed_material' => true,
            'supports_seedling_material' => true,
            'is_active' => true,
        ]);

        $this->mock(PredictionService::class, function (MockInterface $mock): void {
            $mock->shouldReceive('predictProduction')
                ->once()
                ->andReturn([
                    'success' => false,
                    'error' => 'Prediction service error: crop_name_mappings table missing',
                ]);

            $mock->shouldReceive('patternBasedNormalization')
                ->once()
                ->with('CABBAGE')
                ->andReturn('CABBAGE');
        });

        $this->mock(MLApiService::class, function (MockInterface $mock): void {
            $mock->shouldReceive('predict')
                ->once()
                ->andReturn([
                    'success' => true,
                    'prediction' => [
                        'production_mt' => 86.88,
                        'productivity_mt_ha' => 21.72,
                        'confidence_score' => 83.12,
                    ],
                ]);
        });

        $response = $this
            ->actingAs($farmer, 'farmer')
            ->postJson(route('farmers.api.crop-plans.preview'), [
                'crop_type_id' => $cropType->id,
                'planting_date' => '2026-04-28',
                'area_hectares' => 4,
                'farm_type' => 'IRRIGATED',
                'planting_material_type' => 'SEED',
            ]);

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.predicted_production', 86.88)
            ->assertJsonPath('data.predicted_production_formatted', '86.88 MT')
            ->assertJsonPath('data.productivity_mt_ha', 21.72)
            ->assertJsonPath('data.prediction_source', 'ml')
            ->assertJsonPath('data.prediction_source_label', 'Live ML API')
            ->assertJsonPath('data.confidence_score', 83.12)
            ->assertJsonPath('data.ml_error', null);
    }
}