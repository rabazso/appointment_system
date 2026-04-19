<?php

namespace App\Services\Booking;

use App\Models\Appointment;
use App\Models\Employee;
use App\Models\EmployeeBookingRuleConfiguration;
use App\Models\EmployeeBreak;
use App\Models\EmployeeScheduleConfiguration;
use App\Models\EmployeeService;
use App\Models\EmployeeServiceConfiguration;
use App\Models\EmployeeTimeOffRequest;
use App\Models\EmployeeVersion;
use App\Models\ServiceVersion;
use App\Models\EmployeeWorkingHour;
use App\Models\ShopSpecialDay;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class AppointmentAvailabilityService
{
    public function bookableDaysFromDate(Request $request): array
    {
        $serviceIds = $request->validated('service_ids');
        $employeeId = (int) $request->get('employee_id');
        $startDate = Carbon::parse($request->get('start_date', Carbon::today()->toDateString()))->startOfDay();

        $employee = Employee::Find($employeeId);

        $bookingRuleConfiguration = EmployeeBookingRuleConfiguration::where('employee_id', $employee->id)
            ->validAt($startDate)
            ->with('rules')
            ->first();

        $bookingWindowDays = (int) ($bookingRuleConfiguration->rules->first()->booking_window_days);

        $monthEnd = $startDate->copy()->endOfMonth()->startOfDay();
        $bookingWindowEnd = Carbon::today()->addDays($bookingWindowDays)->startOfDay();
        
        $endDate = Carbon::min($monthEnd, $bookingWindowEnd);

        if ($startDate->gt($endDate)) {
            return [];
        }

        $days = (int) $startDate->diffInDays($endDate);
        $bookableDays = [];

        for ($i = 0; $i < $days+1; $i++) {
            $date = $startDate->copy()->addDays($i);
            $availability = $this->dayAvailability($employee, $serviceIds, $date);

            $bookableDays[] = [
                'date' => $date->toDateString(),
                'is_bookable' => $availability['is_bookable'],
                'occupancy_percent' => $availability['occupancy_percent'],
            ];
        }

        return $bookableDays;
    }

    public function bookableSlotsForDate(Request $request): array
    {
        $serviceIds = $request->validated('service_ids');
        $employeeId = (int) $request->get('employee_id');
        $selectedDate = Carbon::parse($request->get('selected_date'))->startOfDay();
        $emptySlots = [$selectedDate->toDateString() => []];

        $employee = Employee::Find($employeeId);

        $specialDay = ShopSpecialDay::whereDate('date', $selectedDate)->first();
        if ($specialDay && (!$specialDay->open_time || !$specialDay->close_time)) {
            return $emptySlots;
        }

        if (!$this->servicesAreAvailableOnDate($serviceIds, $selectedDate)) {
            return $emptySlots;
        }

        $employeeVersion = EmployeeVersion::where('employee_id', $employee->id)
            ->validAt($selectedDate)
            ->where('is_available', true)
            ->first();

        if (!$employeeVersion) {
            return $emptySlots;
        }

        $isOnLeave = EmployeeTimeOffRequest::where('employee_id', $employee->id)
            ->whereDate('date', $selectedDate)
            ->where('status', 'approved')
            ->exists();

        if ($isOnLeave) {
            return $emptySlots;
        }

        $scheduleConfiguration = EmployeeScheduleConfiguration::where('employee_id', $employee->id)
            ->validAt($selectedDate)
            ->first();

        if (!$scheduleConfiguration) {
            return $emptySlots;
        }

        $weekday = $selectedDate->dayOfWeek;
        $workingHour = EmployeeWorkingHour::where('schedule_configuration_id', $scheduleConfiguration->id)
            ->where('weekday', $weekday)
            ->first();

        if (!$workingHour->start_time && !$workingHour->end_time) {
            return $emptySlots;
        }

        $bookingRuleConfiguration = EmployeeBookingRuleConfiguration::where('employee_id', $employee->id)
            ->validAt($selectedDate)
            ->with('rules')
            ->first();

        $interval = (int) ($bookingRuleConfiguration->rules->first()->booking_interval_minutes);

        $workingStart = Carbon::parse($selectedDate->toDateString() . ' ' . $workingHour->start_time);
        $workingEnd = Carbon::parse($selectedDate->toDateString() . ' ' . $workingHour->end_time);

        if ($specialDay) {
            $specialOpen = Carbon::parse($selectedDate->toDateString() . ' ' . $specialDay->open_time);
            $specialClose = Carbon::parse($selectedDate->toDateString() . ' ' . $specialDay->close_time);
            $workingStart = $workingStart->gt($specialOpen) ? $workingStart : $specialOpen;
            $workingEnd = $workingEnd->lt($specialClose) ? $workingEnd : $specialClose;
        }

        if ($workingStart->gte($workingEnd)) {
            return $emptySlots;
        }

        $breaks = EmployeeBreak::where('schedule_configuration_id', $scheduleConfiguration->id)
            ->where('weekday', $weekday)
            ->get();

        $serviceConfiguration = EmployeeServiceConfiguration::where('employee_id', $employee->id)
            ->validAt($selectedDate)
            ->first();

        if (!$serviceConfiguration) {
            return $emptySlots;
        }

        $serviceDuration = $this->totalServiceDuration($serviceConfiguration->id, $serviceIds);

        if ($serviceDuration === null) {
            return $emptySlots;
        }

        $blocked = $this->blockedIntervals(
            $employee->id,
            $selectedDate,
            $workingStart,
            $workingEnd,
            $breaks
        );

        $free = $this->freeIntervals($blocked, $workingStart, $workingEnd);

        return [
            $selectedDate->toDateString() => $this->generateSlots(
                $free,
                $selectedDate,
                $serviceDuration,
                $interval
            ),
        ];
    }

    private function blockedIntervals(
        int $employeeId,
        Carbon $selectedDate,
        Carbon $workingStart,
        Carbon $workingEnd,
        Collection $breaks,
        bool $includeAppointments = true
    ): array {
        $blocked = [];

        if ($includeAppointments) {
            $appointments = Appointment::where('employee_id', $employeeId)
                ->whereIn('status', ['pending', 'confirmed'])
                ->where('start_datetime', '<', $workingEnd)
                ->where('end_datetime', '>', $workingStart)
                ->get();

            foreach ($appointments as $appointment) {
                $blocked[] = [
                    'start' => Carbon::parse($appointment->start_datetime),
                    'end' => Carbon::parse($appointment->end_datetime),
                ];
            }
        }


        foreach ($breaks as $break) {
            $blocked[] = [
                'start' => Carbon::parse($selectedDate->toDateString() . ' ' . $break->start_time),
                'end' => Carbon::parse($selectedDate->toDateString() . ' ' . $break->end_time),
            ];
        }

        usort($blocked, fn (array $a, array $b) => $a['start'] <=> $b['start']);

        $merged = [];
        foreach ($blocked as $block) {
            if (!$merged) {
                $merged[] = $block;
                continue;
            }

            $lastIndex = count($merged) - 1;
            if ($block['start']->lte($merged[$lastIndex]['end'])) {
                if ($block['end']->gt($merged[$lastIndex]['end'])) {
                    $merged[$lastIndex]['end'] = $block['end'];
                }
                continue;
            }

            $merged[] = $block;
        }

        return $merged;
    }

    private function freeIntervals(array $blocked, Carbon $workingStart, Carbon $workingEnd): array
    {
        $free = [];
        $cursor = $workingStart->copy();

        foreach ($blocked as $block) {
            if ($cursor->lt($block['start'])) {
                $free[] = [
                    'start' => $cursor->copy(),
                    'end' => $block['start']->copy(),
                ];
            }

            $cursor = $block['end']->copy();
        }

        if ($cursor->lt($workingEnd)) {
            $free[] = [
                'start' => $cursor->copy(),
                'end' => $workingEnd->copy(),
            ];
        }

        return $free;
    }

    private function generateSlots(array $free, Carbon $selectedDate, int $serviceDuration, int $interval): array
    {
        $available = [];
        $now = Carbon::now();

        foreach ($free as $intervalBlock) {
            $start = $intervalBlock['start']->copy();
            $end = $intervalBlock['end'];

            while ($start->copy()->addMinutes($serviceDuration)->lte($end)) {
                if ($selectedDate->isToday() && $start->lte($now)) {
                    $start->addMinutes($interval);
                    continue;
                }

                $available[] = $start->format('H:i');
                $start->addMinutes($interval);
            }
        }

        return $available;
    }

    private function dayAvailability(Employee $employee, array $serviceIds, Carbon $selectedDate): array
    {
        $specialDay = ShopSpecialDay::whereDate('date', $selectedDate)->first();
        if ($specialDay && (!$specialDay->open_time || !$specialDay->close_time)) {
            return $this->emptyDayAvailability();
        }

        $isOnLeave = EmployeeTimeOffRequest::where('employee_id', $employee->id)
            ->whereDate('date', $selectedDate)
            ->where('status', 'approved')
            ->exists();

        if ($isOnLeave) {
            return $this->emptyDayAvailability();
        }

        if (!$this->servicesAreAvailableOnDate($serviceIds, $selectedDate)) {
            return $this->emptyDayAvailability();
        }

        $employeeVersion = EmployeeVersion::where('employee_id', $employee->id)
            ->validAt($selectedDate)
            ->first();

        if (!$employeeVersion || !$employeeVersion->is_available) {
            return $this->emptyDayAvailability();
        }

        $scheduleConfiguration = EmployeeScheduleConfiguration::where('employee_id', $employee->id)
            ->validAt($selectedDate)
            ->first();

        if (!$scheduleConfiguration) {
            return $this->emptyDayAvailability();
        }

        $weekday = $selectedDate->dayOfWeek;
        $workingHour = EmployeeWorkingHour::where('schedule_configuration_id', $scheduleConfiguration->id)
            ->where('weekday', $weekday)
            ->first();

        if (!$workingHour->start_time && !$workingHour->end_time) {
            return $this->emptyDayAvailability();
        }

        $workingStart = Carbon::parse($selectedDate->toDateString() . ' ' . $workingHour->start_time);
        $workingEnd = Carbon::parse($selectedDate->toDateString() . ' ' . $workingHour->end_time);

        if ($specialDay) {
            $specialOpen = Carbon::parse($selectedDate->toDateString() . ' ' . $specialDay->open_time);
            $specialClose = Carbon::parse($selectedDate->toDateString() . ' ' . $specialDay->close_time);
            $workingStart = $workingStart->gt($specialOpen) ? $workingStart : $specialOpen;
            $workingEnd = $workingEnd->lt($specialClose) ? $workingEnd : $specialClose;
        }

        if ($workingStart->gte($workingEnd)) {
            return $this->emptyDayAvailability();
        }

        $breaks = EmployeeBreak::where('schedule_configuration_id', $scheduleConfiguration->id)
            ->where('weekday', $weekday)
            ->get();

        $serviceConfiguration = EmployeeServiceConfiguration::where('employee_id', $employee->id)
            ->validAt($selectedDate)
            ->first();

        if (!$serviceConfiguration) {
            return $this->emptyDayAvailability();
        }

        $serviceDuration = $this->totalServiceDuration($serviceConfiguration->id, $serviceIds);

        if ($serviceDuration === null) {
            return $this->emptyDayAvailability();
        }

        $blocked = $this->blockedIntervals($employee->id, $selectedDate, $workingStart, $workingEnd, $breaks);
        $totalMinutes = $workingStart->diffInMinutes($workingEnd);
        $blockedMinutes = 0;

        foreach ($blocked as $block) {
            $blockedMinutes += $block['start']->diffInMinutes($block['end']);
        }

        $occupancyPercent = (int) round((min($blockedMinutes, $totalMinutes) / $totalMinutes) * 100);
        $hasAvailableServiceSlot = false;
        $now = Carbon::now();

        foreach ($this->freeIntervals($blocked, $workingStart, $workingEnd) as $freeInterval) {
            $slotStart = $freeInterval['start']->copy();

            if ($selectedDate->isToday() && $slotStart->lte($now)) {
                $slotStart = $now->copy();
            }

            if ($slotStart->addMinutes($serviceDuration)->lte($freeInterval['end'])) {
                $hasAvailableServiceSlot = true;
                break;
            }
        }

        return [
            'is_bookable' => $hasAvailableServiceSlot,
            'occupancy_percent' => $occupancyPercent,
        ];
    }

    private function emptyDayAvailability(): array
    {
        return [
            'is_bookable' => false,
            'occupancy_percent' => 0,
        ];
    }

    private function servicesAreAvailableOnDate(array $serviceIds, Carbon $selectedDate): bool
    {
        return ServiceVersion::whereIn('service_id', $serviceIds)
            ->validAt($selectedDate)
            ->where('is_available', true)
            ->count('service_id') === count($serviceIds);
    }

    private function totalServiceDuration(int $configurationId, array $serviceIds): ?int
    {
        $employeeServices = EmployeeService::where('configuration_id', $configurationId)
            ->whereIn('service_id', $serviceIds)
            ->get();

        $hasAllServices = $employeeServices->count() === count($serviceIds);

        if (!$hasAllServices) {
            return null;
        }

        return (int) $employeeServices->sum('duration');
    }
}
