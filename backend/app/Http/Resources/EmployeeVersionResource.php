<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeVersionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'employee_id' => $this->employee_id,
            'uses_default_booking_interval' => $this->uses_default_booking_interval,
            'booking_interval_minutes' => $this->booking_interval_minutes,
            'uses_default_booking_window' => $this->uses_default_booking_window,
            'booking_window_days' => $this->booking_window_days,
            'valid_from' => $this->valid_from,
            'valid_to' => $this->valid_to,
        ];
    }
}
