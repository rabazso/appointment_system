<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
        'photo_path' => ['nullable', 'string'],
        'instagram_url' => ['nullable', 'string'],
    ];
}
}
