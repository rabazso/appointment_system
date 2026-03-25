<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeTimeOff extends Model
{
    protected $table = 'employee_time_off_requests';

    protected $fillable = [
        'employee_id',
        'date',
        'type',
        'status',
        'note',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
