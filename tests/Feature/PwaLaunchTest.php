<?php

namespace Tests\Feature;

use App\Models\Farmer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PwaLaunchTest extends TestCase
{
    use RefreshDatabase;

    public function test_manifest_starts_from_the_public_launch_route(): void
    {
        $manifest = json_decode(file_get_contents(public_path('manifest.json')), true);

        $this->assertSame('/app', $manifest['start_url']);
    }

    public function test_pwa_launch_sends_guests_to_the_public_homepage(): void
    {
        $this->get('/app')
            ->assertRedirect('/');
    }

    public function test_pwa_launch_sends_farmers_to_the_farmer_dashboard(): void
    {
        $farmer = Farmer::create([
            'farmer_id' => 'F-001',
            'first_name' => 'Ana',
            'last_name' => 'Santos',
            'municipality' => 'La Trinidad',
            'mobile_number' => '09171234567',
            'password' => 'password',
        ]);

        $this->actingAs($farmer, 'farmer')
            ->get('/app')
            ->assertRedirect(route('farmers.dashboard', absolute: false));
    }

    public function test_pwa_launch_sends_web_users_through_the_dashboard_bridge(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/app')
            ->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_pwa_launch_sends_admin_users_to_the_admin_dashboard(): void
    {
        config(['app.admin_email' => 'admin@example.test']);

        $admin = User::factory()->create([
            'email' => 'admin@example.test',
            'role' => User::ROLE_DA_ADMIN,
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->get('/app')
            ->assertRedirect(route('admin.dashboard', absolute: false));
    }

    public function test_pwa_launch_sends_lgu_validators_to_the_lgu_dashboard(): void
    {
        $validator = User::factory()->create([
            'role' => User::ROLE_LGU_VALIDATOR,
            'municipality' => 'BUGUIAS',
            'is_active' => true,
        ]);

        $this->actingAs($validator)
            ->get('/app')
            ->assertRedirect(route('lgu.dashboard', absolute: false));
    }
}
