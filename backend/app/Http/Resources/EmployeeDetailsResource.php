<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeDetailsResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource['employee']->id,
            'name' => $this->resource['employee']->name,
            'is_available' => $this->resource['is_available'],
            'gallery' => EmployeeImageResource::collection($this->resource['gallery']),
            'services' => $this->resource['services'],
            'reviews' => $this->resource['reviews'],
        ];
    }
}
