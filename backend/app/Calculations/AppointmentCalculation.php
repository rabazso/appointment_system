<?php

namespace App\Calculations;

use App\Models\Service;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AppointmentCalculation
{
    const SLOT_MINUTES = 30;

    public function Appointments(Request $request)
    {
        $serviceId = $request->get('service_id');
        $serviceDuration = Service::find($serviceId)->duration;
        $selectedDate = Carbon::parse($request->get('selected_date'));
        $employeeId = $request->get('employee_id');

        if ($selectedDate->lt(today())) {
            return response()->json(['error' => 'You cannot select a past date for appointments.'], 400);
        }

        $from = $selectedDate->copy()->startOfDay()->toDateTimeString();
        $to   = $selectedDate->copy()->endOfDay()->toDateTimeString();

        $employees = Employee::with([
            'workingHours' => fn($query) => $query->where('weekday', $selectedDate->dayOfWeekIso),
            'appointments' => fn($query) => $query->whereBetween('start_datetime', [$from, $to])
        ])
            ->whereHas('services', fn($y) => $y->where('services.id', $serviceId))
            ->when($employeeId, fn($x) => $x->where('id', $employeeId))
            ->get();

        $allAvailableSlots = [];

        foreach ($employees as $employee) {
            $working = $employee->workingHours->first();

            if (!$working) continue;

            $start = Carbon::parse($selectedDate->toDateString() . ' ' . $working->start_time);
            $end = Carbon::parse($selectedDate->toDateString() . ' ' . $working->end_time);

            $allSlots = [];

            while ($start->lt($end)) {
                $allSlots[] = $start->format('H:i');
                $start->addMinutes(self::SLOT_MINUTES);
            }

            $occupiedSlots = $employee->appointments->map(function ($appointment) {
                $start = Carbon::parse($appointment->start_datetime);
                $end = Carbon::parse($appointment->end_datetime);

                $slots = [];
                while ($start->lt($end)) {
                    $slots[] = $start->format('H:i');
                    $start->addMinutes(self::SLOT_MINUTES);
                }
                return $slots;
            })->flatten()->toArray();

            $availableSlots = array_values(array_diff($allSlots, $occupiedSlots));

            $requiredSlots = $serviceDuration / self::SLOT_MINUTES;

            for ($i = 0; $i <= count($availableSlots) - $requiredSlots; $i++) {
                $possibleSlots = array_slice($availableSlots, $i, $requiredSlots);
                $consecutive = true;

                for ($j = 0; $j < $requiredSlots - 1; $j++) {
                    $x = Carbon::parse($possibleSlots[$j]);
                    $y = Carbon::parse($possibleSlots[$j + 1]);
                    if (!$x->addMinutes(self::SLOT_MINUTES)->eq($y)) {
                        $consecutive = false;
                        break;
                    }
                }
                if ($consecutive) {
                    $allAvailableSlots[] = $availableSlots[$i];
                }
            }
        }

        $allAvailableSlots = array_unique($allAvailableSlots);
        sort($allAvailableSlots);
        $allAvailableSlots = [$selectedDate->toDateString() => $allAvailableSlots];

        return $allAvailableSlots;
    }
}