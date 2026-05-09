<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('crop_plans', function (Blueprint $table) {
            $table->date('actual_harvest_date')->nullable()->after('expected_harvest_date');
        });
    }

    public function down(): void
    {
        Schema::table('crop_plans', function (Blueprint $table) {
            $table->dropColumn('actual_harvest_date');
        });
    }
};
