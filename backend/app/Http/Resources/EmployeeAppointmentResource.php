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
        $serviceName = $this->appointmentServices->pluck('service.name')->filter()->implode(', ');

        return [
            'id' => $this->id,
            'status' => $this->status,
            'cancellation_reason' => $this->cancellation_reason,
            'cancelled_by' => $this->cancelled_by,
            'client' => $this->customer?->name ?? 'Guest',
            'email' => $this->customer?->email,
            'phone' => $this->customer?->phone,
            'customer_note' => $this->customer_note,
            'service' => $serviceName ?: null,
            'total_duration' => $this->total_duration,
            'total_price' => $this->total_price,
            'start_datetime' => $this->start_datetime?->toIso8601String(),
            'end_datetime' => $this->end_datetime?->toIso8601String(),
        ];
    }
}
