<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEmployeeServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'configuration_id' => ['integer', 'exists:employee_service_configurations,id'],
            'service_id' => ['integer', 'exists:services,id'],
            'uses_default_values' => ['boolean'],
            'duration' => ['nullable', 'integer'],
            'price' => ['nullable', 'integer'],
        ];
    }
}
