<?php

namespace App\Http\Resources\Employee;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeDetailsResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $employee = $this->resource['employee'];

        return [
            'id' => $employee->id,
            'name' => $employee->name,
            'email' => $employee->user?->email,
            'phone' => $employee->phone,
            'bio' => $employee->bio,
            'links' => $employee->links,
            'is_available' => $this->resource['is_available'],
            'rating' => $employee->rating !== null ? round((float) $employee->rating, 1) : null,
            'profile_image' => $employee->profileImage ? (new EmployeeImageResource($employee->profileImage)) : null,
            'gallery' => EmployeeImageResource::collection($this->resource['gallery']),
            'services' => $this->resource['services'],
            'reviews' => $this->resource['reviews'],
        ];
    }
}
