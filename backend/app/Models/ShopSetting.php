<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShopSetting extends Model
{
    protected $fillable = [
        'max_advance_booking_days',
        'cancellation_deadline_hours',
        'slot_interval',
        'sync_opening_hours_with_employee_schedule',
    ];

    protected $casts = [
        'max_advance_booking_days' => 'integer',
        'cancellation_deadline_hours' => 'integer',
        'slot_interval' => 'integer',
        'sync_opening_hours_with_employee_schedule' => 'boolean',
    ];
}
