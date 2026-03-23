<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShopHoliday extends Model
{
    protected $fillable = [
        'date',
        'name',
        'is_open',
        'open_time',
        'close_time',
    ];

    protected $casts = [
        'date' => 'date',
        'is_open' => 'boolean',
    ];
}
