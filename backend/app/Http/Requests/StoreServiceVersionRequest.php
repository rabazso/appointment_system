<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreServiceVersionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'service_id' => ['required', 'integer', 'exists:services,id'],
            'default_duration' => ['required', 'integer'],
            'default_price' => ['required', 'integer'],
            'valid_from' => ['required', 'date'],
            'valid_to' => [ 'nullable', 'date',],
        ];
    }
}
