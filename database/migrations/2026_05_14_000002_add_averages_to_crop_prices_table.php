<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('crop_prices', function (Blueprint $table) {
            $table->decimal('weekly_average', 10, 2)->nullable()->after('previous_price');
            $table->decimal('monthly_average', 10, 2)->nullable()->after('weekly_average');
            $table->decimal('last_year_price', 10, 2)->nullable()->after('monthly_average');
        });
    }

    public function down(): void
    {
        Schema::table('crop_prices', function (Blueprint $table) {
            $table->dropColumn(['weekly_average', 'monthly_average', 'last_year_price']);
        });
    }
};
