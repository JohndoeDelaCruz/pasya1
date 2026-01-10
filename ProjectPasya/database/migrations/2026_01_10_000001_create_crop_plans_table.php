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
        // Add days_to_harvest and average_yield to crop_types table
        Schema::table('crop_types', function (Blueprint $table) {
            $table->integer('days_to_harvest')->nullable()->after('description');
            $table->decimal('average_yield_per_hectare', 10, 2)->nullable()->after('days_to_harvest');
        });

        // Create crop_plans table for farmer planting schedules
        Schema::create('crop_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farmer_id')->constrained('farmers')->onDelete('cascade');
            $table->foreignId('crop_type_id')->constrained('crop_types')->onDelete('cascade');
            $table->string('crop_name'); // Denormalized for quick access
            $table->date('planting_date');
            $table->date('expected_harvest_date');
            $table->decimal('area_hectares', 10, 2);
            $table->decimal('predicted_production', 12, 2)->nullable();
            $table->string('municipality')->nullable();
            $table->string('farm_type')->default('IRRIGATED');
            $table->enum('status', ['planned', 'planted', 'growing', 'harvested', 'cancelled'])->default('planned');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Indexes for better query performance
            $table->index(['farmer_id', 'planting_date']);
            $table->index(['farmer_id', 'expected_harvest_date']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crop_plans');
        
        Schema::table('crop_types', function (Blueprint $table) {
            $table->dropColumn(['days_to_harvest', 'average_yield_per_hectare']);
        });
    }
};
