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
        Schema::create('crop_name_mappings', function (Blueprint $table) {
            $table->id();
            $table->string('database_name')->unique()->comment('Name as stored in crops table');
            $table->string('ml_name')->comment('Name expected by ML API');
            $table->boolean('is_active')->default(true)->comment('Enable/disable mapping');
            $table->text('notes')->nullable()->comment('Notes about this mapping');
            $table->timestamps();
            
            // Indexes for performance
            $table->index('database_name');
            $table->index(['database_name', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crop_name_mappings');
    }
};
