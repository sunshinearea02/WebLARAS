<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SensorNode extends Model
{
    protected $table = 'sensor_nodes';

    protected $fillable = [
        'node_code',
        'node_name',
        'latitude',
        'longitude',
        'installation_date',
        'status',
        'last_seen',
        'desa',
        'notes',
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'installation_date' => 'date',
        'last_seen' => 'datetime',
    ];

    public function sensorData(): HasMany
    {
        return $this->hasMany(SensorLog::class, 'node_id');
    }

    public function landscapeAssessments(): HasMany
    {
        return $this->hasMany(LandscapeAssessment::class, 'node_id');
    }

    /** Label untuk dropdown, contoh: "NODE-01 — Lereng Padusan". */
    public function getLabelAttribute(): string
    {
        return trim($this->node_code . ' — ' . $this->node_name, ' —');
    }
}
