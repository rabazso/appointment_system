<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class WorkingHour extends Model
{
    protected $fillable = ['employee_id','weekday','start_time','end_time'];

    public function employee(){ return $this->belongsTo(Employee::class); }
}
