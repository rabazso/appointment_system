<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class IndexEmployeeTimeOffRequestsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'employee_id' => ['integer', 'exists:employees,id'],
            'date' => ['date'],
            'type' => ['in:vacation,sickness,personal'],
            'status' => ['in:pending,approved,rejected,cancelled'],
        ];
    }
}
