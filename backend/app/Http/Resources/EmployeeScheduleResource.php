<?php

namespace App\Http\Resources;

use App\Services\Timeline\ValidFromPolicy;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeScheduleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'employee_id' => $this->employee_id,
            'valid_from' => $this->valid_from?->toDateString(),
            'valid_to' => $this->valid_to?->toDateString(),
            'valid_from_policy' => ValidFromPolicy::for($this->resource),
            'weeklyHours' => EmployeeScheduleWorkingHourResource::collection($this->whenLoaded('workingHours')),
            'breaks' => EmployeeScheduleBreakResource::collection($this->whenLoaded('breaks')),
        ];
    }
}
