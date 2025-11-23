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
        // Remove duplicate crop types (keep the one with spaces, remove without spaces)
        $duplicatePairs = [
            ['CHINESECABBAGE', 'CHINESE CABBAGE'],
            ['GARDENPEAS', 'GARDEN PEAS'],
            ['SNAPBEANS', 'SNAP BEANS'],
            ['SWEETPEPPER', 'SWEET PEPPER'],
        ];

        foreach ($duplicatePairs as [$without_space, $with_space]) {
            // Check if both exist
            $withSpace = DB::table('crop_types')->where('name', $with_space)->first();
            $withoutSpace = DB::table('crop_types')->where('name', $without_space)->first();

            if ($withSpace && $withoutSpace) {
                // Delete the one without space
                DB::table('crop_types')->where('name', $without_space)->delete();
            }
        }

        // Remove any other duplicates by keeping only the first occurrence
        DB::statement("
            DELETE ct1 FROM crop_types ct1
            INNER JOIN crop_types ct2 
            WHERE 
                REPLACE(ct1.name, ' ', '') = REPLACE(ct2.name, ' ', '')
                AND ct1.id > ct2.id
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Cannot restore deleted duplicates
    }
};
