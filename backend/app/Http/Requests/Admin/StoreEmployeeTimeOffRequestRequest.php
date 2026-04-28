<?php

namespace App\Http\Requests\Admin;

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
            'date' => ['required', 'date', 'after_or_equal:today'],
            'status' => ['required', 'in:pending,approved,rejected,cancelled'],
            'note' => ['required', 'string'],
        ];
    }
}
