<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sensor_nodes', function (Blueprint $table) {
            if (! Schema::hasColumn('sensor_nodes', 'desa')) {
                $table->string('desa')->nullable();
            }
            if (! Schema::hasColumn('sensor_nodes', 'notes')) {
                $table->text('notes')->nullable();
            }
        });

        Schema::table('sensor_data', function (Blueprint $table) {
            if (! Schema::hasColumn('sensor_data', 'battery_percent')) {
                $table->smallInteger('battery_percent')->nullable()->comment('Sisa baterai node (0-100%)');
            }
            if (! Schema::hasColumn('sensor_data', 'battery_voltage')) {
                $table->double('battery_voltage')->nullable()->comment('Tegangan baterai (V)');
            }
        });
    }

    public function down(): void
    {
        Schema::table('sensor_data', function (Blueprint $table) {
            foreach (['battery_percent', 'battery_voltage'] as $c) {
                if (Schema::hasColumn('sensor_data', $c)) $table->dropColumn($c);
            }
        });
        Schema::table('sensor_nodes', function (Blueprint $table) {
            foreach (['desa', 'notes'] as $c) {
                if (Schema::hasColumn('sensor_nodes', $c)) $table->dropColumn($c);
            }
        });
    }
};
