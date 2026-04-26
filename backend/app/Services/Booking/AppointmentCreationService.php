<?php

namespace App\Services\Booking;

use App\Models\Appointment;
use App\Models\Customer;
use App\Models\EmployeeService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AppointmentCreationService
{
    public function __construct(private AppointmentAvailabilityService $availability)
    {
    }

    public function create(Request $request)
    {
        $timezone = (string) config('app.timezone', 'UTC');
        $serviceIds = array_map('intval', $request->validated('service_ids'));
        $employeeId = $request->get('employee_id');
        $appointmentStart = $request->get('appointment_start');
        $guestName = $request->get('guest_name');
        $guestEmail = $request->get('guest_email');
        $guestPhone = $request->get('guest_phone');
        $slotStart = Carbon::createFromFormat('Y-m-d H:i', $appointmentStart, $timezone);

        if (!$this->availability->isSlotBookable($serviceIds, $employeeId, $appointmentStart)) {
            throw ValidationException::withMessages([
                'appointment_start' => 'Selected time is not available.',
            ]);
        }

        $employeeServices = EmployeeService::whereHas('configuration', fn ($query) => $query
            ->where('employee_id', $employeeId)
            ->validAt(Carbon::createFromFormat('Y-m-d H:i', $appointmentStart, $timezone))
        )
            ->whereIn('service_id', $serviceIds)
            ->get();

        $totalDuration = (int) $employeeServices->sum('duration');
        $totalPrice = (int) $employeeServices->sum('price');
        $slotEnd = $slotStart->copy()->addMinutes($totalDuration);

        $customer = $this->resolveCustomer($request, $guestName, $guestEmail, $guestPhone);

        $appointment = Appointment::create([
            'customer_id' => $customer->id,
            'employee_id' => $employeeId,
            'total_duration' => $totalDuration,
            'total_price' => $totalPrice,
            'status' => 'pending',
            'start_datetime' => $slotStart->toDateTimeString(),
            'end_datetime' => $slotEnd->toDateTimeString(),
        ]);

        $appointment->appointmentServices()->createMany(
            $employeeServices->map(fn ($service) => [
                'service_id' => $service->service_id,
                'duration' => $service->duration,
                'price' => $service->price,
            ])->all()
        );

        return $appointment;
    }

    private function resolveCustomer(Request $request, ?string $guestName, ?string $guestEmail, ?string $guestPhone): Customer
    {
        if ($request->user()?->customer) {
            return $request->user()->customer;
        }

        return Customer::updateOrCreate(
            ['email' => $guestEmail],
            [
                'name' => $guestName,
                'phone' => $guestPhone,
            ]
        );
    }
}
