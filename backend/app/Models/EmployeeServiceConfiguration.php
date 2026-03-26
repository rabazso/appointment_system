<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeServiceConfiguration extends Model
{
    protected $fillable = [
        'employee_id',
        'valid_from',
        'valid_to',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function services()
    {
        return $this->hasMany(EmployeeService::class, 'configuration_id');
    }
}
