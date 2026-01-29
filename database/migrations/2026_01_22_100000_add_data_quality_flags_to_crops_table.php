<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Add is_imputed flag and data_quality_score to crops table
     * 
     * This migration addresses data quality issues identified in the analysis:
     * - 49.5% of records appear to be median-imputed placeholders
     * - Pattern: Area=5, Production=55, Productivity=11
     * 
     * The is_imputed flag helps distinguish genuine vs placeholder data
     * The data_quality_score provides a numeric quality indicator (0-100)
     */
    public function up(): void
    {
        // Add new columns
        Schema::table('crops', function (Blueprint $table) {
            $table->boolean('is_imputed')->default(false)->after('productivity')
                  ->comment('True if record appears to be median-imputed placeholder data');
            $table->tinyInteger('data_quality_score')->default(100)->after('is_imputed')
                  ->comment('Data quality score 0-100, lower = more suspicious');
        });

        // Update existing records to flag likely imputed data
        // Pattern: Area ≈ 5, Production ≈ 55, Productivity ≈ 11
        DB::statement("
            UPDATE crops 
            SET is_imputed = 1,
                data_quality_score = CASE
                    WHEN ABS(area_harvested - 5) < 0.01 
                         AND ABS(production - 55) < 0.01 
                         AND ABS(productivity - 11) < 0.01 
                    THEN 10  -- All three median values = very likely imputed
                    WHEN ABS(area_harvested - 5) < 0.01 AND ABS(production - 55) < 0.01 
                    THEN 20  -- Area and Production median
                    WHEN ABS(productivity - 11) < 0.5 
                    THEN 40  -- Only productivity is median (within tolerance)
                    ELSE 60  -- Some median values present
                END
            WHERE (ABS(area_harvested - 5) < 0.01 AND ABS(production - 55) < 0.01)
               OR (ABS(area_harvested - 5) < 0.01 AND ABS(productivity - 11) < 0.01)
               OR (ABS(production - 55) < 0.01 AND ABS(productivity - 11) < 0.01)
        ");

        // Flag records with suspicious productivity (outside realistic range)
        DB::statement("
            UPDATE crops 
            SET data_quality_score = LEAST(data_quality_score, 30)
            WHERE productivity <= 0.5 OR productivity > 100
        ");

        // Flag records where calculated productivity differs from stored
        DB::statement("
            UPDATE crops 
            SET data_quality_score = LEAST(data_quality_score, 50)
            WHERE area_harvested > 0 
              AND ABS(productivity - (production / area_harvested)) > 0.5
              AND is_imputed = 0
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('crops', function (Blueprint $table) {
            $table->dropColumn(['is_imputed', 'data_quality_score']);
        });
    }
};
