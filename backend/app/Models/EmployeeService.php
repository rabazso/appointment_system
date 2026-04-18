<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeService extends Model
{
    protected $fillable = [
        'configuration_id',
        'service_id',
        'duration',
        'price',
    ];

    public function configuration()
    {
        return $this->belongsTo(EmployeeServiceConfiguration::class, 'configuration_id');
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}
