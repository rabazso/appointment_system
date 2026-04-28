<?php

namespace App\Http\Resources\Appointment;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserAppointmentResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $services = $this->appointmentServices
            ->map(fn ($appointmentService) => [
                'id' => $appointmentService?->service?->id,
                'name' => $appointmentService?->service?->name,
                'duration' => $appointmentService?->duration,
                'price' => $appointmentService?->price,
            ])
            ->filter(fn ($service) => $service['id'] !== null)
            ->values();
        $firstService = $services->first();

        return [
            'id' => $this->id,
            'status' => $this->status,
            'display_status' => $this->displayStatus(),
            'start_datetime' => $this->start_datetime?->toIso8601String(),
            'end_datetime' => $this->end_datetime?->toIso8601String(),
            'total_duration' => $this->total_duration,
            'price' => $this->total_price,
            'customer_note' => $this->customer_note,
            'service' => [
                'id' => $firstService['id'] ?? null,
                'name' => $firstService['name'] ?? null,
            ],
            'services' => $services,
            'employee' => [
                'id' => $this->employee?->id,
                'name' => $this->employee?->name,
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
