<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();
        $hasCropPlanDamageColumn = Schema::hasColumn('crop_plans', 'damaged_area_hectares');
        $hasDamageReportDamageColumn = Schema::hasColumn('crop_plan_damage_reports', 'damaged_area_hectares');

        if ($driver === 'pgsql') {
            if ($hasCropPlanDamageColumn) {
                DB::statement('ALTER TABLE crop_plans ALTER COLUMN damaged_area_hectares TYPE NUMERIC(12, 4)');
            }

            if ($hasDamageReportDamageColumn) {
                DB::statement('ALTER TABLE crop_plan_damage_reports ALTER COLUMN damaged_area_hectares TYPE NUMERIC(12, 4)');
            }

            return;
        }

        if ($driver === 'mysql') {
            if ($hasCropPlanDamageColumn) {
                DB::statement('ALTER TABLE crop_plans MODIFY damaged_area_hectares DECIMAL(12, 4) NULL');
            }

            if ($hasDamageReportDamageColumn) {
                DB::statement('ALTER TABLE crop_plan_damage_reports MODIFY damaged_area_hectares DECIMAL(12, 4) NOT NULL');
            }
        }
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();
        $hasCropPlanDamageColumn = Schema::hasColumn('crop_plans', 'damaged_area_hectares');
        $hasDamageReportDamageColumn = Schema::hasColumn('crop_plan_damage_reports', 'damaged_area_hectares');

        if ($driver === 'pgsql') {
            if ($hasCropPlanDamageColumn) {
                DB::statement('ALTER TABLE crop_plans ALTER COLUMN damaged_area_hectares TYPE NUMERIC(10, 2)');
            }

            if ($hasDamageReportDamageColumn) {
                DB::statement('ALTER TABLE crop_plan_damage_reports ALTER COLUMN damaged_area_hectares TYPE NUMERIC(10, 2)');
            }

            return;
        }

        if ($driver === 'mysql') {
            if ($hasCropPlanDamageColumn) {
                DB::statement('ALTER TABLE crop_plans MODIFY damaged_area_hectares DECIMAL(10, 2) NULL');
            }

            if ($hasDamageReportDamageColumn) {
                DB::statement('ALTER TABLE crop_plan_damage_reports MODIFY damaged_area_hectares DECIMAL(10, 2) NOT NULL');
            }
        }
    }
};
