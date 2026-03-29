<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEmployeeWorkingHourRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'schedule_configuration_id' => ['required', 'integer', 'exists:employee_schedule_configurations,id'],
            'weekday' => ['required', 'integer', 'between:0,6'],
            'start_time' => ['nullable', 'date_format:H:i:s'],
            'end_time' => ['nullable', 'date_format:H:i:s'],
        ];
    }
}
