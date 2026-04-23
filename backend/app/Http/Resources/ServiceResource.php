<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServiceResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'is_available' => $this->whenLoaded('versions', fn () => $this->versions->first()?->is_available ?? false),
            'versions' => ServiceVersionResource::collection($this->whenLoaded('versions')),
        ];
    }
}
