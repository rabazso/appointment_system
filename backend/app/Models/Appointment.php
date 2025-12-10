<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    protected $fillable = ['customer_id','employee_id','service_id','start_datetime','end_datetime'];

    public function customer(){ return $this->belongsTo(User::class,'customer_id'); }
    public function employee(){ return $this->belongsTo(Employee::class); }
    public function service(){ return $this->belongsTo(Service::class); }
    public function payment(){ return $this->hasOne(Payment::class); }
    public function review(){ return $this->hasOne(Review::class); }
}
