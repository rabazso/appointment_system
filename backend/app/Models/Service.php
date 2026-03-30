<?php
namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $fillable = [
        'name',
        'description',
    ];

    public function employeeServices()
    {
        return $this->hasMany(EmployeeService::class);
    }

    public function versions()
    {
        return $this->hasMany(ServiceVersion::class);
    }

    public function appointmentServices()
    {
        return $this->hasMany(AppointmentService::class);
    }
}
