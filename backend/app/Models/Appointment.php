<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    protected $fillable = [
        'customer_id',
        'employee_id',
        'total_duration',
        'total_price',
        'status',
        'customer_note',
        'start_datetime',
        'end_datetime',
    ];

    protected $casts = [
        'start_datetime' => 'datetime',
        'end_datetime' => 'datetime',
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
        return $this->hasOneThrough(
            Service::class,
            AppointmentService::class,
            'appointment_id',
            'id',
            'id',
            'service_id'
        );
    }

    public function appointmentServices()
    {
        return $this->hasMany(AppointmentService::class);
    }

    public function review()
    {
        return $this->hasOne(Review::class);
    }
}
