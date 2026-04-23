<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'email' => $this->whenLoaded('user', fn () => $this->user?->email),
            'name' => $this->name,
            'phone' => $this->phone,
            'bio' => $this->bio,
            'links' => $this->links,
            'profile_image' => new EmployeeImageResource($this->whenLoaded('profileImage')),
            'versions' => EmployeeVersionResource::collection($this->whenLoaded('versions')),
        ];
    }
}
