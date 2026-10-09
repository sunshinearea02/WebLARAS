<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TelegramAlertLog extends Model
{
    protected $table = 'telegram_alert_logs';

    protected $fillable = [
        'status',
        'sensor_log_id',
        'moisture_percent',
        'pitch',
        'roll',
        'vibration',
        'rf_confidence',
        'lstm_confidence',
        'sent',
        'error_message',
        'sensor_timestamp',
    ];

    protected $casts = [
        'sent' => 'boolean',
        'sensor_timestamp' => 'datetime',
    ];
}