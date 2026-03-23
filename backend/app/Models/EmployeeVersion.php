<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeVersion extends Model
{
    protected $fillable = [
        'employee_id',
        'is_active',
        'valid_from',
        'valid_to',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'valid_from' => 'datetime',
        'valid_to' => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
