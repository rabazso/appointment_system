<?php

namespace App\Http\Requests;

class EmployeeBookingRulesAffectedPreviewRequest extends EmployeeBookingRulesRequest
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
