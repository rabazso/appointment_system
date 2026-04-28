<?php

namespace App\Http\Resources\Employee;

use App\Services\Timeline\ValidFromPolicy;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeAvailabilityResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'employee_id' => $this->employee_id,
            'is_available' => $this->is_available,
            'valid_from' => $this->valid_from?->toDateString(),
            'valid_to' => $this->valid_to?->toDateString(),
            'valid_from_policy' => ValidFromPolicy::for($this->resource),
        ];
    }
}
