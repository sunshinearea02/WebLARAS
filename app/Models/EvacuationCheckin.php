<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EvacuationCheckin extends Model
{
    use HasFactory;

    protected $fillable = [
        'posko_id',
        'lokasi_posko',
        'nama_lengkap',
        'asal',
        'usia',
        'kondisi',
    ];
}