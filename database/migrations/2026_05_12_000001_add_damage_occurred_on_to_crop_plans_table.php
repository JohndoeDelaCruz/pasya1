<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('crop_plans', function (Blueprint $table) {
            $table->date('damage_occurred_on')->nullable()->after('damage_notes');
            $table->index('damage_occurred_on');
        });
    }

    public function down(): void
    {
        Schema::table('crop_plans', function (Blueprint $table) {
            $table->dropIndex(['damage_occurred_on']);
            $table->dropColumn('damage_occurred_on');
        });
    }
};
