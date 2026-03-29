<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreShopSettingVersionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'shop_setting_id' => ['required', 'integer', 'exists:shop_settings,id'],
            'default_booking_interval_minutes' => ['required', 'integer'],
            'default_booking_window_days' => ['required', 'integer'],
            'cancellation_deadline_hours' => ['required', 'integer'],
            'valid_from' => ['required', 'date'],
            'valid_to' => ['nullable', 'date', 'after:valid_from'],
        ];
    }
}
