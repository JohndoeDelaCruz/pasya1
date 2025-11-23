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
            // Add unique constraint to prevent duplicate crop type names
            $table->unique('name', 'unique_crop_type_name');
        });

        Schema::table('municipalities', function (Blueprint $table) {
            // Add unique constraint to prevent duplicate municipality names
            $table->unique('name', 'unique_municipality_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('crop_types', function (Blueprint $table) {
            $table->dropUnique('unique_crop_type_name');
        });

        Schema::table('municipalities', function (Blueprint $table) {
            $table->dropUnique('unique_municipality_name');
        });
    }
};
