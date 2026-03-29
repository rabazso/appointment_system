<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEmployeeVersionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'employee_id' => ['required', 'integer', 'exists:employees,id'],
            'uses_default_booking_interval' => ['required', 'boolean'],
            'booking_interval_minutes' => ['nullable', 'integer'],
            'uses_default_booking_window' => ['required', 'boolean'],
            'booking_window_days' => ['nullable', 'integer'],
            'valid_from' => ['required', 'date'],
            'valid_to' => ['nullable', 'date', 'after:valid_from'],
        ];
    }
}
