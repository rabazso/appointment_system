<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShopSetting extends Model
{
    protected $fillable = [
        'max_advance_booking_days',
        'cancellation_deadline_hours',
        'slot_interval_minutes',
        'sync_opening_hours_with_employee_schedule',
    ];

    protected $casts = [
        'max_advance_booking_days' => 'integer',
        'cancellation_deadline_hours' => 'integer',
        'slot_interval_minutes' => 'integer',
        'sync_opening_hours_with_employee_schedule' => 'boolean',
    ];
}
