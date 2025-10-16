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
        // Call the AdminUserSeeder to create the admin account
        $this->call([
            AdminUserSeeder::class,
        ]);

        // Optionally create additional test users for development
        // User::factory(10)->create();
    }
}
