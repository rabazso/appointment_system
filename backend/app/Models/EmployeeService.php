<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeService extends Model
{
    protected $fillable = [
        'configuration_id',
        'service_id',
        'uses_default_values',
        'duration',
        'price',
    ];

    protected $casts = [
        'uses_default_values' => 'boolean',
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
