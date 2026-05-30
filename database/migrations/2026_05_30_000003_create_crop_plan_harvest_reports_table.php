<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crop_plan_harvest_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('crop_plan_id')->constrained('crop_plans')->cascadeOnDelete();
            $table->foreignId('farmer_id')->constrained('farmers')->cascadeOnDelete();
            $table->date('actual_harvest_date');
            $table->decimal('actual_production_mt', 12, 4);
            $table->text('harvest_notes')->nullable();
            $table->string('lgu_validation_status')->default('pending')->index();
            $table->foreignId('lgu_validated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('lgu_validated_at')->nullable();
            $table->text('lgu_validation_notes')->nullable();
            $table->unsignedInteger('lgu_validation_revision')->default(0);
            $table->timestamp('submitted_to_da_at')->nullable();
            $table->timestamp('applied_at')->nullable();
            $table->timestamps();

            $table->index(['farmer_id', 'lgu_validation_status']);
            $table->index(['crop_plan_id', 'lgu_validation_status']);
            $table->index('actual_harvest_date');
        });

        Schema::table('crop_plans', function (Blueprint $table) {
            $table->decimal('actual_harvest_production_mt', 12, 4)->nullable()->after('actual_harvest_date');
            $table->foreignId('actual_harvest_report_id')->nullable()->after('actual_harvest_production_mt')->constrained('crop_plan_harvest_reports')->nullOnDelete();
            $table->timestamp('actual_harvest_reported_at')->nullable()->after('actual_harvest_report_id');
            $table->index('actual_harvest_reported_at');
        });
    }

    public function down(): void
    {
        Schema::table('crop_plans', function (Blueprint $table) {
            $table->dropIndex(['actual_harvest_reported_at']);
            $table->dropConstrainedForeignId('actual_harvest_report_id');
            $table->dropColumn([
                'actual_harvest_production_mt',
                'actual_harvest_reported_at',
            ]);
        });

        Schema::dropIfExists('crop_plan_harvest_reports');
    }
};
