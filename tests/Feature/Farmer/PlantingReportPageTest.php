<?php

namespace Tests\Feature\Farmer;

use App\Models\Farmer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlantingReportPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_farmer_can_view_planting_report_page(): void
    {
        $farmer = Farmer::create([
            'farmer_id' => 'FMR250100',
            'first_name' => 'Planting',
            'middle_name' => null,
            'last_name' => 'Tester',
            'suffix' => null,
            'municipality' => 'BUGUIAS',
            'cooperative' => null,
            'contact_info' => null,
            'email' => 'planting-report@example.com',
            'mobile_number' => '09123456780',
            'password' => bcrypt('password'),
            'created_by' => null,
        ]);

        $response = $this->actingAs($farmer, 'farmer')
            ->get(route('farmers.planting-report'));

        $response->assertOk();
        $response->assertSee('Planting Report');
    }
}