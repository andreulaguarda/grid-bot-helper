<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SelectedCryptocurrency extends Model
{
    protected $fillable = [
        'coin_id',
        'name',
        'symbol',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];
}