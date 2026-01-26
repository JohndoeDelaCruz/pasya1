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
        
        // Sample crop plans with different maturity statuses for presentation
        $cropPlans = [
            // 1. Overdue harvest (planted 100 days ago, should have been harvested 10 days ago)
            [
                'crop_name' => 'Cabbage',
                'planting_date' => $today->copy()->subDays(100),
                'expected_harvest_date' => $today->copy()->subDays(10),
                'area_hectares' => 0.5,
                'predicted_production' => 12.50,
                'status' => 'growing',
                'notes' => 'Overdue for harvest - ready for demo',
            ],
            // 2. Ready to harvest (1-3 days left)
            [
                'crop_name' => 'Carrots',
                'planting_date' => $today->copy()->subDays(73),
                'expected_harvest_date' => $today->copy()->addDays(2),
                'area_hectares' => 0.3,
                'predicted_production' => 5.40,
                'status' => 'growing',
                'notes' => 'Ready to harvest soon',
            ],
            // 3. Almost ready (4-7 days left)
            [
                'crop_name' => 'Lettuce',
                'planting_date' => $today->copy()->subDays(40),
                'expected_harvest_date' => $today->copy()->addDays(5),
                'area_hectares' => 0.25,
                'predicted_production' => 3.75,
                'status' => 'growing',
                'notes' => 'Almost ready for harvest',
            ],
            // 4. Approaching harvest (8-14 days left)
            [
                'crop_name' => 'BROCCOLI',
                'planting_date' => $today->copy()->subDays(70),
                'expected_harvest_date' => $today->copy()->addDays(10),
                'area_hectares' => 0.4,
                'predicted_production' => 4.80,
                'status' => 'growing',
                'notes' => 'Approaching harvest date',
            ],
            // 5. Still growing (more than 14 days left)
            [
                'crop_name' => 'WHITEPOTATO',
                'planting_date' => $today->copy()->subDays(30),
                'expected_harvest_date' => $today->copy()->addDays(70),
                'area_hectares' => 0.6,
                'predicted_production' => 9.00,
                'status' => 'growing',
                'notes' => 'Still in growing phase',
            ],
            // 6. Completed harvest (for reference)
            [
                'crop_name' => 'Snap Beans',
                'planting_date' => $today->copy()->subDays(70),
                'expected_harvest_date' => $today->copy()->subDays(15),
                'area_hectares' => 0.2,
                'predicted_production' => 2.40,
                'status' => 'harvested',
                'notes' => 'Successfully harvested',
            ],
            // 7. Another completed harvest
            [
                'crop_name' => 'Chinese Cabbage',
                'planting_date' => $today->copy()->subDays(80),
                'expected_harvest_date' => $today->copy()->subDays(20),
                'area_hectares' => 0.35,
                'predicted_production' => 7.00,
                'status' => 'harvested',
                'notes' => 'Harvest completed last month',
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
