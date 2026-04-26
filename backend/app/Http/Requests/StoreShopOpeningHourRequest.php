<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreShopOpeningHourRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'weekday' => ['required', 'integer', 'between:0,6'],
            'open_time' => ['nullable', 'date_format:H:i'],
            'close_time' => ['nullable', 'date_format:H:i'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $this->validateTimeRange($validator);
        });
    }

    private function validateTimeRange(Validator $validator): void
    {
        $openTime = $this->input('open_time');
        $closeTime = $this->input('close_time');

        if (($openTime === null) !== ($closeTime === null)) {
            $validator->errors()->add(
                $openTime === null ? 'open_time' : 'close_time',
                'Opening and closing times must both be provided or both be empty.'
            );
        }

        if ($openTime && $closeTime && $closeTime <= $openTime) {
            $validator->errors()->add('close_time', 'Closing time must be later than opening time.');
        }
    }
}
