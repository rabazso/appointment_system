<?php
namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens;
    use Notifiable;

    protected $fillable = [
            'name','email','password','phone','role','verified'
        ];
    protected $hidden = ['password'];

    public function employee()
    {
        return $this->hasOne(Employee::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'customer_id');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }
}