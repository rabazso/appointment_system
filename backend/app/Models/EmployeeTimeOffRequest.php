<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeTimeOffRequest extends Model
{
    protected $fillable = [
        'employee_id',
        'date',
        'type',
        'status',
        'note',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
