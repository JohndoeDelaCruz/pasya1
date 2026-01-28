<?php

namespace Database\Seeders;

use App\Models\CropPlan;
use App\Models\CropType;
use App\Models\Farmer;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class CropPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Seeds sample crop plans for harvest history presentation
     */
    public function run(): void
    {
        // Get the first farmer or create a demo farmer
        $farmer = Farmer::first();
        
        if (!$farmer) {
            $this->command->warn('No farmer found. Please create a farmer first or run this after farmer registration.');
            return;
        }

        // Get crop types
        $cropTypes = CropType::active()->get()->keyBy('name');
        
        if ($cropTypes->isEmpty()) {
            $this->command->warn('No crop types found. Please run CropTypeSeeder first.');
            return;
        }

        $today = Carbon::now();
        
        // Sample crop plans with different crops and years for presentation
        $cropPlans = [
            // === 2024 Harvests ===
            [
                'crop_name' => 'Cabbage',
                'planting_date' => Carbon::create(2024, 3, 15),
                'expected_harvest_date' => Carbon::create(2024, 6, 15),
                'area_hectares' => 0.5,
                'predicted_production' => 12.50,
                'status' => 'harvested',
                'notes' => 'Good harvest season 2024',
            ],
            [
                'crop_name' => 'Carrots',
                'planting_date' => Carbon::create(2024, 5, 10),
                'expected_harvest_date' => Carbon::create(2024, 8, 5),
                'area_hectares' => 0.4,
                'predicted_production' => 7.20,
                'status' => 'harvested',
                'notes' => 'Summer 2024 batch',
            ],
            [
                'crop_name' => 'WHITEPOTATO',
                'planting_date' => Carbon::create(2024, 7, 1),
                'expected_harvest_date' => Carbon::create(2024, 10, 20),
                'area_hectares' => 0.6,
                'predicted_production' => 9.00,
                'status' => 'harvested',
                'notes' => 'Rainy season potato harvest',
            ],
            [
                'crop_name' => 'Lettuce',
                'planting_date' => Carbon::create(2024, 9, 5),
                'expected_harvest_date' => Carbon::create(2024, 10, 25),
                'area_hectares' => 0.25,
                'predicted_production' => 3.75,
                'status' => 'harvested',
                'notes' => 'Late 2024 lettuce batch',
            ],
            [
                'crop_name' => 'Bell Pepper',
                'planting_date' => Carbon::create(2024, 8, 20),
                'expected_harvest_date' => Carbon::create(2024, 11, 15),
                'area_hectares' => 0.3,
                'predicted_production' => 4.50,
                'status' => 'harvested',
                'notes' => 'Bell pepper 2024',
            ],
            
            // === 2025 Harvests ===
            [
                'crop_name' => 'Chinese Cabbage',
                'planting_date' => Carbon::create(2025, 1, 10),
                'expected_harvest_date' => Carbon::create(2025, 3, 20),
                'area_hectares' => 0.35,
                'predicted_production' => 7.00,
                'status' => 'harvested',
                'notes' => 'Early 2025 pechay batch',
            ],
            [
                'crop_name' => 'BROCCOLI',
                'planting_date' => Carbon::create(2025, 2, 1),
                'expected_harvest_date' => Carbon::create(2025, 4, 25),
                'area_hectares' => 0.4,
                'predicted_production' => 4.80,
                'status' => 'harvested',
                'notes' => 'Q1 2025 broccoli',
            ],
            [
                'crop_name' => 'Tomatoes',
                'planting_date' => Carbon::create(2025, 3, 15),
                'expected_harvest_date' => Carbon::create(2025, 6, 10),
                'area_hectares' => 0.45,
                'predicted_production' => 8.10,
                'status' => 'harvested',
                'notes' => 'Spring 2025 tomatoes',
            ],
            [
                'crop_name' => 'Snap Beans',
                'planting_date' => Carbon::create(2025, 5, 20),
                'expected_harvest_date' => Carbon::create(2025, 7, 25),
                'area_hectares' => 0.2,
                'predicted_production' => 2.40,
                'status' => 'harvested',
                'notes' => 'Mid-year beans 2025',
            ],
            [
                'crop_name' => 'Sayote',
                'planting_date' => Carbon::create(2025, 4, 1),
                'expected_harvest_date' => Carbon::create(2025, 8, 1),
                'area_hectares' => 0.5,
                'predicted_production' => 15.00,
                'status' => 'harvested',
                'notes' => 'Sayote 2025 - good yield',
            ],
            [
                'crop_name' => 'Cauliflower',
                'planting_date' => Carbon::create(2025, 7, 10),
                'expected_harvest_date' => Carbon::create(2025, 10, 5),
                'area_hectares' => 0.35,
                'predicted_production' => 5.25,
                'status' => 'harvested',
                'notes' => 'Fall 2025 cauliflower',
            ],
            [
                'crop_name' => 'Radish',
                'planting_date' => Carbon::create(2025, 9, 1),
                'expected_harvest_date' => Carbon::create(2025, 10, 5),
                'area_hectares' => 0.15,
                'predicted_production' => 2.70,
                'status' => 'harvested',
                'notes' => 'Quick harvest radish 2025',
            ],
            [
                'crop_name' => 'Celery',
                'planting_date' => Carbon::create(2025, 8, 15),
                'expected_harvest_date' => Carbon::create(2025, 11, 25),
                'area_hectares' => 0.2,
                'predicted_production' => 3.00,
                'status' => 'harvested',
                'notes' => 'Celery batch 2025',
            ],
            
            // === 2026 (Current Year) ===
            // Completed harvests
            [
                'crop_name' => 'BROCCOLI',
                'planting_date' => Carbon::create(2025, 10, 23),
                'expected_harvest_date' => Carbon::create(2026, 1, 13),
                'area_hectares' => 0.4,
                'predicted_production' => 4.80,
                'status' => 'harvested',
                'notes' => 'January 2026 harvest complete',
            ],
            [
                'crop_name' => 'Chinese Cabbage',
                'planting_date' => Carbon::create(2025, 10, 22),
                'expected_harvest_date' => Carbon::create(2026, 1, 23),
                'area_hectares' => 0.35,
                'predicted_production' => 7.00,
                'status' => 'harvested',
                'notes' => 'Just harvested this week',
            ],
            
            // Currently growing crops
            [
                'crop_name' => 'Carrots',
                'planting_date' => Carbon::create(2025, 12, 1),
                'expected_harvest_date' => Carbon::create(2026, 2, 25),
                'area_hectares' => 0.3,
                'predicted_production' => 5.40,
                'status' => 'growing',
                'notes' => 'Approaching harvest',
            ],
            [
                'crop_name' => 'Cabbage',
                'planting_date' => Carbon::create(2025, 12, 15),
                'expected_harvest_date' => Carbon::create(2026, 3, 15),
                'area_hectares' => 0.5,
                'predicted_production' => 12.50,
                'status' => 'growing',
                'notes' => 'Growing well',
            ],
            [
                'crop_name' => 'WHITEPOTATO',
                'planting_date' => Carbon::create(2026, 1, 5),
                'expected_harvest_date' => Carbon::create(2026, 4, 20),
                'area_hectares' => 0.6,
                'predicted_production' => 9.00,
                'status' => 'growing',
                'notes' => 'New year potato planting',
            ],
        ];

        foreach ($cropPlans as $planData) {
            // Find the crop type
            $cropType = $cropTypes->get($planData['crop_name']);
            
            if (!$cropType) {
                $this->command->warn("Crop type '{$planData['crop_name']}' not found, skipping...");
                continue;
            }

            // Check if this plan already exists (to avoid duplicates on re-run)
            $existingPlan = CropPlan::where('farmer_id', $farmer->id)
                ->where('crop_name', $planData['crop_name'])
                ->where('planting_date', $planData['planting_date'])
                ->first();

            if ($existingPlan) {
                $this->command->info("Crop plan for {$planData['crop_name']} already exists, skipping...");
                continue;
            }

            CropPlan::create([
                'farmer_id' => $farmer->id,
                'crop_type_id' => $cropType->id,
                'crop_name' => $planData['crop_name'],
                'planting_date' => $planData['planting_date'],
                'expected_harvest_date' => $planData['expected_harvest_date'],
                'area_hectares' => $planData['area_hectares'],
                'predicted_production' => $planData['predicted_production'],
                'municipality' => $farmer->municipality ?? 'BUGUIAS',
                'farm_type' => 'highland',
                'status' => $planData['status'],
                'notes' => $planData['notes'],
            ]);

            $this->command->info("Created crop plan: {$planData['crop_name']} ({$planData['status']})");
        }

        $this->command->info('Crop plan seeding completed!');
    }
}
