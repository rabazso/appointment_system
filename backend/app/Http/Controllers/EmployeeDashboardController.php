<?php

namespace App\Http\Controllers;

use App\Http\Requests\CancelEmployeeAppointmentRequest;
use App\Http\Resources\AppointmentStatusResource;
use App\Http\Resources\EmployeeAppointmentResource;
use App\Models\Appointment;
use App\Services\Booking\AppointmentCancellationNotificationService;
use Illuminate\Http\Request;

class EmployeeDashboardController extends Controller
{
    public function index(Request $request){
        $employee = $request->user()->employee;
        $statuses = $request->statuses ?? [];

        $appointments = Appointment::where('employee_id', $employee->id)
            ->with(['customer', 'appointmentServices.service'])
            ->whereDate('start_datetime', $request->date)->orderBy('start_datetime');

        if (!empty($statuses)) {
            $appointments->whereIn('status', $statuses);
        }

        return EmployeeAppointmentResource::collection($appointments->get());
    }

    public function cancelAppointment(CancelEmployeeAppointmentRequest $request, Appointment $appointment, AppointmentCancellationNotificationService $cancellationNotificationService)
    {
        $employee = $request->user()->employee;
        if ($appointment->employee_id !== $employee->id) {
            return response()->noContent(403);
        }

        if ($appointment->status !== 'pending' && $appointment->status !== 'confirmed') {
            return response()->noContent(409);
        }

        $reason = trim($request->validated('cancellation_reason'));

        $appointment->update(['status' => 'cancelled', 'cancellation_reason' => $reason ? $reason : null, 'cancelled_by' => 'employee']);
        $cancellationNotificationService->send($appointment);

        return new AppointmentStatusResource($appointment);
    }

    public function completeAppointment(Request $request, Appointment $appointment)
    {
        $employee = $request->user()->employee;
        if (!$employee || $appointment->employee_id !== $employee->id) {
            return response()->noContent(403);
        }

        if ($appointment->status !== 'confirmed') {
            return response()->noContent(409);
        }

        $appointment->update(['status' => 'completed']);

        return new AppointmentStatusResource($appointment);
    }
}
