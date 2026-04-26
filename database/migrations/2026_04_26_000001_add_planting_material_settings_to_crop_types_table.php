<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('crop_types', function (Blueprint $table) {
            $table->integer('seedling_days')->nullable()->after('average_yield_per_hectare');
            $table->boolean('supports_seed_material')->nullable()->after('seedling_days');
            $table->boolean('supports_seedling_material')->nullable()->after('supports_seed_material');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('crop_types', function (Blueprint $table) {
            $table->dropColumn([
                'seedling_days',
                'supports_seed_material',
                'supports_seedling_material',
            ]);
        });
    }
};