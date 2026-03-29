<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeServiceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'configuration_id' => $this->configuration_id,
            'service_id' => $this->service_id,
            'uses_default_values' => $this->uses_default_values,
            'duration' => $this->duration,
            'price' => $this->price,
        ];
    }
}
