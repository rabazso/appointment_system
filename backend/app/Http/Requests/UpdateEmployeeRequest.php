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
            'links' => ['nullable', 'array'],
            'links.*.label' => ['required_with:links', 'string', 'max:50'],
            'links.*.url' => ['required_with:links', 'url', 'max:2048'],
            'profile_image_id' => ['nullable', 'integer', 'exists:employee_images,id'],
        ];
    }
}
