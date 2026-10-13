<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Menyamakan tabel users di Supabase dengan kebutuhan login Laravel:
 * - menambah kolom phone/role/status jika belum ada,
 * - menambah kolom remember_token (dibutuhkan fitur "Ingat saya"),
 * - membetulkan default role 'KADER' (huruf besar) yang melanggar
 *   CHECK constraint role IN ('admin','kader') menjadi 'kader'.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'phone')) {
                $table->string('phone')->nullable();
            }
            if (! Schema::hasColumn('users', 'role')) {
                $table->string('role')->default('kader');
            }
            if (! Schema::hasColumn('users', 'status')) {
                $table->string('status')->default('ACTIVE');
            }
            if (! Schema::hasColumn('users', 'remember_token')) {
                $table->rememberToken();
            }
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE users ALTER COLUMN role SET DEFAULT 'kader'");
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'remember_token')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('remember_token');
            });
        }
    }
};

