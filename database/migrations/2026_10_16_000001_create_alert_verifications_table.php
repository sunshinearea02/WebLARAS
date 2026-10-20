<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Antrean verifikasi peringatan oleh kader sebelum dikirim ke warga.
 *
 * state:
 *   pending    -> menunggu keputusan kader
 *   sending    -> sedang dikirim ke warga (kunci sementara agar tidak dobel)
 *   approved   -> disetujui kader, sudah terkirim ke warga
 *   auto_sent  -> BAHAYA tanpa respons kader, terkirim otomatis setelah batas waktu
 *   rejected   -> kader menyatakan alarm palsu, tidak dikirim
 *   expired    -> WASPADA tanpa respons sampai batas waktu, tidak dikirim
 *   superseded -> digantikan verifikasi baru (mis. WASPADA naik jadi BAHAYA)
 *   cancelled  -> kondisi kembali AMAN sebelum kader menjawab
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('alert_verifications')) return;

        Schema::create('alert_verifications', function (Blueprint $table) {
            $table->id();
            $table->string('level', 10);                                // WASPADA | BAHAYA
            $table->string('state', 12)->default('pending');
            $table->unsignedBigInteger('sensor_data_id')->nullable();   // sensor_data.id pemicu
            $table->json('snapshot')->nullable();                       // data sensor saat terdeteksi
            $table->unsignedBigInteger('decided_by')->nullable();       // users.id kader
            $table->string('decided_by_phone', 30)->nullable();
            $table->string('decided_via', 10)->nullable();              // web | auto
            $table->timestamp('decided_at')->nullable();
            $table->timestamp('kader_notified_at')->nullable();
            $table->unsignedSmallInteger('kader_sent')->default(0);     // jumlah kader yang berhasil dikirimi
            $table->timestamp('sent_to_warga_at')->nullable();
            $table->unsignedSmallInteger('warga_sent')->default(0);
            $table->unsignedSmallInteger('warga_total')->default(0);
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index(['state', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alert_verifications');
    }
};