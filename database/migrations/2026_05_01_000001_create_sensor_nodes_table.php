<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Titik node sensor LARAS (sesuai skema Supabase). */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('sensor_nodes')) {
            return;
        }

        Schema::create('sensor_nodes', function (Blueprint $table) {
            $table->id();
            $table->string('node_code')->unique();
            $table->string('node_name');
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->date('installation_date')->nullable();
            $table->string('status')->default('ACTIVE');
            $table->timestamp('last_seen')->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sensor_nodes');
    }
};
