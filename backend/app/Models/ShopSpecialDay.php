<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShopSpecialDay extends Model
{
    protected $fillable = [
        'date',
        'name',
        'open_time',
        'close_time',
    ];

    protected $casts = [
        'date' => 'date',
    ];
}
