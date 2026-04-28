<?php

namespace App\Http\Resources\Review;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerReviewResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'appointment_id' => $this->appointment_id,
            'rating' => $this->rating,
            'comment' => $this->comment,
            'is_visible' => (bool) $this->is_visible,
            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }
}
