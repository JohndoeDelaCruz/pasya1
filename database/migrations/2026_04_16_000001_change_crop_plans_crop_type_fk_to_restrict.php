<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Change crop_type_id foreign key from CASCADE to RESTRICT
     * to prevent accidental deletion of crop plans when an admin
     * deletes a crop type.
     */
    public function up(): void
    {
        Schema::table('crop_plans', function (Blueprint $table) {
            $table->dropForeign(['crop_type_id']);
            $table->foreign('crop_type_id')
                ->references('id')
                ->on('crop_types')
                ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('crop_plans', function (Blueprint $table) {
            $table->dropForeign(['crop_type_id']);
            $table->foreign('crop_type_id')
                ->references('id')
                ->on('crop_types')
                ->onDelete('cascade');
        });
    }
};
