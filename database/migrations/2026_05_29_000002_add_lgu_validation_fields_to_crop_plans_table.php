<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('crop_plans', function (Blueprint $table) {
            $table->string('lgu_validation_status')->default('pending')->after('status')->index();
            $table->foreignId('lgu_validated_by')->nullable()->after('lgu_validation_status')->constrained('users')->nullOnDelete();
            $table->timestamp('lgu_validated_at')->nullable()->after('lgu_validated_by');
            $table->text('lgu_validation_notes')->nullable()->after('lgu_validated_at');
            $table->unsignedInteger('lgu_validation_revision')->default(0)->after('lgu_validation_notes');
            $table->timestamp('submitted_to_da_at')->nullable()->after('lgu_validation_revision');
            $table->index(['municipality', 'lgu_validation_status']);
        });

        DB::table('crop_plans')->update([
            'lgu_validation_status' => 'approved',
            'lgu_validated_at' => DB::raw('COALESCE(updated_at, created_at)'),
            'submitted_to_da_at' => DB::raw('COALESCE(updated_at, created_at)'),
        ]);
    }

    public function down(): void
    {
        Schema::table('crop_plans', function (Blueprint $table) {
            $table->dropIndex(['municipality', 'lgu_validation_status']);
            $table->dropConstrainedForeignId('lgu_validated_by');
            $table->dropColumn([
                'lgu_validation_status',
                'lgu_validated_at',
                'lgu_validation_notes',
                'lgu_validation_revision',
                'submitted_to_da_at',
            ]);
        });
    }
};
