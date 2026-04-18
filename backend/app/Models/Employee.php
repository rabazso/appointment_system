<?php
namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'phone',
        'bio',
        'links',
        'profile_image_id',
    ];

    protected $casts = [
        'links' => 'array',
    ];

    public function versions()
    {
        return $this->hasMany(EmployeeVersion::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function serviceConfigurations()
    {
        return $this->hasMany(EmployeeServiceConfiguration::class);
    }

    public function scheduleConfigurations()
    {
        return $this->hasMany(EmployeeScheduleConfiguration::class);
    }

    public function bookingRuleConfigurations()
    {
        return $this->hasMany(EmployeeBookingRuleConfiguration::class);
    }

    public function profileImage()
    {
        return $this->belongsTo(EmployeeImage::class, 'profile_image_id');
    }

    public function images()
    {
        return $this->hasMany(EmployeeImage::class);
    }

    public function timeOffRequests()
    {
        return $this->hasMany(EmployeeTimeOffRequest::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }
}
