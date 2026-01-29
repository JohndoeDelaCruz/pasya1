<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Remove duplicate crop records, keeping only the most recent one (highest ID)
        // This query deletes duplicates where the same municipality, farm_type, year, month, and crop exist multiple times
        DB::statement("
            DELETE c1 FROM crops c1
            INNER JOIN crops c2 
            WHERE 
                c1.municipality = c2.municipality 
                AND c1.farm_type = c2.farm_type 
                AND c1.year = c2.year 
                AND c1.month = c2.month 
                AND c1.crop = c2.crop 
                AND c1.id < c2.id
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Cannot restore deleted duplicate records
        // This migration is irreversible
    }
};
