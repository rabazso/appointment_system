<?php

namespace App\Http\Resources\Appointment;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AppointmentResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $services = $this->appointmentServices
            ->map(fn ($appointmentService) => [
                'id' => $appointmentService->service?->id,
                'name' => $appointmentService->service?->name,
                'duration' => $appointmentService->duration,
                'price' => $appointmentService->price,
            ])
            ->values();
        $firstService = $services->first();
        $serviceIds = $services
            ->pluck('id')
            ->filter()
            ->values();

        return [
            'id' => $this->id,
            'status' => $this->status,
            'display_status' => $this->displayStatus(),
            'created_at' => $this->created_at?->toIso8601String(),
            'start_datetime' => $this->start_datetime?->toIso8601String(),
            'end_datetime' => $this->end_datetime?->toIso8601String(),
            'total_duration' => $this->total_duration,
            'price' => $this->total_price,
            'customer_id' => $this->customer_id,
            'employee_id' => $this->employee_id,
            'cancellation_reason' => $this->cancellation_reason,
            'cancelled_by' => $this->cancelled_by,
            'service_id' => $firstService['id'] ?? null,
            'service_ids' => $serviceIds,
            'service' => $firstService,
            'services' => $services,
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
