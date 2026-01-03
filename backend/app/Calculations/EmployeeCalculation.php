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

        $appointment = Carbon::parse($request->get('appointment'));
        $slotStart = $appointment->copy();
        $slotEnd   = $appointment->copy()->addMinutes($serviceDuration);

        $employees = Employee::query()
            ->whereHas('services', fn ($x) =>
                $x->where('services.id', $serviceId)
            )
            ->when($appointment, fn($x) =>
                $x->whereDoesntHave('appointments', fn ($y) =>
                    $y->where('start_datetime', '<', $slotEnd)
                    ->where('end_datetime', '>', $slotStart)
                ))
            ->get();

        return [
            'employees' => $employees,
        ];
    }
}
