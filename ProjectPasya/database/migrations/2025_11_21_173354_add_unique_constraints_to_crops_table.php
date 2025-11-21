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
        Schema::table('crops', function (Blueprint $table) {
            // Add composite unique constraint to prevent duplicate crop records
            // Same municipality, farm_type, year, month, and crop should not exist more than once
            $table->unique(
                ['municipality', 'farm_type', 'year', 'month', 'crop'],
                'unique_crop_record'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('crops', function (Blueprint $table) {
            $table->dropUnique('unique_crop_record');
        });
    }
};
