<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SensorNode extends Model
{
    protected $table = 'sensor_nodes';

    protected $fillable = [
        'node_name',
        'lat',
        'lng',
        'status',
        'desa',
        'notes',
        'sensor_log_id',
    ];
}
