<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShopSchedule extends Model
{
    protected $fillable = [
        'weekday',
        'is_open',
        'open_time',
        'close_time',
        'valid_from',
        'valid_to',
    ];

    protected $casts = [
        'is_open' => 'boolean',
        'valid_from' => 'date',
        'valid_to' => 'date',
    ];
}
