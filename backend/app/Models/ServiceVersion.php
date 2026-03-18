<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceVersion extends Model
{
    protected $fillable = [
        'service_id',
        'is_active',
        'valid_from',
        'valid_to',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'valid_from' => 'datetime',
        'valid_to' => 'datetime',
    ];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}
