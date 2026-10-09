<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SensorLog extends Model
{
    protected $table = 'sensor_data';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [

        'timestamp',

        'moisture',
        'moisture_percent',

        // Rencana pengadaan (lihat Tabel 2 proposal) — nullable sampai hardware terpasang
        'rainfall_mm',
        'moisture_percent_deep',

        'ax',
        'ay',
        'az',

        'gx',
        'gy',
        'gz',

        'pitch',
        'roll',

        'vibration',
        'status',

        'forecast_status',
        'forecast_confidence',

        'rf_confidence',
        'lstm_confidence'
    ];
}

