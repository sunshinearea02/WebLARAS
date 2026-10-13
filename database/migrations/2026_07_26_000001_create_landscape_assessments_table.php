<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Data kualitatif lanskap per node sensor, diisi oleh Admin/Kader
 * (sesuai skema Supabase: wajib terhubung ke node & user pengisi).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('landscape_assessments')) {
            return;
        }

        Schema::create('landscape_assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('node_id')->constrained('sensor_nodes', indexName: 'fk_assessment_node');
            $table->foreignId('user_id')->constrained('users', indexName: 'fk_assessment_user');

            $table->string('location_name')->nullable();
            $table->string('dominant_vegetation')->nullable();
            $table->string('slope_classification')->nullable();
            $table->string('drainage_condition')->nullable();
            $table->string('soil_cracking')->nullable();
            $table->string('soil_type')->nullable();
            $table->string('landslide_history')->nullable();
            $table->boolean('artificial_slope_cutting')->nullable();
            $table->string('geosynthetic_reinforcement')->nullable();
            $table->text('field_notes')->nullable();

            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrent();
            $table->text('sensor_log_id')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('landscape_assessments');
    }
};
