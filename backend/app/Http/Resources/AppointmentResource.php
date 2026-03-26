<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AppointmentResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'display_status' => $this->displayStatus(),
            'start_datetime' => $this->start_datetime?->toIso8601String(),
            'end_datetime' => $this->end_datetime?->toIso8601String(),
            'price' => $this->total_price,
            'guest_name' => $this->guest_name,
            'guest_email' => $this->guest_email,
            'customer_id' => $this->customer_id,
            'employee_id' => $this->employee_id,
            'service_id' => $this->service?->id,
            'service' => [
                'id' => $this->service?->id,
                'name' => $this->service?->name,
            ],
            'employee' => [
                'id' => $this->employee?->id,
                'name' => $this->employee?->name,
            ],
            'customer' => [
                'id' => $this->customer?->id,
                'name' => $this->customer?->name,
                'email' => $this->customer?->email,
            ],
        ];
    }

    private function displayStatus(): string
    {
        return match ($this->status) {
            'cancelled', 'no_show' => 'cancelled',
            'completed' => 'completed',
            default => 'upcoming',
        };
    }
}
