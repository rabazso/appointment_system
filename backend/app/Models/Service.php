<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $fillable = ['name', 'duration', 'description'];

    public function employees()
    {
        return $this->belongsToMany(Employee::class, 'employee_services')
            ->withPivot(['price']);
    }

    public $timestamps = false;
}