<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class IndexEmployeeBookingRulesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'booking_rule_configuration_id' => ['integer', 'exists:employee_booking_rule_configurations,id'],
        ];
    }
}
