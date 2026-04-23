<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ServiceAvailabilityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'is_available' => ['required', 'boolean'],
            'valid_from' => ['required', 'date'],
            'valid_to' => ['nullable', 'date', 'after:valid_from'],
        ];
    }
}
