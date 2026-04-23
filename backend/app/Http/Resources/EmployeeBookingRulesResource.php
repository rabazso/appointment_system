<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeBookingRulesResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $rule = $this->rules->first();

        return [
            'id' => $this->id,
            'employee_id' => $this->employee_id,
            'valid_from' => $this->valid_from?->toDateString(),
            'valid_to' => $this->valid_to?->toDateString(),
            'slot_interval_minutes' => $rule?->booking_interval_minutes,
            'max_advance_days' => $rule?->booking_window_days,
        ];
    }
}
