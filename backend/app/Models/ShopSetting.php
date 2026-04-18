<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShopSetting extends Model
{
    protected $fillable = [
        'logo_path',
        'about_us_text',
        'cancellation_deadline_hours'
    ];

    public function versions()
    {
        return $this->hasMany(ShopSettingVersion::class);
    }

    public function resolveValidVersion(): ?ShopSettingVersion
    {
        $date = now();

        return $this->versions()->validAt($date)->first();
    }
}
