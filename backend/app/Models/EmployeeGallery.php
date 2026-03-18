<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeGallery extends Model
{
    protected $fillable = [
        'employee_id',
        'image_url',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
