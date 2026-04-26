<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $gallery = $this->relationLoaded('images')
            ? $this->images->where('id', '!=', $this->profile_image_id)->values()
            : collect();

        return [
            'id' => $this->id,
            'name' => $this->name,
            'bio' => $this->bio,
            'description' => $this->bio,
            'links' => $this->links,
            'photo_url' => $this->profileImage?->preview_url,
            'profile_image' => $this->profileImage
                ? (new EmployeeImageResource($this->profileImage))->toArray($request)
                : null,
            'gallery' => $gallery
                ->map(fn ($image) => (new EmployeeImageResource($image))->toArray($request))
                ->values(),
        ];
    }
}
