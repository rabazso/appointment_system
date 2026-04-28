<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Http\Requests\Admin\EmployeeAvailabilityAffectedPreviewRequest;
use App\Http\Requests\Admin\EmployeeBookingRulesAffectedPreviewRequest;
use App\Http\Requests\Admin\EmployeeScheduleAffectedPreviewRequest;
use App\Http\Requests\Admin\EmployeeServicesAffectedPreviewRequest;
use App\Http\Requests\Admin\ServiceAvailabilityAffectedPreviewRequest;
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
