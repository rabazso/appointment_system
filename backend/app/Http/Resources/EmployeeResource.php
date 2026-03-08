<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $serviceId = $request->integer('service_id');

        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'name' => $this->user?->name,
            'email' => $this->user?->email,
            'bio' => $this->bio,
            'photo_url' => $this->photo_url,
            'instagram_url' => $this->instagram_url,
            'active' => (bool) $this->active,
            'services' => $this->when($serviceId > 0 && $this->relationLoaded('services'), function () use ($serviceId) {
                $service = $this->services->firstWhere('id', $serviceId);

                if (! $service) {
                    return null;
                }

                return [
                    'id' => $service->id,
                    'service_id' => $service->pivot?->service_id,
                    'price' => $service->pivot?->price,
                    'duration' => $service->pivot?->duration,
                ];
            }),
        ];
    }
}
