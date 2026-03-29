<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEmployeeVersionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'uses_default_booking_interval' => ['boolean'],
            'booking_interval_minutes' => ['nullable', 'integer'],
            'uses_default_booking_window' => ['boolean'],
            'booking_window_days' => ['nullable', 'integer'],
            'valid_from' => ['date'],
            'valid_to' => ['nullable', 'date'],
        ];
    }
}
