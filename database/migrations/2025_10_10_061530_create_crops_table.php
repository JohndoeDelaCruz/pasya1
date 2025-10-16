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
        Schema::create('crops', function (Blueprint $table) {
            $table->id();
            $table->string('municipality');
            $table->string('farm_type');
            $table->integer('year');
            $table->string('month');
            $table->string('crop');
            $table->decimal('area_planted', 10, 2);
            $table->decimal('area_harvested', 10, 2);
            $table->decimal('production', 10, 2);
            $table->decimal('productivity', 10, 2);
            $table->foreignId('uploaded_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crops');
    }
};
