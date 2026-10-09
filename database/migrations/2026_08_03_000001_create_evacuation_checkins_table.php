<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evacuation_checkins', function (Blueprint $table) {
            $table->id();
            $table->string('posko_id'); // ID/Nama Posko (Contoh: Posko Cepokolimo, Posko Kembangbelor)
            $table->string('lokasi_posko'); // Detail Alamat/Lokasi Posko
            $table->string('nama_lengkap');
            $table->string('asal');
            $table->integer('usia');
            $table->enum('kondisi', ['Sehat', 'Luka Ringan', 'Luka Berat', 'Lansia / Rentan']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evacuation_checkins');
    }
};