<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class User extends Models
{
    protected $fillable = ['name','email','password','phone'];

    public function employee(){ return $this->hasOne(Employee::class); }
    public function appointments(){ return $this->hasMany(Appointment::class, 'customer_id'); }
}