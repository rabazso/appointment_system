<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateServiceVersionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'default_duration' => ['integer'],
            'default_price' => ['integer'],
            'valid_from' => ['date'],
            'valid_to' => ['nullable', 'date'],
        ];
    }
}
