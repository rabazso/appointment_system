<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class EmployeeTimeOff extends Model {
    protected $table = 'employee_time_off';
    protected $fillable = ['employee_id','date_from','date_to','type','status'];
    public function employee() { return $this->belongsTo(Employee::class); }
}
