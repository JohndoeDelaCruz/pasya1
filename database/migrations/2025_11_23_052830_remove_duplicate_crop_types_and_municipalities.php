<?php

use Illuminate\Database\Migrations\Migration;
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

        // Remove any other duplicates by keeping only the first normalized occurrence.
        DB::table('crop_types')
            ->whereNotIn('id', function ($query) {
                $query->from('crop_types')
                    ->selectRaw('MIN(id)')
                    ->groupBy(DB::raw("REPLACE(name, ' ', '')"));
            })
            ->delete();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Cannot restore deleted duplicates
    }
};
