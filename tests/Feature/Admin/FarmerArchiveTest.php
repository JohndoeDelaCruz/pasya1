<?php

namespace Tests\Feature\Admin;

use App\Models\Farmer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FarmerArchiveTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_archive_a_farmer_account(): void
    {
        $admin = User::factory()->create();
        $farmer = Farmer::create([
            'farmer_id' => 'FMR250001',
            'first_name' => 'Ana',
            'middle_name' => null,
            'last_name' => 'Dela Cruz',
            'suffix' => null,
            'municipality' => 'BUGUIAS',
            'cooperative' => null,
            'contact_info' => null,
            'email' => 'ana@example.com',
            'mobile_number' => '09123456789',
            'password' => 'password',
            'created_by' => $admin->id,
        ]);

        $response = $this->actingAs($admin)
            ->from(route('admin.farmers.index'))
            ->delete(route('admin.farmers.destroy', $farmer));

        $response->assertRedirect(route('admin.farmers.index'));
        $response->assertSessionHas('success', 'Farmer account archived successfully!');
        $this->assertSoftDeleted('farmers', ['id' => $farmer->id]);
    }

    public function test_archived_farmer_account_blocks_web_user_bridge_login(): void
    {
        $user = User::factory()->create([
            'email' => 'archived@example.com',
        ]);

        $farmer = Farmer::create([
            'farmer_id' => 'FMR250002',
            'first_name' => 'Archived',
            'middle_name' => null,
            'last_name' => 'Farmer',
            'suffix' => null,
            'municipality' => 'LA TRINIDAD',
            'cooperative' => null,
            'contact_info' => null,
            'email' => $user->email,
            'mobile_number' => '09999999999',
            'password' => 'password',
            'created_by' => null,
        ]);

        $farmer->delete();

        $response = $this->from('/login')->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors([
            'email' => 'This farmer account has been archived. Please contact an administrator.',
        ]);
        $this->assertGuest('web');
        $this->assertGuest('farmer');
    }
}