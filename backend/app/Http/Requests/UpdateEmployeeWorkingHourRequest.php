<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEmployeeWorkingHourRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'schedule_configuration_id' => ['integer', 'exists:employee_schedule_configurations,id'],
            'weekday' => ['integer', 'between:0,6'],
            'start_time' => ['date_format:H:i:s'],
            'end_time' => ['date_format:H:i:s'],
        ];
    }
}
