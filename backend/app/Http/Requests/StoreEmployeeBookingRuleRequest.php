<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEmployeeBookingRuleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'booking_rule_configuration_id' => ['required', 'integer', 'exists:employee_booking_rule_configurations,id'],
            'booking_interval_minutes' => ['required', 'integer', 'min:1'],
            'booking_window_days' => ['required', 'integer', 'min:1'],
        ];
    }
}
