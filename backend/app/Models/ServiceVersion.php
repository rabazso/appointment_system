<?php

namespace App\Models;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ServiceVersion extends Model
{
    protected $fillable = [
        'service_id',
        'is_available',
        'valid_from',
        'valid_to',
    ];

    protected $casts = [
        'valid_from' => 'datetime',
        'valid_to' => 'datetime',
    ];

    public function service()
    {
        return $this->belongsTo(Service::class);
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

    public function scopeCurrentAndUpcomingFrom(Builder $query, Carbon $at): Builder
    {
        return $query
            ->where(function ($query) use ($at) {
                $query
                    ->whereNull('valid_to')
                    ->orWhere('valid_to', '>', $at);
            })
            ->orderBy('valid_from');
    }
}
