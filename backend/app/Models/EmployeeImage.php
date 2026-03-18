<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeImage extends Model
{
    protected $fillable = [
        'employee_id',
        'image_path',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
