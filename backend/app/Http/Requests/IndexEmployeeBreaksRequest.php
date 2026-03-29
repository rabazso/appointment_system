<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class IndexEmployeeBreaksRequest extends FormRequest
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
        ];
    }
}
