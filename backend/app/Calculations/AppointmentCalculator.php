<?php

namespace App\Calculations;

use App\Models\Employee;
use App\Models\Appointment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class AppointmentCalculator
{
    const SLOT_MINUTES = 30;
    const DAYS_AHEAD   = 28;

    public function allAppointments(Request $request): array
    {
        $employees = Employee::with('workingHours')->get();
        $appointments = Appointment::where('start_datetime', '>=', now()->startOfDay())->get();

        $availableSlots = collect();

        $from = now()->startOfDay();
        $to   = now()->addDays(self::DAYS_AHEAD)->endOfDay();

        for ($date = $from->copy(); $date->lte($to); $date->addDay()) {
            $dayliSlots = collect();

            foreach ($employees as $employee) {

                if ($date < now()->startOfDay()){
                    continue;
                }

                $working = $employee->workingHours->firstWhere('weekday', $date->dayOfWeekIso);

                if (!$working) continue;

                $start = Carbon::parse($date->toDateString() . ' ' . $working->start_time);
                $end   = Carbon::parse($date->toDateString() . ' ' . $working->end_time);

                $slotStart = $start->copy();
                $slotEnd = $start->copy()->addMinutes(self::SLOT_MINUTES);

                while ($slotEnd->lte($end)) {

                    if ($slotStart->lt(now())) {
                        $slotStart->addMinutes(self::SLOT_MINUTES);
                        $slotEnd->addMinutes(self::SLOT_MINUTES);
                        continue;
                    }

                    $isFree = !$appointments->contains(function ($appointment) use ($employee, $slotStart, $slotEnd) {
                        return $appointment->employee_id == $employee->id &&
                               $slotStart < $appointment->end_datetime &&
                               $slotEnd > $appointment->start_datetime;
                    });

                    if ($isFree) {
                        
                        $dayliSlots->push($slotStart->format('H:i'));
                    }
                    
                    $slotStart->addMinutes(self::SLOT_MINUTES);
                    $slotEnd->addMinutes(self::SLOT_MINUTES);
                }
            }

            if ($dayliSlots->isNotEmpty()) {
            $availableSlots[$date->format('Y-m-d')] = $dayliSlots->unique()->sort()->all();
            }
        }

        return [
            'time_slots' => $availableSlots,
        ];
    }
}