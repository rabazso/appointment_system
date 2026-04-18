<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreShopSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'logo_path' => ['nullable', 'string'],
            'about_us_text' => ['nullable', 'string'],
            'cancellation_deadline_hours' => ['required', 'integer']
        ];
    }
}
