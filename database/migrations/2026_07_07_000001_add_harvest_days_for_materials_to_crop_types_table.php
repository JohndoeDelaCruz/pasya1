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
            $table->integer('days_to_harvest_seed')->nullable()->after('days_to_harvest');
            $table->integer('days_to_harvest_seedling')->nullable()->after('days_to_harvest_seed');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('crop_types', function (Blueprint $table) {
            $table->dropColumn(['days_to_harvest_seed', 'days_to_harvest_seedling']);
        });
    }
};
