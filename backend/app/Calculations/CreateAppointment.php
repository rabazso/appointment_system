<?php

namespace App\Calculations;

use App\Models\Service;
use App\Models\Employee;
use App\Models\Appointment;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CreateAppointment
{
    function Create(Request $request)
    {
        $serviceId = $request->get('service_id');
        $employeeId = $request->get('employee_id');
        $appointmentStart = $request->get('appointment_start');
        $customerId = $request->get('customer_id');

        $service = Service::find($serviceId);
        $slotStart = Carbon::parse($appointmentStart);
        $slotEnd = Carbon::parse($appointmentStart)->addMinutes($service->duration);

        if ($slotStart->lt(now())) {
            return response()->json(['error' => 'Appointment cannot be created in the past'], 400);
        }

        if ($slotStart->minute % 30 != 0) {
            return response()->json(['error' => 'The appointment must fall within the allowed interval'], 400);
        }

        $employee = Employee::where('id', $employeeId)
            ->whereHas(
                'services',
                fn($x) =>
                $x->where('service_id', $serviceId)
            )
            ->whereDoesntHave(
                'appointments',
                fn($y) =>
                $y->where('start_datetime', '<', $slotEnd)
                    ->where('end_datetime', '>', $slotStart)
            )->first();

        if (!$employee) {
            return response()->json(['error' => 'Employee is not available during this time for this service'], 400);
        }

        Appointment::create([
            'customer_id' => $customerId,
            'employee_id' => $employeeId,
            'service_id' => $serviceId,
            'start_datetime' => $slotStart,
            'end_datetime' => $slotEnd,
        ]);

        return response()->json(['message' => 'Appointment created successfully'], 201);
    }
}