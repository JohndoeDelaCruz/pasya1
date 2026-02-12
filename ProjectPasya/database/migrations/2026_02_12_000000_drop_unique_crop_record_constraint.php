<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Drop the unique constraint on crops table to allow importing
     * all rows from CSV files exactly as they are, including duplicates.
     */
    public function up(): void
    {
        Schema::table('crops', function (Blueprint $table) {
            $table->dropUnique('unique_crop_record');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('crops', function (Blueprint $table) {
            $table->unique(
                ['municipality', 'farm_type', 'year', 'month', 'crop'],
                'unique_crop_record'
            );
        });
    }
};
