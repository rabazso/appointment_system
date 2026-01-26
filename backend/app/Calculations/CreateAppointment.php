<?php

namespace App\Calculations;

use App\Mail\BookingConfirmation;
use App\Models\Service;
use App\Models\Employee;
use App\Models\Appointment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class CreateAppointment
{
    function Create(Request $request)
    {
        $serviceId = $request->get('service_id');
        $serviceDuration = Service::find($serviceId)->duration;
        $employeeId = $request->get('employee_id');
        $appointmentStart = Carbon::parse($request->get('appointment_start'));
        $customerId = $request->get('customer_id');

        $slotStart = $appointmentStart->copy()->toDateTimeString();
        $slotEnd = $appointmentStart->copy()->addMinutes($serviceDuration)->toDateTimeString();

        if ($appointmentStart->lt(now())) {
            throw ValidationException::withMessages(["appointment_start" => "Appointment cannot be created in the past"]);
        }

        if ($appointmentStart->minute % 30 != 0) {
            throw ValidationException::withMessages(['appointment_start' => 'The appointment must fall within the allowed interval']);
        }

        $employee = Employee::where('id', $employeeId)
            ->whereHas(
                'services',
                fn($x) =>
                $x->where('service_id', $serviceId)
            )
            ->whereDoesntHave(
                'appointments',
                fn($x) =>
                $x->where('start_datetime', '<', $slotEnd)
                    ->where('end_datetime', '>', $slotStart)
            )->first();

        if (!$employee) {
            throw ValidationException::withMessages(['error' => 'Employee is not available during this time for this service']);
        }

        return Appointment::create([
            'customer_id' => $customerId,
            'employee_id' => $employeeId,
            'service_id' => $serviceId,
            'start_datetime' => $slotStart,
            'end_datetime' => $slotEnd,
        ]);
        
    }
}