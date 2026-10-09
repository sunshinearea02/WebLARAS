<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel untuk modul Navigasi Penyelamatan (roadmap, belum aktif di UI).
 * Menyimpan rute evakuasi yang sudah divalidasi bersama kader/BPBD.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evacuation_routes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->json('waypoints')->nullable()->comment('Array koordinat lat/lng rute evakuasi');
            $table->string('meeting_point_name')->nullable();
            $table->decimal('meeting_point_lat', 10, 7)->nullable();
            $table->decimal('meeting_point_lng', 10, 7)->nullable();
            $table->boolean('validated_by_bpbd')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evacuation_routes');
    }
};
