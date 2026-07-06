<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('crop_plan_damage_reports', function (Blueprint $table) {
            $table->string('typhoon_name')->nullable()->after('damage_cause');
        });
    }

    public function down(): void
    {
        Schema::table('crop_plan_damage_reports', function (Blueprint $table) {
            $table->dropColumn('typhoon_name');
        });
    }
};
