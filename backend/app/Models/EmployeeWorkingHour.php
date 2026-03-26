<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeWorkingHour extends Model
{
    protected $fillable = [
        'schedule_configuration_id',
        'weekday',
        'start_time',
        'end_time',
    ];

    public function scheduleConfiguration()
    {
        return $this->belongsTo(EmployeeScheduleConfiguration::class, 'schedule_configuration_id');
    }
}
