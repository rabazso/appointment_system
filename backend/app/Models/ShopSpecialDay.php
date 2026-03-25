<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShopSpecialDay extends Model
{
    protected $fillable = [
        'date',
        'name',
        'is_open',
        'open_time',
        'close_time',
    ];
}
