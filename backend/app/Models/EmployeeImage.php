<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeImage extends Model
{
    protected $fillable = [
        'employee_id',
        'type',
        'original',
        'preview',
    ];

    protected $hidden = [
        'original',
        'preview',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function getPreviewUrlAttribute(): string
    {
        return route('employee-images.preview', ['employeeImage' => $this]);
    }

    public function getOriginalUrlAttribute(): string
    {
        return route('employee-images.original', ['employeeImage' => $this]);
    }
}
