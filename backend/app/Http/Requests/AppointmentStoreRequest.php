<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AppointmentStoreRequest extends FormRequest
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
        return [
            'service_id' => ['required', 'integer', 'exists:services,id'],
            'employee_id' => ['required', 'integer', 'exists:employees,id'],
            'appointment_start' => ['required', 'date_format:Y-m-d H:i'],
            'customer_id' => ['required', 'integer', 'exists:users,id'],
            
        ];
    }
}
