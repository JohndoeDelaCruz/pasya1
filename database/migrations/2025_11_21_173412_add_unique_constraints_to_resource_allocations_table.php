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
        Schema::table('resource_allocations', function (Blueprint $table) {
            // Add unique constraint to prevent duplicate resource allocations
            // for the same resource type and municipality
            $table->unique(
                ['resource_type', 'municipality'],
                'unique_resource_allocation'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('resource_allocations', function (Blueprint $table) {
            // Drop the unique constraint when rolling back
            $table->dropUnique('unique_resource_allocation');
        });
    }
};
