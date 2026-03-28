<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ShowEmployeeVersionValidAtRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'employee_id' => ['required', 'integer', 'exists:employees,id'],
            'date' => ['required', 'date'],
        ];
    }
}
