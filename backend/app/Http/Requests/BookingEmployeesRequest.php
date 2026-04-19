<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BookingEmployeesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'service_ids' => ['required', 'array', 'min:1'],
            'service_ids.*' => ['required', 'integer', 'distinct', 'exists:services,id'],
        ];
    }
}
