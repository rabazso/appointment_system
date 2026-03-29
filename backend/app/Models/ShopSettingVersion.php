<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ShopSettingVersion extends Model
{
    protected $table = 'shop_setting_versions';

    protected $fillable = [
        'shop_setting_id',
        'default_booking_interval_minutes',
        'default_booking_window_days',
        'cancellation_deadline_hours',
        'valid_from',
        'valid_to',
    ];

    protected $casts = [
        'valid_from' => 'datetime',
        'valid_to' => 'datetime',
    ];

    public function shopSetting()
    {
        return $this->belongsTo(ShopSetting::class, 'shop_setting_id');
    }

    public function scopeValidAt(Builder $query, Carbon $at): Builder
    {
        return $query
            ->where('valid_from', '<=', $at)
            ->where(function ($query) use ($at) {
                $query
                    ->whereNull('valid_to')
                    ->orWhere('valid_to', '>', $at);
            });
    }
}
