<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('farmer')->after('password')->index();
            $table->string('municipality')->nullable()->after('role')->index();
            $table->boolean('is_active')->default(true)->after('municipality')->index();
        });

        $adminEmail = trim((string) config('app.admin_email'));

        if ($adminEmail !== '') {
            DB::table('users')
                ->where('email', $adminEmail)
                ->update([
                    'role' => 'da_admin',
                    'is_active' => true,
                    'email_verified_at' => now(),
                    'updated_at' => now(),
                ]);
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'municipality', 'is_active']);
        });
    }
};
