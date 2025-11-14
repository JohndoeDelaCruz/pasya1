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
        Schema::create('subsidies', function (Blueprint $table) {
            $table->id();
            $table->string('full_name');
            $table->string('farmer_id')->unique();
            $table->string('crop');
            $table->enum('subsidy_status', ['Approved', 'Pending', 'Rejected'])->default('Pending');
            $table->decimal('subsidy_amount', 10, 2)->nullable();
            $table->string('municipality')->nullable();
            $table->string('farm_type')->nullable();
            $table->integer('year')->nullable();
            $table->decimal('area_planted', 10, 2)->nullable();
            $table->decimal('area_harvested', 10, 2)->nullable();
            $table->decimal('production', 10, 2)->nullable();
            $table->decimal('productivity', 10, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subsidies');
    }
};
