<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShopSettingsVersion extends Model
{
    protected $fillable = [
        'shop_setting_id',
        'default_booking_interval_minutes',
        'default_booking_window_days',
        'cancellation_deadline_hours',
        'sync_opening_hours_with_employee_schedule',
        'valid_from',
        'valid_to',
    ];

    public function shopSetting()
    {
        return $this->belongsTo(ShopSetting::class);
    }
}
