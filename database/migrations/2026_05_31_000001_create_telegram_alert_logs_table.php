<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('telegram_alert_logs')) {
            Schema::create('telegram_alert_logs', function (Blueprint $table) {
                $table->id();
                $table->string('status', 20);           
                $table->unsignedBigInteger('sensor_log_id')->nullable(); 
                $table->decimal('moisture_percent', 6, 2)->nullable();
                $table->decimal('pitch', 8, 4)->nullable();
                $table->decimal('roll', 8, 4)->nullable();
                $table->decimal('vibration', 10, 6)->nullable();
                $table->decimal('rf_confidence', 5, 4)->nullable();
                $table->decimal('lstm_confidence', 5, 4)->nullable();
                $table->boolean('sent')->default(false);
                $table->text('error_message')->nullable();
                $table->timestamp('sensor_timestamp')->nullable();
                $table->timestamps();

                $table->index('status');
                $table->index('created_at');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('telegram_alert_logs');
    }
};