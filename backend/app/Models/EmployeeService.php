<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeService extends Model
{
    protected $fillable = [
        'employee_id',
        'service_id',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function versions()
    {
        return $this->hasMany(EmployeeServiceVersion::class);
    }
}
