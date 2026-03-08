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
            'default_duration' => $this->default_duration,
            'default_price' => $this->default_price,
            'duration' => $this->duration,
            'active' => (bool) $this->active,
        ];
    }
}
