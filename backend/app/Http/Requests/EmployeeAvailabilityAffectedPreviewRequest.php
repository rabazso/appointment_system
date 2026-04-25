<?php

namespace App\Http\Requests;

class EmployeeAvailabilityAffectedPreviewRequest extends EmployeeAvailabilityRequest
{
    public function rules(): array
    {
        return [
            ...parent::rules(),
            'employee_id' => ['required', 'integer', 'exists:employees,id'],
            'valid_from' => ['required', 'date'],
        ];
    }
}
