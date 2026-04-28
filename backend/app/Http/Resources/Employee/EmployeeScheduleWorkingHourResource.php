<?php

namespace App\Http\Resources\Employee;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeScheduleWorkingHourResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'weekday' => $this->weekday,
            'isOpen' => $this->start_time !== null && $this->end_time !== null,
            'start' => $this->toInputTime($this->start_time),
            'end' => $this->toInputTime($this->end_time),
        ];
    }

    private function toInputTime(?string $time): ?string
    {
        return $time ? substr($time, 0, 5) : null;
    }
}
