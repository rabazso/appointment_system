<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer', 'exists:users,id', 'unique:employees,user_id'],
            'name' => ['required', 'string'],
            'phone' => ['required', 'string'],
            'bio' => ['nullable', 'string'],
            'instagram_url' => ['nullable', 'string'],
            'profile_image_id' => ['nullable', 'integer', 'exists:employee_images,id'],
        ];
    }
}
