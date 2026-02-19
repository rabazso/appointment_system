<?php

namespace App\Calculations;

use App\Models\Service;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Http\Request;

class EmployeeCalculation
{
    public function Employees(Request $request)
    {
        $serviceId   = $request->get('service_id');
        $appointmentStart = $request->get('appointment_start');

        if ($serviceId && $appointmentStart) {
            $service = Service::find($serviceId);

            $slotStart = Carbon::parse($appointmentStart);
            $slotEnd = Carbon::parse($appointmentStart)->addMinutes($service->duration);
        }

        $employees = Employee::query()
            ->with('user')
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
                $appointmentStart && $serviceId,
                fn($x) =>
                $x->whereDoesntHave(
                    'appointments',
                    fn($y) =>
                    $y->whereIn('status', ['pending', 'confirmed'])
                        ->where('start_datetime', '<', $slotEnd)
                        ->where('end_datetime', '>', $slotStart)
                )
            )
            ->get()
            ->map(function ($employee) use ($serviceId) {
                $data = [
                    'id' => $employee->id,
                    'name' => $employee->user->name,
                    'bio' => $employee->bio,
                    'photo_url' => $employee->photo_url,
                    'email' => $employee->user->email,
                ];

                if ($serviceId) {
                    $service = $employee->services->Where('id', $serviceId)->First();

                    $data['services'] = [
                        'service_id' => $service->pivot->service_id,
                        'price' => $service->pivot->price
                    ];
                }
                return $data;
            });

        return $employees;
    }
}
