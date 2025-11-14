<?php

namespace Database\Seeders;

use App\Models\Subsidy;
use Illuminate\Database\Seeder;

class SubsidySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $subsidies = [
            [
                'full_name' => 'Maria Angelica M. Torres',
                'farmer_id' => 'ID-COOP-0108',
                'crop' => 'Cabbage',
                'subsidy_status' => 'Approved',
                'subsidy_amount' => 5000.00,
                'municipality' => 'ATOK',
                'farm_type' => 'Irrigated',
                'year' => 2025,
                'area_planted' => 15.5,
                'area_harvested' => 15.0,
                'production' => 450.75,
                'productivity' => 30.05,
            ],
            [
                'full_name' => 'Juan D. Cruz',
                'farmer_id' => 'ID-COOP-0234',
                'crop' => 'Broccoli',
                'subsidy_status' => 'Pending',
                'subsidy_amount' => 3000.00,
                'municipality' => 'BAKUN',
                'farm_type' => 'Rainfed',
                'year' => 2025,
                'area_planted' => 10.2,
                'area_harvested' => 9.8,
                'production' => 280.50,
                'productivity' => 28.62,
            ],
        ];

        foreach ($subsidies as $subsidy) {
            Subsidy::create($subsidy);
        }
    }
}
