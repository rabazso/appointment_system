<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateShopSettingVersionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'shop_setting_id' => ['integer', 'exists:shop_settings,id'],
            'default_booking_interval_minutes' => ['integer'],
            'default_booking_window_days' => ['integer'],
            'cancellation_deadline_hours' => ['integer'],
            'valid_from' => ['date'],
            'valid_to' => ['nullable', 'date'],
        ];
    }
}
