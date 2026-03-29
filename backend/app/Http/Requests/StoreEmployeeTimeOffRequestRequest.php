<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEmployeeTimeOffRequestRequest extends FormRequest
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
            'type' => ['required', 'in:vacation,sickness,personal'],
            'status' => ['required', 'in:pending,approved,rejected,cancelled'],
            'note' => ['nullable', 'string'],
        ];
    }
}
