<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
        $isGuest = !$this->user();

        return [
            'service_ids' => ['required', 'array', 'min:1'],
            'service_ids.*' => ['required', 'integer', 'distinct', 'exists:services,id'],

            'employee_id' => ['required', 'integer', 'exists:employees,id'],
            'appointment_start' => ['required', 'date_format:Y-m-d H:i'],

            'guest_name' => [$isGuest ? 'required' : 'nullable', 'string', 'max:255'],

            'guest_email' => [
                $isGuest ? 'required' : 'nullable',
                'email',
                'max:255',
                Rule::unique('users', 'email'),
            ],

            'guest_phone' => ['nullable', 'string', 'max:255'],
        ];
    }
}
