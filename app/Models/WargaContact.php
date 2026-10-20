<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WargaContact extends Model
{
    protected $fillable = ['desa', 'name', 'phone', 'active'];

    protected function casts(): array
    {
        return ['active' => 'boolean'];
    }
}