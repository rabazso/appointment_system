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
        $serviceName = $this->appointmentServices
            ->map(fn ($appointmentService) => $appointmentService->service?->name)
            ->filter()
            ->join(', ');

        return [
            'id' => $this->id,
            'status' => $this->status,
            'client' => $this->customer?->name ?? 'Guest',
            'email' => $this->customer?->email,
            'phone' => $this->customer?->phone,
            'service' => $serviceName ?: null,
            'price' => $this->total_price,
            'start_datetime' => $this->start_datetime?->toIso8601String(),
            'end_datetime' => $this->end_datetime?->toIso8601String(),
        ];
    }
}
