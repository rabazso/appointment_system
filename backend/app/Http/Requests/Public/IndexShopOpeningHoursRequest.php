<?php

namespace App\Http\Requests\Public;

use Illuminate\Foundation\Http\FormRequest;

class IndexShopOpeningHoursRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'weekday' => ['integer', 'between:0,6'],
        ];
    }
}
