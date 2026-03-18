<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'phone',
        'bio',
        'photo_path',
        'instagram_url',
    ];

    public function versions()
    {
        return $this->hasMany(EmployeeVersion::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function services()
    {
        return $this->belongsToMany(Service::class, 'employee_services');
    }

    public function employeeServices()
    {
        return $this->hasMany(EmployeeService::class);
    }

    public function image()
    {
        return $this->hasMany(EmployeeImage::class);
    }

    public function workingHours()
    {
        return $this->hasMany(EmployeeWorkingHour::class);
    }

    public function breaks()
    {
        return $this->hasMany(EmployeeBreak::class);
    }

    public function timeOff()
    {
        return $this->hasMany(EmployeeTimeOff::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }
}
