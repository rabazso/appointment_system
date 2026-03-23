<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    protected $fillable = [
        'customer_id',
        'employee_id',
        'service_id',
        'duration',
        'price',
        'status',
        'customer_note',
        'start_datetime',
        'end_datetime',
    ];

    protected $casts = [
        'duration' => 'integer',
        'price' => 'integer',
        'start_datetime' => 'datetime',
        'end_datetime' => 'datetime'
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function review()
    {
        return $this->hasOne(Review::class);
    }
}
