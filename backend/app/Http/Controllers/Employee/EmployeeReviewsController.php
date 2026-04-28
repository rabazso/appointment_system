<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;

use App\Http\Requests\Employee\UpdateEmployeeReviewVisibilityRequest;
use App\Http\Resources\Review\EmployeeReviewResource;
use App\Models\Review;
use Illuminate\Http\Request;

class EmployeeReviewsController extends Controller
{
    public function index(Request $request)
    {
        $employee = $request->user()->employee;
        if (!$employee) {
            return response()->json(['message' => 'Employee profile not found'], 404);
        }

        $reviews = Review::query()
            ->whereHas('appointment', fn ($query) => $query->where('employee_id', $employee->id))
            ->with(['customer:id,name', 'appointment.appointmentServices.service:id,name'])
            ->latest()
            ->get();

        return EmployeeReviewResource::collection($reviews);
    }

    public function updateVisibility(UpdateEmployeeReviewVisibilityRequest $request, Review $review)
    {
        $employee = $request->user()->employee;
        if (!$employee || $review->appointment?->employee_id !== $employee->id) {
            return response()->json(['message' => 'You are not allowed to update this review'], 403);
        }

        $review->update([
            'is_visible' => $request->validated('is_visible'),
        ]);

        $review->load(['customer:id,name', 'appointment.appointmentServices.service:id,name']);

        return new EmployeeReviewResource($review);
    }
}
