<?php
namespace App\Models;

use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens;
    use Notifiable;
    use CanResetPassword;

    protected $fillable = [
        'email',
        'password',
        'phone',
        'role',
        'email_verified_at',
    ];

    protected $hidden = ['password'];

    public function employee()
    {
        return $this->hasOne(Employee::class);
    }

    public function customer()
    {
        return $this->hasOne(Customer::class);
    }
}
