<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel untuk fitur tombol SOS dan check-in warga di titik kumpul (roadmap, belum aktif di UI).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sos_reports', function (Blueprint $table) {
            $table->id();
            $table->string('reporter_name')->nullable();
            $table->string('phone')->nullable();
            $table->enum('type', ['sos', 'check_in'])->default('sos');
            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lng', 10, 7)->nullable();
            $table->foreignId('evacuation_route_id')->nullable()->constrained('evacuation_routes')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamp('reported_at')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sos_reports');
    }
};
