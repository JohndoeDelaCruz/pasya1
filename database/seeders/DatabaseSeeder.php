<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Call the seeders
        $this->call([
            AdminUserSeeder::class,
            // CropTypeSeeder removed - crop types are managed by admin
            CropPlanSeeder::class, // Demo data for harvest history presentation
        ]);

        // Optionally create additional test users for development
        // User::factory(10)->create();
    }
}
