<?php

namespace App\Http\Requests\Shared;

use Illuminate\Foundation\Http\FormRequest;

class IndexShopSpecialDaysRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'date' => ['date'],
            'month' => ['date_format:Y-m'],
            'name' => ['string'],
        ];
    }
}
