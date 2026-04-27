<?php

namespace Database\Seeders;

use App\Models\Municipality;
use Illuminate\Database\Seeder;

class MunicipalitySeeder extends Seeder
{
    /**
     * Seed the Benguet municipalities used throughout PASYA.
     */
    public function run(): void
    {
        foreach (Municipality::BENGUET_MUNICIPALITIES as $name) {
            Municipality::updateOrCreate(
                ['name' => $name],
                [
                    'province' => 'Benguet',
                    'description' => 'Benguet municipality',
                    'is_active' => true,
                ]
            );
        }

        $this->command->info('Benguet municipalities seeded successfully.');
    }
}
