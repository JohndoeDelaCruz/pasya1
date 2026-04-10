<?php

namespace App\Services;

use App\Models\Farmer;
use App\Models\User;
use Illuminate\Support\Str;

class FarmerAccountBridgeService
{
    public function findOrCreateForUser(User $user): Farmer
    {
        $existingFarmer = Farmer::where('email', $user->email)->first();

        if ($existingFarmer) {
            return $existingFarmer;
        }

        [$firstName, $middleName, $lastName] = $this->splitName($user->name ?? '');

        return Farmer::create([
            'farmer_id' => $this->generateUniqueFarmerId($firstName, $lastName),
            'first_name' => $firstName,
            'middle_name' => $middleName,
            'last_name' => $lastName,
            'suffix' => null,
            'municipality' => 'BUGUIAS',
            'cooperative' => null,
            'contact_info' => $user->email,
            'email' => $user->email,
            'mobile_number' => 'N/A',
            // Reuse the already-hashed password from the web user account.
            'password' => $user->password,
            'created_by' => null,
        ]);
    }

    private function splitName(string $name): array
    {
        $parts = preg_split('/\s+/', trim($name)) ?: [];
        $parts = array_values(array_filter($parts, fn ($part) => $part !== ''));

        if ($parts === []) {
            return ['Farmer', null, 'User'];
        }

        if (count($parts) === 1) {
            return [$parts[0], null, 'User'];
        }

        $firstName = array_shift($parts);
        $lastName = array_pop($parts);
        $middleName = $parts !== [] ? implode(' ', $parts) : null;

        return [$firstName, $middleName, $lastName];
    }

    private function generateUniqueFarmerId(string $firstName, string $lastName): string
    {
        $initials = strtoupper(Str::substr(preg_replace('/[^A-Za-z]/', '', $firstName), 0, 2)
            . Str::substr(preg_replace('/[^A-Za-z]/', '', $lastName), 0, 2));

        if ($initials === '') {
            $initials = 'FMR';
        }

        $year = now()->format('y');

        for ($attempt = 0; $attempt < 20; $attempt++) {
            $suffix = str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
            $candidate = $initials . $year . $suffix;

            if (! Farmer::where('farmer_id', $candidate)->exists()) {
                return $candidate;
            }
        }

        do {
            $candidate = 'FMR' . strtoupper(Str::random(8));
        } while (Farmer::where('farmer_id', $candidate)->exists());

        return $candidate;
    }
}
