<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class EmployeeScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'valid_from' => [$this->isMethod('post') ? 'required' : 'sometimes', 'date'],
            'weeklyHours' => ['required', 'array', 'size:7'],
            'weeklyHours.*.weekday' => ['required', 'integer', 'between:0,6'],
            'weeklyHours.*.isOpen' => ['required', 'boolean'],
            'weeklyHours.*.start' => ['nullable', 'date_format:H:i', 'required_if:weeklyHours.*.isOpen,true'],
            'weeklyHours.*.end' => ['nullable', 'date_format:H:i', 'required_if:weeklyHours.*.isOpen,true'],
            'breaks' => ['array'],
            'breaks.*.weekday' => ['required', 'integer', 'between:0,6'],
            'breaks.*.start' => ['required', 'date_format:H:i'],
            'breaks.*.end' => ['required', 'date_format:H:i'],
        ];
    }
}
