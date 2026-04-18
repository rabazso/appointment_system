<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeBookingRuleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'booking_rule_configuration_id' => $this->booking_rule_configuration_id,
            'booking_interval_minutes' => $this->booking_interval_minutes,
            'booking_window_days' => $this->booking_window_days,
        ];
    }
}
