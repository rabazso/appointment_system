<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeServiceVersion extends Model
{
    protected $fillable = [
        'employee_service_id',
        'duration',
        'price',
        'valid_from',
        'valid_to',
        'is_active',
    ];

    protected $casts = [
        'duration' => 'integer',
        'price' => 'integer',
        'valid_from' => 'datetime',
        'valid_to' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function employeeService()
    {
        return $this->belongsTo(EmployeeService::class);
    }
}
