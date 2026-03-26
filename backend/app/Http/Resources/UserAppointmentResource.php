<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserAppointmentResource extends JsonResource
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
            'service' => [
                'id' => $this->service?->id,
                'name' => $this->service?->name,
            ],
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
