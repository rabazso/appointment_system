<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeBreak extends Model
{
    protected $fillable = [
        'employee_id',
        'weekday',
        'start_time',
        'end_time',
        'valid_from',
        'valid_to',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
