<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingDayResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'date' => $this['date'],
            'is_bookable' => $this['is_bookable'],
            'occupancy_percent' => $this['occupancy_percent'],
        ];
    }
}
