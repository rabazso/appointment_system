<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShopSpecialDayResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'date' => $this->date,
            'name' => $this->name,
            'open_time' => $this->open_time,
            'close_time' => $this->close_time,
            'is_open' => $this->open_time !== null && $this->close_time !== null,
        ];
    }
}
