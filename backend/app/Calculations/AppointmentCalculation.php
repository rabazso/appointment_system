<?php

namespace App\Calculations;

use App\Models\Service;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AppointmentCalculation
{
    const DAYS_AHEAD   = 28;
    const SLOT_MINUTES = 30;

    public function Appointments(Request $request): array
    {
        $serviceId = $request->get('service_id');
        $serviceDuration = Service::find($serviceId)->duration;
        
        $employeeId = $request->get('employee_id');
        
        $availableSlots = collect();
        
        $from = now()->startOfDay();

        $to = now()->startOfDay()->addDays(self::DAYS_AHEAD)->endOfDay();

        $employees = Employee::with([
        'workingHours',
        'appointments' => fn($x) => $x->where('start_datetime', '>=', $from),
        ])
        ->when($employeeId, fn($x) => $x->where('id', $employeeId))
        ->when($serviceId, fn($x) => $x->whereHas('services', fn($y) => $y->where('services.id', $serviceId)))
        ->get();


        for ($date = $from->copy(); $date->lte($to); $date->addDay()) {
            
            $dailySlots = collect();

            foreach ($employees as $employee) {

                $working = $employee->workingHours->where('weekday', $date->dayOfWeekIso)->first();

                if (!$working) continue;

                $start = Carbon::parse($date->toDateString() . ' ' . $working->start_time);
                $end   = Carbon::parse($date->toDateString() . ' ' . $working->end_time);

                $slotStart = $start->copy();
                $slotEnd = $start->copy()->addMinutes($serviceDuration);

                while ($slotEnd->lte($end)) {

                    if ($slotStart->lt(now())) {
                        $slotStart->addMinutes(self::SLOT_MINUTES);
                        $slotEnd->addMinutes(self::SLOT_MINUTES);
                        continue;
                    }

                     $isFree = $employee->appointments
                    ->filter(fn($x) => $slotStart < $x->end_datetime && $slotEnd > $x->start_datetime)
                    ->isEmpty();

                    if ($isFree) {
                        
                        $dailySlots->push($slotStart->format('H:i'));
                    }
                    
                    $slotStart->addMinutes(self::SLOT_MINUTES);
                    $slotEnd->addMinutes(self::SLOT_MINUTES);
                }
            }

            if ($dailySlots->isNotEmpty()) {
            $availableSlots[$date->format('Y-m-d')] = $dailySlots->unique()->sort()->all();
            }
        }

        return [
            'time_slots' => $availableSlots,
        ];
    }
}