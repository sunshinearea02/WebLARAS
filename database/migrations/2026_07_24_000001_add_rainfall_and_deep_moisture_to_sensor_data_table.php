<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sensor_data', function (Blueprint $table) {
            if (! Schema::hasColumn('sensor_data', 'rainfall_mm')) {
                $table->double('rainfall_mm')->nullable()
                    ->comment('Curah hujan (mm), diisi setelah rain gauge tipping bucket terpasang');
            }
            if (! Schema::hasColumn('sensor_data', 'moisture_percent_deep')) {
                $table->double('moisture_percent_deep')->nullable()
                    ->comment('Kelembapan tanah probe kedalaman kedua, diisi setelah probe dalam terpasang');
            }
        });
    }

    public function down(): void
    {
        // Kolom ini bagian dari skema inti sensor_data, tidak di-drop saat rollback.
    }
};
