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
        $serviceId   = $request->get('service_id');
        $appointment = $request->get('appointment');

        if ($serviceId && $appointment) {
            $service = Service::find($serviceId);

            $slotStart = Carbon::parse($appointment);
            $slotEnd = Carbon::parse($appointment)->addMinutes($service->duration);
        }

        $employees = Employee::query()
            ->when(
                $serviceId,
                fn($x) =>
                $x->whereHas(
                    'services',
                    fn($y) =>
                    $y->where('services.id', $serviceId)
                )
            )

            ->when(
                $appointment && $serviceId,
                fn($x) =>
                $x->whereDoesntHave(
                    'appointments',
                    fn($y) =>
                    $y->where('start_datetime', '<', $slotEnd)
                        ->where('end_datetime', '>', $slotStart)
                )
            )
            ->get();

        return [
            'employees' => $employees,
        ];
    }
}
