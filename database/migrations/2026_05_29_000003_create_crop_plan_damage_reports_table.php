<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crop_plan_damage_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('crop_plan_id')->constrained('crop_plans')->cascadeOnDelete();
            $table->foreignId('farmer_id')->constrained('farmers')->cascadeOnDelete();
            $table->decimal('damaged_area_hectares', 10, 2);
            $table->string('damage_cause');
            $table->date('damage_occurred_on');
            $table->text('damage_notes')->nullable();
            $table->string('lgu_validation_status')->default('pending')->index();
            $table->foreignId('lgu_validated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('lgu_validated_at')->nullable();
            $table->text('lgu_validation_notes')->nullable();
            $table->unsignedInteger('lgu_validation_revision')->default(0);
            $table->timestamp('submitted_to_da_at')->nullable();
            $table->timestamp('applied_at')->nullable();
            $table->timestamps();

            $table->index(['crop_plan_id', 'lgu_validation_status'], 'cpdr_crop_plan_status_idx');
            $table->index(['farmer_id', 'lgu_validation_status'], 'cpdr_farmer_status_idx');
        });

        DB::table('crop_plans')
            ->whereNotNull('damage_reported_at')
            ->whereNotNull('damaged_area_hectares')
            ->where('damaged_area_hectares', '>', 0)
            ->orderBy('id')
            ->chunkById(200, function ($cropPlans): void {
                foreach ($cropPlans as $cropPlan) {
                    $reportedAt = $cropPlan->damage_reported_at ?? $cropPlan->updated_at ?? $cropPlan->created_at ?? now();

                    DB::table('crop_plan_damage_reports')->insert([
                        'crop_plan_id' => $cropPlan->id,
                        'farmer_id' => $cropPlan->farmer_id,
                        'damaged_area_hectares' => $cropPlan->damaged_area_hectares,
                        'damage_cause' => $cropPlan->damage_cause ?? 'other',
                        'damage_occurred_on' => $cropPlan->damage_occurred_on ?? $reportedAt,
                        'damage_notes' => $cropPlan->damage_notes,
                        'lgu_validation_status' => 'approved',
                        'lgu_validated_at' => $reportedAt,
                        'lgu_validation_notes' => 'Existing damage report marked approved during LGU validation rollout.',
                        'submitted_to_da_at' => $reportedAt,
                        'applied_at' => $reportedAt,
                        'created_at' => $reportedAt,
                        'updated_at' => $reportedAt,
                    ]);
                }
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('crop_plan_damage_reports');
    }
};
