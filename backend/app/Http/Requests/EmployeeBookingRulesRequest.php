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
            'valid_from' => ['required', 'date'],
            'valid_to' => ['nullable', 'date', 'after:valid_from'],
            'slot_interval_minutes' => ['required', 'integer', 'min:1'],
            'max_advance_days' => ['required', 'integer', 'min:1'],
        ];
    }
}
