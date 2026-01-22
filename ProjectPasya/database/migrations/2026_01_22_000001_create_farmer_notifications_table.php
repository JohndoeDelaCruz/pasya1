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
        Schema::create('farmer_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farmer_id')->constrained('farmers')->onDelete('cascade');
            $table->string('type'); // crop_plan, harvest_reminder, planting_reminder, announcement, price_alert
            $table->string('title');
            $table->text('message');
            $table->string('icon')->nullable(); // Icon type for display
            $table->string('icon_color')->default('green'); // Icon color
            $table->string('link')->nullable(); // Optional link to navigate to
            $table->json('data')->nullable(); // Additional data (e.g., crop_plan_id)
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index(['farmer_id', 'is_read']);
            $table->index(['farmer_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('farmer_notifications');
    }
};
