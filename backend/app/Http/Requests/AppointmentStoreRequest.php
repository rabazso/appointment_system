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
            'service_id' => 'required|integer|exists:services,id',
            'employee_id' => 'required|integer|exists:employees,id',
            'appointment_start' => 'required|date_format:Y-m-d H:i',
            'customer_id' => 'nullable|integer|exists:users,id|required_without:guest_email',
            'guest_name' => 'nullable|string|max:255|required_without:customer_id',
            'guest_email' => 'nullable|email|max:255|unique:users,email|required_without:customer_id',
        ];
    }

    public function messages(): array
    {
        return [
            'guest_email.unique' => 'This email is already registered. Please log in to book an appointment.',
            'customer_id.required_without' => 'Please log in or provide guest details.',
        ];
    }
}
