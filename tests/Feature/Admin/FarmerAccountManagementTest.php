<?php

namespace Tests\Feature\Admin;

use App\Models\Farmer;
use App\Models\User;
use App\Services\FarmerImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class FarmerAccountManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_farmer_search_is_case_insensitive(): void
    {
        $admin = $this->createAdmin();

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
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->get(route('admin.farmers.index'));

        $response->assertOk();
        $response->assertSee('data-auto-filter-form', false);
        $response->assertSee('data-filter-action-row', false);
        $response->assertSee('data-farmer-results', false);
    }

    public function test_farmer_import_reads_strawberry_farmer_workbook_layout(): void
    {
        $summary = $this->importWorkbook([
            ['No.', 'Name of Cluster Member', null, 'RSBSA/ FISHR No.'],
            [],
            [],
            ['FCA 1: BSU-Agribased Technology Business Incubator Cooperative'],
            [1, 'Abiado, Israel', null, '14-11-10-016-00550'],
            [2, 'Alilies, Septer L.', null, null],
        ]);

        $this->assertSame(2, $summary['created']);
        $this->assertSame(0, $summary['updated']);
        $this->assertSame(1, $summary['imported_missing_rsbsa']);

        $this->assertDatabaseHas('farmers', [
            'farmer_id' => '14-11-10-016-00550',
            'first_name' => 'Abiado, Israel',
            'cooperative' => 'BSU-Agribased Technology Business Incubator Cooperative',
        ]);

        $this->assertDatabaseHas('farmers', [
            'farmer_id' => null,
            'first_name' => 'Alilies, Septer L.',
            'cooperative' => 'BSU-Agribased Technology Business Incubator Cooperative',
        ]);
    }

    public function test_farmer_import_reads_reference_number_workbook_layout(): void
    {
        $rows = [
            ['MUNICIPAL REFERENCE NUMBER', 'NAME', 'BARANGAY'],
        ];

        for ($index = 1; $index <= 70; $index++) {
            $rows[] = [
                sprintf('14-11-10-001-%05d', $index),
                "PRACTICE FARMER {$index}",
                'ALAPANG',
            ];
        }

        for ($index = 71; $index <= 91; $index++) {
            $rows[] = [
                'NO REFERENCE NUMBER',
                "PRACTICE FARMER {$index}",
                'SHILAN',
            ];
        }

        $summary = $this->importWorkbook($rows);

        $this->assertSame(91, $summary['created']);
        $this->assertSame(0, $summary['updated']);
        $this->assertSame(21, $summary['imported_missing_rsbsa']);
        $this->assertSame(0, $summary['skipped_missing_name']);
        $this->assertSame(91, Farmer::count());

        $this->assertDatabaseHas('farmers', [
            'farmer_id' => '14-11-10-001-00001',
            'first_name' => 'PRACTICE FARMER 1',
            'municipality' => 'ALAPANG',
        ]);

        $this->assertDatabaseHas('farmers', [
            'farmer_id' => null,
            'first_name' => 'PRACTICE FARMER 91',
            'municipality' => 'SHILAN',
        ]);
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

    private function importWorkbook(array $rows): array
    {
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getActiveSheet()->fromArray($rows);

        $path = tempnam(sys_get_temp_dir(), 'farmers_import_') . '.xlsx';

        try {
            (new Xlsx($spreadsheet))->save($path);

            return app(FarmerImportService::class)->import($path);
        } finally {
            $spreadsheet->disconnectWorksheets();

            if (is_file($path)) {
                unlink($path);
            }
        }
    }

    private function createAdmin(): User
    {
        return User::factory()->create([
            'role' => User::ROLE_DA_ADMIN,
            'is_active' => true,
        ]);
    }
}
