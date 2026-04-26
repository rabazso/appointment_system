<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShopOpeningHourResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'weekday' => $this->weekday,
            'open_time' => $this->toInputTime($this->open_time),
            'close_time' => $this->toInputTime($this->close_time),
            'is_open' => $this->open_time !== null && $this->close_time !== null,
        ];
    }

    private function toInputTime(?string $time): ?string
    {
        return $time ? substr($time, 0, 5) : null;
    }
}
