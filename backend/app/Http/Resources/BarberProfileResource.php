<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BarberProfileResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'name' => $this->user?->name,
            'description' => $this->bio,
            'photo_url' => $this->photo_url,
            'gallery' => EmployeeGalleryResource::collection($this->whenLoaded('gallery')),
        ];
    }
}
