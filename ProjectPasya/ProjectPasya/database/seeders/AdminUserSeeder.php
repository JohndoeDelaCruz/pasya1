<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'DA Admin',
            'username' => 'DAadmin',
            'email' => 'DAadmin@gmail.com',
            'email_verified_at' => now(),
            'password' => Hash::make('admin123'),
        ]);

        $this->command->info('Admin user created successfully!');
        $this->command->info('Username: DAadmin');
        $this->command->info('Email: DAadmin@gmail.com');
        $this->command->info('Password: admin123');
    }
}
