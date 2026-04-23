<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EmployeeServicesRequest extends FormRequest
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
            'services' => ['array'],
            'services.*.service_id' => ['required', 'integer', 'exists:services,id'],
            'services.*.duration' => ['required', 'integer'],
            'services.*.price' => ['required', 'integer'],
        ];
    }
}
