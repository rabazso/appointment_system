<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeWorkingHour extends Model
{
    protected $fillable = [
        'employee_id',
        'weekday',
        'start_time',
        'end_time',
        'valid_from',
        'valid_to',
    ];

    protected $casts = [
        'valid_from' => 'date',
        'valid_to' => 'date',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
