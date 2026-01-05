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
        $appointment = $request->get('appointment');

        if ($serviceId && $appointment) {
            $service = Service::find($serviceId);

            $slotStart = Carbon::parse($appointment);
            $slotEnd = Carbon::parse($appointment)->addMinutes($service->duration);
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
                $appointment && $serviceId,
                fn($x) =>
                $x->whereDoesntHave(
                    'appointments',
                    fn($y) =>
                    $y->where('start_datetime', '<', $slotEnd)
                        ->where('end_datetime', '>', $slotStart)
                )
            )
            ->get()
            ->map(function ($employee) use($serviceId) {
                $service = $employee->services->firstWhere('id', $serviceId);
                return [
                    'id' => $employee->id,
                    'name' => $employee->user->name,
                    'bio' => $employee->bio,
                    'photo_url' => $employee->photo_url,
                    'email' => $employee->user->email,
                    'services' => ['service_id' => $service->pivot->service_id, 'price' => $service->pivot->price,] 
                ];
            });
            
            return $employees;
        
    }
}
