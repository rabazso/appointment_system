<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShopImage extends Model
{
    protected $fillable = [
        'type',
        'original',
        'preview',
    ];

    protected $hidden = [
        'original',
        'preview',
    ];

    public function getPreviewUrlAttribute(): string
    {
        return route('shop-images.preview', ['shopImage' => $this]);
    }

    public function getOriginalUrlAttribute(): string
    {
        return route('shop-images.original', ['shopImage' => $this]);
    }
}
