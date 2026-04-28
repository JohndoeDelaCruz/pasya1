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
        $driver = DB::getDriverName();

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE farmers DROP CONSTRAINT IF EXISTS farmers_farmer_id_unique');
            DB::statement('DROP INDEX IF EXISTS farmers_farmer_id_unique');
        } else {
            try {
                Schema::table('farmers', function (Blueprint $table) {
                    $table->dropUnique('farmers_farmer_id_unique');
                });
            } catch (Throwable) {
                //
            }
        }

        if (! Schema::hasColumn('farmers', 'import_key')) {
            Schema::table('farmers', function (Blueprint $table) {
                $table->string('import_key')->nullable()->unique()->after('farmer_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('farmers', 'import_key')) {
            Schema::table('farmers', function (Blueprint $table) {
                $table->dropUnique('farmers_import_key_unique');
                $table->dropColumn('import_key');
            });
        }
    }
};
