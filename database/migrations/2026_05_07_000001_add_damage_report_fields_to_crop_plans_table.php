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
        Schema::table('crop_plans', function (Blueprint $table) {
            $table->decimal('damaged_area_hectares', 10, 2)->nullable()->after('area_hectares');
            $table->string('damage_cause')->nullable()->after('status');
            $table->text('damage_notes')->nullable()->after('damage_cause');
            $table->timestamp('damage_reported_at')->nullable()->after('damage_notes');

            $table->index('damage_reported_at');
            $table->index('damage_cause');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('crop_plans', function (Blueprint $table) {
            $table->dropIndex(['damage_reported_at']);
            $table->dropIndex(['damage_cause']);
            $table->dropColumn([
                'damaged_area_hectares',
                'damage_cause',
                'damage_notes',
                'damage_reported_at',
            ]);
        });
    }
};