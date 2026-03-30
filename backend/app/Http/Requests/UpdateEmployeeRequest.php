<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['string'],
            'phone' => ['string'],
            'bio' => ['nullable', 'string'],
            'instagram_url' => ['nullable', 'string'],
            'profile_image_id' => ['nullable', 'integer', 'exists:employee_images,id'],
        ];
    }
}
