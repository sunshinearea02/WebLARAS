<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Tabel sensor_data (telemetri ESP32).
 *
 * Kolom hasil AI (vibration, rf_confidence, lstm_confidence, forecast_confidence)
 * TIDAK ada di sensor_data, tetapi di tabel ai_predictions. Supaya kode
 * dashboard/chatbot yang lama tetap jalan, nilai itu diambil dari
 * relasi `prediction` lewat accessor di bawah dan ikut tampil di JSON.
 */
class SensorLog extends Model
{
    protected $table = 'sensor_data';

    public $timestamps = false;

    protected $fillable = [
        'node_id',
        'timestamp',
        'moisture',
        'moisture_percent',
        'moisture_percent_deep',
        'moisture_raw_dangkal',
        'moisture_raw_dalam',
        'kelembapan_dangkal',
        'kelembapan_dalam',
        'rainfall_mm',
        'ax', 'ay', 'az',
        'gx', 'gy', 'gz',
        'pitch',
        'roll',
        'sensor_log_id',
        'rain_tips_total',
        'status',
        'forecast_status',
        'battery_percent',
        'battery_voltage',
    ];

    /** Selalu ambil prediksi AI sekaligus (1 query tambahan, bukan N+1). */
    protected $with = ['prediction'];

    protected $hidden = ['prediction'];

    protected $appends = ['vibration', 'rf_confidence', 'lstm_confidence', 'forecast_confidence', 'moisture_percent', 'moisture_percent_deep'];

    public function prediction(): HasOne
    {
        return $this->hasOne(AiPrediction::class, 'sensor_data_id');
    }

    public function node(): BelongsTo
    {
        return $this->belongsTo(SensorNode::class, 'node_id');
    }

    public function getVibrationAttribute(): ?float
    {
        return $this->prediction?->vibration ?? $this->prediction?->vibration_g;
    }

    public function getRfConfidenceAttribute(): ?float
    {
        return $this->prediction?->rf_confidence;
    }

    public function getLstmConfidenceAttribute(): ?float
    {
        return $this->prediction?->lstm_confidence;
    }

    public function getForecastConfidenceAttribute(): ?float
    {
        return $this->prediction?->forecast_confidence;
    }

    /**
     * Kolom kelembapan di sensor_data berganti nama:
     * moisture_percent -> kelembapan_dangkal, moisture_percent_deep -> kelembapan_dalam.
     * Accessor ini membuat kode lama tetap jalan dengan nama kolom lama maupun baru.
     */
    public function getMoisturePercentAttribute($value): ?float
    {
        $v = $value ?? ($this->attributes['kelembapan_dangkal'] ?? null);
        return $v === null ? null : (float) $v;
    }

    public function getMoisturePercentDeepAttribute($value): ?float
    {
        $v = $value ?? ($this->attributes['kelembapan_dalam'] ?? null);
        return $v === null ? null : (float) $v;
    }

    /** forecast_status dari sensor_data, kalau kosong ambil dari ai_predictions. */
    public function getForecastStatusAttribute($value): ?string
    {
        return $value ?? $this->prediction?->forecast_status;
    }
}
