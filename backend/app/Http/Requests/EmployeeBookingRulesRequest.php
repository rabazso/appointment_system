<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EmployeeBookingRulesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'valid_from' => [$this->isMethod('post') ? 'required' : 'sometimes', 'date'],
            'booking_interval_minutes' => ['required', 'integer', 'min:1'],
            'booking_window_days' => ['required', 'integer', 'min:1'],
        ];
    }
}
