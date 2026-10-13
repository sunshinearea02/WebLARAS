<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Hasil prediksi AI per baris sensor_data.
 * Ditulis oleh pipeline ML (Python), web hanya membaca.
 */
class AiPrediction extends Model
{
    protected $table = 'ai_predictions';

    public $timestamps = false;

    protected $guarded = ['id'];

    protected $casts = [
        'lstm_uncertain' => 'boolean',
        'timestamp' => 'datetime',
        'created_at' => 'datetime',
    ];

    public function sensorData(): BelongsTo
    {
        return $this->belongsTo(SensorLog::class, 'sensor_data_id');
    }
}
