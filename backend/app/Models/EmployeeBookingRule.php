<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeBookingRule extends Model
{
    protected $fillable = [
        'booking_rule_configuration_id',
        'booking_interval_minutes',
        'booking_window_days',
    ];

    public function configuration()
    {
        return $this->belongsTo(EmployeeBookingRuleConfiguration::class, 'booking_rule_configuration_id');
    }
}
