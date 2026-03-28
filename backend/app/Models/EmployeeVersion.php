<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class EmployeeVersion extends Model
{
    protected $fillable = [
        'employee_id',
        'uses_default_booking_interval',
        'booking_interval_minutes',
        'uses_default_booking_window',
        'booking_window_days',
        'valid_from',
        'valid_to',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function scopeValidAt(Builder $query, Carbon $at): Builder
    {
        return $query
            ->where('valid_from', '<=', $at)
            ->where(function ($query) use ($at) {
                $query
                    ->whereNull('valid_to')
                    ->orWhere('valid_to', '>', $at);
            });
    }
}
