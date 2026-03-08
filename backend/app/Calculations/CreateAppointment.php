<?php

namespace App\Calculations;

use App\Models\Service;
use App\Models\Employee;
use App\Models\Appointment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CreateAppointment
{
    function Create(Request $request)
    {
        $serviceId = $request->get('service_id');
        $service = Service::findOrFail($serviceId);
        $employeeId = $request->get('employee_id');
        $appointmentStart = Carbon::parse($request->get('appointment_start'));
        $customerId = $request->get('customer_id');
        $guestName = $request->get('guest_name');
        $guestEmail = $request->get('guest_email');

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
            ->with(['services' => fn($q) => $q->where('services.id', $serviceId)])
            ->first();

        if (!$employee) {
            throw ValidationException::withMessages(['error' => 'Employee is not available during this time for this service']);
        }

        $employeeService = $employee->services->first();
        $serviceDuration = (int) ($employeeService?->pivot?->duration ?? $service->default_duration);
        $price = $employeeService?->pivot?->price ?? $service->default_price;

        if ($serviceDuration <= 0) {
            throw ValidationException::withMessages(['service_id' => 'Service duration is not configured']);
        }
        if ($price === null) {
            throw ValidationException::withMessages(['service_id' => 'Service price is not configured']);
        }

        $slotStart = $appointmentStart->copy()->toDateTimeString();
        $slotEnd = $appointmentStart->copy()->addMinutes($serviceDuration)->toDateTimeString();

        $hasConflict = $employee->appointments()
            ->whereIn('status', ['pending', 'confirmed'])
            ->where('start_datetime', '<', $slotEnd)
            ->where('end_datetime', '>', $slotStart)
            ->exists();
        if ($hasConflict) {
            throw ValidationException::withMessages(['error' => 'Employee is not available during this time for this service']);
        }

        if (!$customerId && (!$guestName || !$guestEmail)) {
            throw ValidationException::withMessages([
                'customer_id' => 'Please log in or provide guest details.',
            ]);
        }

        return Appointment::create([
            'customer_id' => $customerId,
            'guest_name' => $customerId ? null : $guestName,
            'guest_email' => $customerId ? null : $guestEmail,
            'employee_id' => $employeeId,
            'service_id' => $serviceId,
            'price' => $price,
            'status' => 'pending',
            'start_datetime' => $slotStart,
            'end_datetime' => $slotEnd,
        ]);
        
    }
}
