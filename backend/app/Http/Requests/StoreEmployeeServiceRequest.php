<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEmployeeServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'configuration_id' => ['required', 'integer', 'exists:employee_service_configurations,id'],
            'service_id' => ['required', 'integer', 'exists:services,id'],
            'uses_default_values' => ['required', 'boolean'],
            'duration' => ['nullable', 'integer'],
            'price' => ['nullable', 'integer'],
        ];
    }
}
