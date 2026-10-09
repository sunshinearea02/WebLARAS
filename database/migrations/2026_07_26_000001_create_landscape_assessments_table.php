<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('landscape_assessments', function (Blueprint $table) {
            $table->id();

            $table->string('location_name');
            $table->text('field_notes')->nullable();

            $table->string('dominant_vegetation')->nullable();
            $table->string('slope_classification')->nullable();
            $table->string('drainage_condition')->nullable();
            $table->string('soil_cracking')->nullable();
            $table->string('soil_type')->nullable();
            $table->string('landslide_history')->nullable();
            $table->string('upstream_land_use')->nullable();
            $table->boolean('artificial_slope_cutting')->nullable();
            $table->string('geosynthetic_reinforcement')->nullable();

            $table->foreignId('sensor_log_id')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('landscape_assessments');
    }
};
