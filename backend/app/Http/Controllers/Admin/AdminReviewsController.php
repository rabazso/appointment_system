<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Http\Resources\Review\AdminReviewResource;
use App\Models\Review;
use Illuminate\Http\Request;

class AdminReviewsController extends Controller
{
    public function index(Request $request)
    {
        $employeeId = $request->query('employee_id');
        $visibility = $request->query('visibility');
        $minRating = $request->query('min_rating');

        $reviews = Review::query()
            ->with([
                'customer:id,name',
                'appointment.employee:id,name',
                'appointment.appointmentServices.service:id,name',
            ])
            ->when($employeeId, fn ($query) => $query->whereHas('appointment', fn ($appointmentQuery) => $appointmentQuery->where('employee_id', $employeeId)))
            ->when($visibility === 'visible', fn ($query) => $query->where('is_visible', true))
            ->when($visibility === 'hidden', fn ($query) => $query->where('is_visible', false))
            ->when($minRating, fn ($query) => $query->where('rating', '>=', (int) $minRating))
            ->latest()
            ->get();

        return AdminReviewResource::collection($reviews);
    }

    public function updateVisibility(Request $request, Review $review)
    {
        $review->update([
            'is_visible' => $request->boolean('is_visible'),
        ]);

        $review->load([
            'customer:id,name',
            'appointment.employee:id,name',
            'appointment.appointmentServices.service:id,name',
        ]);

        return new AdminReviewResource($review);
    }
}
