<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class EmployeeScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'valid_from' => [$this->isMethod('post') ? 'required' : 'sometimes', 'date'],
            'weeklyHours' => ['required', 'array', 'size:7'],
            'weeklyHours.*.weekday' => ['required', 'integer', 'between:0,6'],
            'weeklyHours.*.isOpen' => ['required', 'boolean'],
            'weeklyHours.*.start' => ['nullable', 'date_format:H:i', 'required_if:weeklyHours.*.isOpen,true'],
            'weeklyHours.*.end' => ['nullable', 'date_format:H:i', 'required_if:weeklyHours.*.isOpen,true'],
            'breaks' => ['array'],
            'breaks.*.weekday' => ['required', 'integer', 'between:0,6'],
            'breaks.*.start' => ['required', 'date_format:H:i'],
            'breaks.*.end' => ['required', 'date_format:H:i'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            foreach ($this->input('weeklyHours', []) as $index => $day) {
                if ($day['isOpen'] == false) {
                    continue;
                }

                $this->validateTimeRange(
                    $validator,
                    "weeklyHours.{$index}.end",
                    $day['start'],
                    $day['end'],
                    'The working hours end time must be at least one minute after the start time.'
                );
            }

            foreach ($this->input('breaks', []) as $index => $break) {
                $this->validateTimeRange(
                    $validator,
                    "breaks.{$index}.end",
                    $break['start'],
                    $break['end'],
                    'The break end time must be at least one minute after the start time.'
                );

                $workingDay = collect($this->input('weeklyHours', []))
                    ->firstWhere('weekday', $break['weekday']);

                if (! $workingDay || $workingDay['isOpen'] == false) {
                    continue;
                }

                if ($break['start'] < $workingDay['start']) {
                    $validator->errors()->add(
                        "breaks.{$index}.start",
                        'The break start time must be within the working hours.'
                    );
                }

                if ($break['end'] > $workingDay['end']) {
                    $validator->errors()->add(
                        "breaks.{$index}.end",
                        'The break end time must be within the working hours.'
                    );
                }
            }
        });
    }

    private function validateTimeRange(
        Validator $validator,
        string $field,
        string $start,
        string $end,
        string $message
    ): void {
        if (! $start || ! $end) {
            return;
        }

        if ($end <= $start) {
            $validator->errors()->add($field, $message);
        }
    }
}
