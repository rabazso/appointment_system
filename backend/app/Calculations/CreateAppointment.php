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
        $timezone = (string) config('app.timezone', 'UTC');
        $serviceId = $request->get('service_id');
        $service = Service::findOrFail($serviceId);
        $employeeId = $request->get('employee_id');
        $appointmentStart = Carbon::createFromFormat('Y-m-d H:i', (string) $request->get('appointment_start'), $timezone);
        $customerId = $request->filled('customer_id') ? (int) $request->get('customer_id') : null;
        $guestName = trim((string) $request->get('guest_name', '')) ?: null;
        $guestEmail = trim(mb_strtolower((string) $request->get('guest_email', ''))) ?: null;

        if ($appointmentStart->lt(Carbon::now($timezone))) {
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

        $this->ensureWeeklyBookingLimitNotExceeded($appointmentStart, $customerId, $guestEmail);

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

    private function ensureWeeklyBookingLimitNotExceeded(Carbon $appointmentStart, mixed $customerId, ?string $guestEmail): void
    {
        $weekStart = $appointmentStart->copy()->startOfWeek(Carbon::MONDAY)->startOfDay();
        $weekEnd = $appointmentStart->copy()->endOfWeek(Carbon::SUNDAY)->endOfDay();

        $query = Appointment::query()
            ->whereIn('status', ['pending', 'confirmed'])
            ->whereBetween('start_datetime', [
                $weekStart->toDateTimeString(),
                $weekEnd->toDateTimeString(),
            ]);

        if ($customerId) {
            $query->where('customer_id', $customerId);
        } elseif ($guestEmail) {
            $query->whereRaw('LOWER(guest_email) = ?', [$guestEmail]);
        } else {
            return;
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'appointment_start' => 'You can only keep 1 appointment per week. Please choose a date in another week.',
            ]);
        }
    }
}
