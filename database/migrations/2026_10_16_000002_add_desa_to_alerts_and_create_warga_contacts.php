<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * - alert_verifications.desa : peringatan dibuat per desa (1 desa = beberapa node)
 * - warga_contacts           : nomor WA warga per desa (desa NULL = terima peringatan semua desa,
 *                              mis. grup BPBD / perangkat kecamatan)
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('alert_verifications') && !Schema::hasColumn('alert_verifications', 'desa')) {
            Schema::table('alert_verifications', function (Blueprint $table) {
                $table->string('desa', 100)->nullable()->after('level');
                $table->index(['desa', 'state']);
            });
        }

        if (!Schema::hasTable('warga_contacts')) {
            Schema::create('warga_contacts', function (Blueprint $table) {
                $table->id();
                $table->string('desa', 100)->nullable();
                $table->string('name', 100)->nullable();
                $table->string('phone', 60);                 // 628xxx atau id grup xxx@g.us
                $table->boolean('active')->default(true);
                $table->timestamps();
                $table->unique(['desa', 'phone']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('warga_contacts');
        if (Schema::hasColumn('alert_verifications', 'desa')) {
            Schema::table('alert_verifications', function (Blueprint $table) {
                $table->dropIndex(['desa', 'state']);
                $table->dropColumn('desa');
            });
        }
    }
};