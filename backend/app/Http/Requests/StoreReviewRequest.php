<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreReviewRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $customerId = $this->user()?->id;

        return [
            "appointment_id" => [
                "bail",
                "required",
                "integer",
                Rule::exists("appointments", "id")->where(
                    fn ($query) => $query->where("customer_id", $customerId)
                ),
                "unique:reviews,appointment_id",
            ],
            "rating" => "required|integer|min:1|max:5",
            "comment" => "nullable|string|max:1000"
        ];
    }

    public function messages(): array
    {
        return [
            "appointment_id.exists" => "You can only review your own appointments.",
            "appointment_id.unique" => "A review for this appointment already exists.",
        ];
    }
}
