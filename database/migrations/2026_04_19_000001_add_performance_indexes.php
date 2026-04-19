<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds indexes on frequently queried columns for performance.
     */
    public function up(): void
    {
        Schema::table('crops', function (Blueprint $table) {
            $table->index('municipality', 'idx_crops_municipality');
            $table->index('crop', 'idx_crops_crop');
            $table->index('farm_type', 'idx_crops_farm_type');
            $table->index('year', 'idx_crops_year');
            $table->index('month', 'idx_crops_month');
            $table->index(['municipality', 'year'], 'idx_crops_municipality_year');
            $table->index(['crop', 'year'], 'idx_crops_crop_year');
            $table->index(['municipality', 'crop', 'year'], 'idx_crops_muni_crop_year');
        });

        Schema::table('farmers', function (Blueprint $table) {
            $table->index('municipality', 'idx_farmers_municipality');
        });

        Schema::table('announcements', function (Blueprint $table) {
            $table->index('municipality', 'idx_announcements_municipality');
            $table->index('is_active', 'idx_announcements_is_active');
            $table->index(['is_active', 'target_audience'], 'idx_announcements_active_audience');
        });

        Schema::table('crop_name_mappings', function (Blueprint $table) {
            $table->index('is_active', 'idx_crop_name_mappings_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('crops', function (Blueprint $table) {
            $table->dropIndex('idx_crops_municipality');
            $table->dropIndex('idx_crops_crop');
            $table->dropIndex('idx_crops_farm_type');
            $table->dropIndex('idx_crops_year');
            $table->dropIndex('idx_crops_month');
            $table->dropIndex('idx_crops_municipality_year');
            $table->dropIndex('idx_crops_crop_year');
            $table->dropIndex('idx_crops_muni_crop_year');
        });

        Schema::table('farmers', function (Blueprint $table) {
            $table->dropIndex('idx_farmers_municipality');
        });

        Schema::table('announcements', function (Blueprint $table) {
            $table->dropIndex('idx_announcements_municipality');
            $table->dropIndex('idx_announcements_is_active');
            $table->dropIndex('idx_announcements_active_audience');
        });

        Schema::table('crop_name_mappings', function (Blueprint $table) {
            $table->dropIndex('idx_crop_name_mappings_active');
        });
    }
};
