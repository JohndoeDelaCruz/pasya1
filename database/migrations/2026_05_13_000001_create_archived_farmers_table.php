<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('archived_farmers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farmer_record_id')->unique()->constrained('farmers')->cascadeOnDelete();
            $table->string('farmer_id')->nullable()->index();
            $table->string('import_key')->nullable()->index();
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->string('suffix')->nullable();
            $table->string('municipality')->nullable()->index();
            $table->string('cooperative')->nullable()->index();
            $table->string('contact_info')->nullable();
            $table->string('email')->nullable()->index();
            $table->string('mobile_number')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('original_created_at')->nullable();
            $table->timestamp('original_updated_at')->nullable();
            $table->timestamp('archived_at')->nullable()->index();
            $table->timestamps();
        });

        $now = now();

        DB::table('farmers')
            ->whereNotNull('deleted_at')
            ->orderBy('id')
            ->chunkById(250, function ($farmers) use ($now) {
                $rows = $farmers->map(fn ($farmer) => [
                    'farmer_record_id' => $farmer->id,
                    'farmer_id' => $farmer->farmer_id,
                    'import_key' => $farmer->import_key,
                    'first_name' => $farmer->first_name,
                    'middle_name' => $farmer->middle_name,
                    'last_name' => $farmer->last_name,
                    'suffix' => $farmer->suffix,
                    'municipality' => $farmer->municipality,
                    'cooperative' => $farmer->cooperative,
                    'contact_info' => $farmer->contact_info,
                    'email' => $farmer->email,
                    'mobile_number' => $farmer->mobile_number,
                    'created_by' => $farmer->created_by,
                    'original_created_at' => $farmer->created_at,
                    'original_updated_at' => $farmer->updated_at,
                    'archived_at' => $farmer->deleted_at,
                    'created_at' => $now,
                    'updated_at' => $now,
                ])->all();

                DB::table('archived_farmers')->upsert($rows, ['farmer_record_id'], [
                    'farmer_id',
                    'import_key',
                    'first_name',
                    'middle_name',
                    'last_name',
                    'suffix',
                    'municipality',
                    'cooperative',
                    'contact_info',
                    'email',
                    'mobile_number',
                    'created_by',
                    'original_created_at',
                    'original_updated_at',
                    'archived_at',
                    'updated_at',
                ]);
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('archived_farmers');
    }
};
