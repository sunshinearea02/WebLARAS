<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel telemetri sensor_data, struktur sama dengan tabel di Supabase.
 *
 * Di Supabase tabel ini sudah ada (dibuat sebelum migration Laravel dipakai dan diisi
 * oleh laras-bridge di Raspberry Pi), sehingga migration ini dilewati di sana. Migration
 * ini dibutuhkan supaya database baru, misalnya SQLite in-memory saat test, punya tabel
 * sensor_data sebelum migration lain menambah kolom atau foreign key ke tabel ini.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('sensor_data')) {
            return;
        }

        Schema::create('sensor_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('node_id')->constrained('sensor_nodes');
            $table->timestamp('timestamp')->nullable()->useCurrent();
            $table->integer('moisture_raw_dangkal')->nullable();
            $table->integer('moisture_raw_dalam')->nullable();
            $table->double('kelembapan_dangkal')->nullable();
            $table->double('kelembapan_dalam')->nullable();
            $table->integer('rain_tips_total')->nullable();
            foreach (['ax', 'ay', 'az', 'gx', 'gy', 'gz', 'pitch', 'roll'] as $axis) {
                $table->double($axis)->nullable();
            }
            $table->text('sensor_log_id')->nullable();
            $table->string('status')->nullable()->default('UNKNOWN');
            $table->string('forecast_status')->nullable();
        });
    }

    public function down(): void
    {
        // Tabel inti telemetri: tidak di-drop saat rollback supaya data Supabase aman.
    }
};
