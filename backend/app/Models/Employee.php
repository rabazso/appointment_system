<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $fillable = ['user_id','bio','photo_url'];

    public function user(){ return $this->belongsTo(User::class); }
    public function services(){
        return $this->belongsToMany(Service::class,'employee_services')
            ->withPivot(['price','duration'])
    }
    public function workingHours(){ return $this->hasMany(WorkingHour::class); }
    public function appointments(){ return $this->hasMany(Appointment::class); }
}
