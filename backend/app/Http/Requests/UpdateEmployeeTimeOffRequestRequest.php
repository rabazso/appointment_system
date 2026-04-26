<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEmployeeTimeOffRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'employee_id' => ['integer', 'exists:employees,id'],
            'date' => ['date', 'after_or_equal:today'],
            'status' => ['in:pending,approved,rejected,cancelled'],
            'note' => ['required', 'string'],
        ];
    }
}
