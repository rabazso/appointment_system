<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShopOpeningHour extends Model
{
    protected $fillable = [
        'weekday',
        'is_open',
        'open_time',
        'close_time',
        'valid_from',
        'valid_to',
    ];
}
