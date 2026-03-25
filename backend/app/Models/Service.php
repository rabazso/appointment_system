<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $fillable = [
        'name',
        'description',
    ];

    public function configurationItems()
    {
        return $this->hasMany(EmployeeServiceConfigurationItem::class);
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
