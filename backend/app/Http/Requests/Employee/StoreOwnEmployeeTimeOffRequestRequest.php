<?php

namespace App\Http\Requests\Employee;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOwnEmployeeTimeOffRequestRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $employeeId = $this->user()?->employee?->id;
        return [
            'date' => ['required', 'date', 'after_or_equal:today', Rule::unique('employee_time_off_requests', 'date')->where('employee_id', $employeeId),],
            'note' => ['required', 'string'],
        ];
    }
}
