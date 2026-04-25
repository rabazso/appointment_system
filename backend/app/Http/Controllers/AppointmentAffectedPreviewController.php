<?php

namespace App\Http\Controllers;

use App\Http\Requests\EmployeeAvailabilityAffectedPreviewRequest;
use App\Http\Requests\EmployeeBookingRulesAffectedPreviewRequest;
use App\Http\Requests\EmployeeScheduleAffectedPreviewRequest;
use App\Http\Requests\EmployeeServicesAffectedPreviewRequest;
use App\Http\Requests\ServiceAvailabilityAffectedPreviewRequest;
use App\Services\Booking\AppointmentAffectedPreviewService;

class AppointmentAffectedPreviewController extends Controller
{
    public function employeeSchedule(
        EmployeeScheduleAffectedPreviewRequest $request,
        AppointmentAffectedPreviewService $previewService
    ) {
        return response()->json($previewService->previewEmployeeSchedule($request->validated()));
    }

    public function employeeServices(
        EmployeeServicesAffectedPreviewRequest $request,
        AppointmentAffectedPreviewService $previewService
    ) {
        return response()->json($previewService->previewEmployeeServices($request->validated()));
    }

    public function employeeAvailability(
        EmployeeAvailabilityAffectedPreviewRequest $request,
        AppointmentAffectedPreviewService $previewService
    ) {
        return response()->json($previewService->previewEmployeeAvailability($request->validated()));
    }

    public function employeeBookingRules(
        EmployeeBookingRulesAffectedPreviewRequest $request,
        AppointmentAffectedPreviewService $previewService
    ) {
        return response()->json($previewService->previewEmployeeBookingRules($request->validated()));
    }

    public function serviceAvailability(
        ServiceAvailabilityAffectedPreviewRequest $request,
        AppointmentAffectedPreviewService $previewService
    ) {
        return response()->json($previewService->previewServiceAvailability($request->validated()));
    }
}
