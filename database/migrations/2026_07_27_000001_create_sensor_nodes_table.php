<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sensor_nodes', function (Blueprint $table) {
            $table->id();
            $table->string('node_name');
            $table->decimal('lat', 10, 7);
            $table->decimal('lng', 10, 7);
            $table->string('status')->default('AMAN'); // AMAN | WASPADA | BAHAYA
            $table->string('desa')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('sensor_log_id')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sensor_nodes');
    }
};
