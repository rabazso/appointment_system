<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $fillable = [
        'name','description','default_duration','default_price','active'
    ];

    public function employees()
    {
        return $this->belongsToMany(Employee::class, 'employee_services')
            ->withPivot('price', 'duration');
    }
}