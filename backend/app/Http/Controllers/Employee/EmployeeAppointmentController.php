<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;

use App\Http\Requests\Shared\CancelAppointmentRequest;
use App\Http\Resources\Appointment\AppointmentStatusResource;
use App\Http\Resources\Appointment\EmployeeAppointmentResource;
use App\Models\Appointment;
use App\Services\Booking\AppointmentCancellationNotificationService;
use Illuminate\Http\Request;

class EmployeeAppointmentController extends Controller
{
    public function index(Request $request){
        $employee = $request->user()->employee;
        $statuses = $request->statuses ?? [];

        if (!$employee) {
            return response()->json(['message' => 'Employee profile not found.'], 404);
        }

        $appointments = Appointment::where('employee_id', $employee->id)
            ->with(['customer', 'appointmentServices.service'])
            ->orderBy('start_datetime');

        if ($request->filled('date')) {
            $appointments->whereDate('start_datetime', $request->date);
        }

        if (!empty($statuses)) {
            $appointments->whereIn('status', $statuses);
        }

        $slotIntervalMinutes = (int) $employee->bookingRuleConfigurations()
            ->validAt(now())
            ->with('rules')
            ->orderByDesc('valid_from')
            ->first()?->rules?->first()?->booking_interval_minutes;
        $scheduleConfiguration = $employee->scheduleConfigurations()
            ->validAt(now())
            ->with('workingHours')
            ->orderByDesc('valid_from')
            ->first();
        $workingHours = $scheduleConfiguration?->workingHours
            ?->map(fn ($workingHour) => [
                'weekday' => (int) $workingHour->weekday,
                'start_time' => $workingHour->start_time,
                'end_time' => $workingHour->end_time,
            ])
            ->values()
            ->all() ?? [];

        return EmployeeAppointmentResource::collection($appointments->get())
            ->additional([
                'slot_interval_minutes' => $slotIntervalMinutes,
                'working_hours' => $workingHours,
            ]);
    }

    public function cancelAppointment(CancelAppointmentRequest $request, Appointment $appointment, AppointmentCancellationNotificationService $cancellationNotificationService)
    {
        $employee = $request->user()->employee;
        if ($appointment->employee_id !== $employee->id) {
            return response()->noContent(403);
        }

        if ($appointment->status !== 'pending' && $appointment->status !== 'confirmed') {
            return response()->noContent(409);
        }

        if (now()->gte($appointment->start_datetime)) {
            return response()->json([
                'message' => 'Appointment can only be cancelled before it starts.',
            ], 409);
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

        if (now()->lt($appointment->end_datetime)) {
            return response()->json([
                'message' => 'Appointment can only be completed after it has ended.',
            ], 409);
        }

        $appointment->update(['status' => 'completed']);

        return new AppointmentStatusResource($appointment);
    }

    public function markNoShow(Request $request, Appointment $appointment)
    {
        $employee = $request->user()->employee;
        if (!$employee || $appointment->employee_id !== $employee->id) {
            return response()->noContent(403);
        }

        if ($appointment->status !== 'confirmed') {
            return response()->noContent(409);
        }

        if (now()->lt($appointment->end_datetime)) {
            return response()->json([
                'message' => 'No-show can only be set after the appointment has ended.',
            ], 409);
        }

        $appointment->update(['status' => 'no_show']);

        return new AppointmentStatusResource($appointment);
    }
}
