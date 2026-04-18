<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class EmployeeBookingRuleConfiguration extends Model
{
    protected $fillable = [
        'employee_id',
        'valid_from',
        'valid_to',
    ];

    protected $casts = [
        'valid_from' => 'datetime',
        'valid_to' => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function rules()
    {
        return $this->hasMany(EmployeeBookingRule::class, 'booking_rule_configuration_id');
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
