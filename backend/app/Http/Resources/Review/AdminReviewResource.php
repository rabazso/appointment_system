<?php

namespace App\Http\Resources\Review;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminReviewResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'client' => $this->customer?->name ?? 'Guest',
            'rating' => $this->rating,
            'comment' => $this->comment,
            'created_at' => $this->created_at?->toDateTimeString(),
            'is_visible' => (bool) $this->is_visible,
            'services' => $this->appointment?->appointmentServices?->map(
                fn ($appointmentService) => $appointmentService->service?->name
            )->filter()->values(),
            'employee' => [
                'id' => $this->appointment?->employee?->id,
                'name' => $this->appointment?->employee?->name,
            ],
        ];
    }
}
