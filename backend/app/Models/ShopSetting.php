<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShopSetting extends Model
{
    protected $fillable = [
        'logo_path',
        'about_us_text',
    ];

    public function versions()
    {
        return $this->hasMany(ShopSettingsVersion::class);
    }
}
