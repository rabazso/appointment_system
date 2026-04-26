<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShopInformation extends Model
{
    protected $table = 'shop_information';

    protected $fillable = [
        'email',
        'phone',
        'address',
        'links',
    ];

    protected $casts = [
        'links' => 'array',
    ];
}
