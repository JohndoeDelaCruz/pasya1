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
        // Keep the newest record in each duplicate group in a database-agnostic way.
        DB::table('crops')
            ->whereNotIn('id', function ($query) {
                $query->from('crops')
                    ->selectRaw('MAX(id)')
                    ->groupBy('municipality', 'farm_type', 'year', 'month', 'crop');
            })
            ->delete();
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
