<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminEmail = trim((string) config('app.admin_email'));
        $adminUsername = trim((string) config('app.admin_username'));
        $adminPassword = (string) config('app.admin_password');

        if ($adminEmail === '' || $adminUsername === '' || $adminPassword === '') {
            throw new RuntimeException('ADMIN_EMAIL, ADMIN_USERNAME, and ADMIN_PASSWORD must be set before seeding the admin user.');
        }

        $adminUser = User::updateOrCreate([
            'email' => $adminEmail,
        ], [
            'name' => config('app.admin_name', 'PASYA Admin'),
            'username' => $adminUsername,
            'email' => $adminEmail,
            'email_verified_at' => now(),
            'password' => Hash::make($adminPassword),
        ]);

        if ($adminUser->wasRecentlyCreated) {
            $this->command->info('Admin user created successfully!');
        } else {
            $this->command->info('Admin user already existed and was updated.');
        }

        $this->command->info('Username: '.$adminUsername);
        $this->command->info('Email: '.$adminEmail);
        $this->command->info('Password: configured via ADMIN_PASSWORD');
    }
}
