<?php

namespace App\Http\Resources\Booking;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingEmployeeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'employee' => [
                'id' => $this->id,
                'name' => $this->name,
                'profile_image' => $this->profileImage
                    ? [
                        'preview_url' => $this->profileImage->preview_url,
                    ]
                    : null,
            ],
            'is_valid' => $this->is_valid,
            'valid_from' => $this->valid_from,
            'valid_to' => $this->valid_to,
        ];
    }
}
