<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShopOpeningHour extends Model
{
    protected $fillable = [
        'weekday',
        'open_time',
        'close_time',
    ];
}
