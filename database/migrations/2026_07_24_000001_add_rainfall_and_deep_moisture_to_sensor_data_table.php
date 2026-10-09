<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Kolom untuk sensor yang masih berstatus "rencana pengadaan" pada Tabel 2
 * proposal LARAS: rain gauge (curah hujan) dan probe kelembapan tanah kedalaman kedua.
 * Kolom dibuat nullable supaya baris lama tetap kompatibel selama hardware belum terpasang.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sensor_data', function (Blueprint $table) {
            $table->float('rainfall_mm')->nullable()->after('moisture_percent')
                ->comment('Curah hujan (mm), diisi setelah rain gauge tipping bucket terpasang');
            $table->float('moisture_percent_deep')->nullable()->after('rainfall_mm')
                ->comment('Kelembapan tanah probe kedalaman kedua, diisi setelah probe dalam terpasang');
        });
    }

    public function down(): void
    {
        Schema::table('sensor_data', function (Blueprint $table) {
            $table->dropColumn(['rainfall_mm', 'moisture_percent_deep']);
        });
    }
};
