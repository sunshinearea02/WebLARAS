<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Hasil prediksi model AI (RF + LSTM) per baris sensor_data (sesuai skema Supabase). */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('ai_predictions')) {
            return;
        }

        Schema::create('ai_predictions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sensor_data_id')->unique()->constrained('sensor_data', indexName: 'fk_prediction_sensor');

            $table->string('current_status')->nullable();
            $table->string('forecast_status')->nullable();
            $table->double('rf_confidence')->nullable();
            $table->double('lstm_confidence')->nullable();
            $table->double('forecast_confidence')->nullable();
            $table->double('vibration')->nullable();
            $table->string('model_version')->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->text('sensor_log_id')->nullable();
            $table->timestamp('timestamp')->nullable();

            $table->enum('final_status', ['AMAN', 'WASPADA', 'BAHAYA'])->nullable();
            $table->float('composite_risk_index')->nullable();
            $table->string('dynamic_status')->nullable();
            $table->string('hydro_ml_status')->nullable();
            $table->string('hydro_rule_status')->nullable();
            $table->string('imu_status')->nullable();
            $table->string('rf_status')->nullable();

            $table->float('lstm_forecast_shallow')->nullable();
            $table->float('lstm_forecast_deep')->nullable();
            $table->boolean('lstm_uncertain')->nullable();
            $table->float('lstm_max_std')->nullable();
            $table->text('lstm_stage')->nullable();

            $table->float('moist_shallow_rel')->nullable();
            $table->float('moist_deep_rel')->nullable();
            $table->float('rain_1h_mm')->nullable();
            $table->float('rain_duration_h')->nullable();
            $table->float('rain_24h_mm')->nullable();
            $table->float('vibration_g')->nullable();
            $table->float('gyro_dps')->nullable();
            $table->float('tilt_rate_deg_min')->nullable();
            $table->float('tilt_rate_deg_h')->nullable();
            $table->float('tilt_dev_deg')->nullable();
            $table->float('static_score')->nullable();
            $table->text('reasons')->nullable();
            $table->text('data_quality')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_predictions');
    }
};
