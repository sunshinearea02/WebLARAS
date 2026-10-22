<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Laporan hambatan jalan di Peta Evakuasi (jalan ditutup, perbaikan, genangan, jalur alternatif).
 * Warga boleh melapor tanpa login (status "menunggu" sampai diverifikasi kader);
 * laporan dari petugas langsung "terverifikasi".
 * Tabel ini juga sudah dibuat langsung di Supabase, jadi dicek dulu sebelum dibuat.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('hazard_reports')) {
            return;
        }

        Schema::create('hazard_reports', function (Blueprint $table) {
            $table->id();
            $table->string('type', 60);
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->string('description', 300)->nullable();
            $table->string('reporter_name', 60)->nullable();
            $table->string('source', 10)->default('warga');        // warga | petugas
            $table->string('status', 15)->default('menunggu');     // menunggu | terverifikasi
            $table->foreignId('user_id')->nullable();              // petugas yang melapor
            $table->foreignId('verified_by')->nullable();          // petugas yang memverifikasi
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hazard_reports');
    }
};
