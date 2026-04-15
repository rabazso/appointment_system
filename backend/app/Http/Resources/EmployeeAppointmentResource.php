<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeAppointmentResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'client' => $this->customer?->name ?? $this->guest_name,
            'email' => $this->customer?->email ?? $this->guest_email,
            'phone' => $this->customer?->phone,
            'service' => $this->service->name,
            'price' => $this->total_price,
            'start_datetime' => $this->start_datetime->format('H:i'),
            'end_datetime' => $this->end_datetime->format('H:i'),
        ];
    }
}
