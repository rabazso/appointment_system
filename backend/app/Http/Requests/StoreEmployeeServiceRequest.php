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
            'duration' => ['required', 'integer'],
            'price' => ['required', 'integer'],
        ];
    }
}
