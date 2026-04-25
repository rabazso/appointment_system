<?php

namespace App\Http\Requests;

class ServiceAvailabilityAffectedPreviewRequest extends ServiceAvailabilityRequest
{
    public function rules(): array
    {
        return [
            ...parent::rules(),
            'service_id' => ['required', 'integer', 'exists:services,id'],
            'valid_from' => ['required', 'date'],
        ];
    }
}
