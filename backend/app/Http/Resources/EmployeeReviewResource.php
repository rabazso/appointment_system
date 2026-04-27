<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeReviewResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'client' => $this->customer?->name ?? 'Guest',
            'rating' => $this->rating,
            'comment' => $this->comment,
            'services' => $this->appointment?->appointmentServices?->map(fn ($appointmentService) => $appointmentService->service?->name)->filter()->values(),
            'is_visible' => (bool) $this->is_visible,
            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }
}
