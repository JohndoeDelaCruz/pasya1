<?php

namespace Tests\Feature\Admin;

use App\Models\Farmer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FarmerAccountManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_farmer_search_is_case_insensitive(): void
    {
        $admin = User::factory()->create();

        $this->createFarmer($admin, [
            'farmer_id' => '14-11-10-008-00017',
            'first_name' => 'Ruel',
            'last_name' => 'Agmaliw',
            'email' => 'ruel.agmaliw@example.com',
        ]);

        $this->createFarmer($admin, [
            'farmer_id' => '14-11-10-012-00009',
            'first_name' => 'Abner',
            'last_name' => 'Aguida',
            'email' => 'abner.aguida@example.com',
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.farmers.index', ['search' => 'agmaliw']));

        $response->assertOk();
        $response->assertSee('Ruel Agmaliw');
        $response->assertSee('14-11-10-008-00017');
        $response->assertDontSee('Abner Aguida');
    }

    public function test_account_management_filter_has_dynamic_update_hooks(): void
    {
        $admin = User::factory()->create();

        $response = $this->actingAs($admin)->get(route('admin.farmers.index'));

        $response->assertOk();
        $response->assertSee('data-auto-filter-form', false);
        $response->assertSee('data-filter-action-row', false);
        $response->assertSee('data-farmer-results', false);
    }

    private function createFarmer(User $admin, array $overrides = []): Farmer
    {
        return Farmer::create(array_merge([
            'farmer_id' => 'FMR260001',
            'first_name' => 'Test',
            'middle_name' => null,
            'last_name' => 'Farmer',
            'suffix' => null,
            'municipality' => 'BUGUIAS',
            'cooperative' => 'Test Cooperative',
            'contact_info' => null,
            'email' => 'farmer@example.com',
            'mobile_number' => '09123456789',
            'password' => 'password',
            'created_by' => $admin->id,
        ], $overrides));
    }
}
