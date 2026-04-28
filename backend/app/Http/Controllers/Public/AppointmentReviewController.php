<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\Public\StoreAppointmentReviewRequest;
use App\Http\Resources\Review\CustomerReviewResource;
use App\Models\Appointment;
use App\Models\Review;

class AppointmentReviewController extends Controller
{
    public function store(StoreAppointmentReviewRequest $request, Appointment $appointment): CustomerReviewResource
    {
        $customer = $request->user()->customer;

        if (!$customer || $appointment->customer_id !== $customer->id) {
            abort(403, 'You are not allowed to review this appointment.');
        }

        if ($appointment->status !== 'completed') {
            abort(422, 'Only completed appointments can be reviewed.');
        }

        if ($appointment->review()->exists()) {
            abort(409, 'This appointment already has a review.');
        }

        $review = Review::create([
            'customer_id' => $customer->id,
            'appointment_id' => $appointment->id,
            'rating' => $request->validated('rating'),
            'comment' => $request->validated('comment'),
            'is_visible' => true,
        ]);

        return new CustomerReviewResource($review);
    }
}
