<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Resources\Employee\EmployeeDetailsResource;
use App\Http\Resources\Employee\EmployeeResource;
use App\Http\Resources\Employee\EmployeeServiceResource;
use App\Http\Resources\Review\EmployeeReviewResource;
use App\Models\Employee;

class EmployeeController extends Controller
{
    public function index()
    {
        $now = now();

        return EmployeeResource::collection(
            Employee::with([
                'profileImage',
                'user',
                'versions' => fn ($query) => $query->validAt($now),
            ])
                ->withAvg('reviews as rating', 'rating')
                ->get()
        );
    }

    public function show(Employee $employee): EmployeeDetailsResource
    {
        $now = now();
        $employee->load(['profileImage', 'images', 'user:id,email'])->loadAvg('reviews as rating', 'rating');

        $isAvailable = (bool) $employee->versions()->validAt($now)->latest('valid_from')->value('is_available');

        $reviews = EmployeeReviewResource::collection(
            $employee->reviews()->where('is_visible', true)->with(['customer:id,name', 'appointment.appointmentServices.service:id,name'])
                ->latest()->get()
        );

        $serviceConfiguration = $employee->serviceConfigurations()->validAt($now)->with('services.service')->latest('valid_from')->first();

        $services = EmployeeServiceResource::collection($serviceConfiguration?->services ?? collect());

        $gallery = $employee->images->where('id', '!=', $employee->profile_image_id)->values();

        return new EmployeeDetailsResource([
            'employee' => $employee,
            'is_available' => $isAvailable,
            'gallery' => $gallery,
            'services' => $services,
            'reviews' => $reviews,
        ]);
    }
}
