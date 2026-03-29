<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShopSettingVersionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'shop_setting_id' => $this->shop_setting_id,
            'default_booking_interval_minutes' => $this->default_booking_interval_minutes,
            'default_booking_window_days' => $this->default_booking_window_days,
            'cancellation_deadline_hours' => $this->cancellation_deadline_hours,
            'valid_from' => $this->valid_from,
            'valid_to' => $this->valid_to,
        ];
    }
}
