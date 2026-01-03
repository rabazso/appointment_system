<?php

namespace App\Calculations;

use App\Models\Service;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Http\Request;

class EmployeeCalculation
{
    public function Employees(Request $request): array
    {
        $serviceId = $request->get('service_id');

        $serviceDuration = Service::findOrFail($serviceId)->duration;

        $appointmentStart = Carbon::parse($request->get('appointment_start'));
        $slotStart = $appointmentStart->copy();
        $slotEnd   = $appointmentStart->copy()->addMinutes($serviceDuration);

        $employees = Employee::query()
            ->whereHas('services', fn ($x) =>
                $x->where('services.id', $serviceId)
            )
            ->whereDoesntHave('appointments', fn ($x) =>
                $x->where('start_datetime', '<', $slotEnd)
                  ->where('end_datetime', '>', $slotStart)
            )
            ->get();

        return [
            'employees' => $employees,
        ];
    }
}
