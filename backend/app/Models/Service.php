<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $fillable = [
        'name',
        'description',
    ];

    public function employees()
    {
        return $this->belongsToMany(Employee::class, 'employee_services');
    }

    public function versions()
    {
        return $this->hasMany(ServiceVersion::class);
    }

    public function employeeServices()
    {
        return $this->hasMany(EmployeeService::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }
}
