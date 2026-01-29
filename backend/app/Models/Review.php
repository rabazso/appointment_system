<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $fillable = ["user_id", "appointment_id", "rating", "comment"];

    public function user(){
        return $this->belongsTo(User::class);
    }
    public function appointment(){
        return $this->belongsTo(Appointment::class);
    }
}
