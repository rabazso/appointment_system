<?php

namespace App\Services\Booking;

use App\Models\Appointment;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class AppointmentAffectedPreviewService
{
    private const ACTIVE_STATUSES = ['pending', 'confirmed'];

    public function previewEmployeeSchedule(array $data): array
    {
        $appointments = $this->employeeAppointments($data);

        $conflicts = [];
        $nonConflicts = [];

        foreach ($appointments as $appointment) {
            $item = $this->previewItem($appointment);

            if ($this->hasScheduleConflict($appointment, $data)) {
                $conflicts[] = $item;
            } else {
                $nonConflicts[] = $item;
            }
        }

        return [
            'affected_count' => count($conflicts) + count($nonConflicts),
            'conflict_count' => count($conflicts),
            'non_conflict_count' => count($nonConflicts),
            'conflict_preview' => $conflicts,
            'non_conflict_preview' => $nonConflicts,
        ];
    }

    public function previewEmployeeServices(array $data): array
    {
        $appointments = $this->employeeAppointments($data);

        $conflicts = [];
        $nonConflicts = [];

        foreach ($appointments as $appointment) {
            $item = $this->previewItem($appointment);

            if ($this->hasServicesConflict($appointment, $data)) {
                $conflicts[] = $item;
            } else {
                $nonConflicts[] = $item;
            }
        }

        return [
            'affected_count' => count($conflicts) + count($nonConflicts),
            'conflict_count' => count($conflicts),
            'non_conflict_count' => count($nonConflicts),
            'conflict_preview' => $conflicts,
            'non_conflict_preview' => $nonConflicts,
        ];
    }

    public function previewEmployeeAvailability(array $data): array
    {
        $appointments = $this->employeeAppointments($data);

        $conflicts = [];
        $nonConflicts = [];

        foreach ($appointments as $appointment) {
            $item = $this->previewItem($appointment);

            if ($this->hasAvailabilityConflict($data)) {
                $conflicts[] = $item;
            } else {
                $nonConflicts[] = $item;
            }
        }

        return [
            'affected_count' => count($conflicts) + count($nonConflicts),
            'conflict_count' => count($conflicts),
            'non_conflict_count' => count($nonConflicts),
            'conflict_preview' => $conflicts,
            'non_conflict_preview' => $nonConflicts,
        ];
    }

    public function previewEmployeeBookingRules(array $data): array
    {
        $appointments = $this->employeeAppointments($data);
        $affectedPreview = [];

        foreach ($appointments as $appointment) {
            $affectedPreview[] = $this->previewItem($appointment);
        }

        return [
            'affected_count' => count($affectedPreview),
            'conflict_count' => 0,
            'non_conflict_count' => count($affectedPreview),
            'conflict_preview' => [],
            'non_conflict_preview' => $affectedPreview,
        ];
    }

    public function previewServiceAvailability(array $data): array
    {
        $appointments = $this->serviceAppointments($data);
        $conflicts = [];
        $nonConflicts = [];

        foreach ($appointments as $appointment) {
            $item = $this->previewItem($appointment);

            if ($this->hasAvailabilityConflict($data)) {
                $conflicts[] = $item;
            } else {
                $nonConflicts[] = $item;
            }
        }

        return [
            'affected_count' => count($conflicts) + count($nonConflicts),
            'conflict_count' => count($conflicts),
            'non_conflict_count' => count($nonConflicts),
            'conflict_preview' => $conflicts,
            'non_conflict_preview' => $nonConflicts,
        ];
    }

    private function employeeAppointments(array $data): Collection
    {
        $validFrom = Carbon::parse($data['valid_from'])->startOfDay();

        return $this->baseAppointments($validFrom)
            ->where('employee_id', (int) $data['employee_id'])
            ->orderBy('start_datetime')
            ->get();
    }

    private function serviceAppointments(array $data): Collection
    {
        $validFrom = Carbon::parse($data['valid_from'])->startOfDay();

        return $this->baseAppointments($validFrom)
            ->whereHas('appointmentServices', fn ($appointmentServices) => $appointmentServices
                ->where('service_id', (int) $data['service_id']))
            ->orderBy('start_datetime')
            ->get();
    }

    private function baseAppointments(Carbon $validFrom): Builder
    {
        return Appointment::query()
            ->with(['customer', 'employee', 'appointmentServices.service'])
            ->whereIn('status', self::ACTIVE_STATUSES)
            ->where('start_datetime', '>=', $validFrom);
    }

    private function hasScheduleConflict(Appointment $appointment, array $data): bool
    {
        $weekday = $appointment->start_datetime->dayOfWeek;
        $workingDay = collect($data['weeklyHours'])
            ->firstWhere('weekday', $weekday);

        if (! $workingDay || ! ($workingDay['isOpen'] ?? false)) {
            return true;
        }

        if (blank($workingDay['start'] ?? null) || blank($workingDay['end'] ?? null)) {
            return true;
        }

        $date = $appointment->start_datetime->toDateString();
        $workingStart = Carbon::parse("{$date} {$workingDay['start']}");
        $workingEnd = Carbon::parse("{$date} {$workingDay['end']}");

        if ($appointment->start_datetime->lt($workingStart) || $appointment->end_datetime->gt($workingEnd)) {
            return true;
        }

        foreach ($data['breaks'] ?? [] as $break) {
            if ((int) ($break['weekday'] ?? -1) !== $weekday) {
                continue;
            }

            if (blank($break['start'] ?? null) || blank($break['end'] ?? null)) {
                continue;
            }

            $breakStart = Carbon::parse("{$date} {$break['start']}");
            $breakEnd = Carbon::parse("{$date} {$break['end']}");

            if ($appointment->start_datetime->lt($breakEnd) && $appointment->end_datetime->gt($breakStart)) {
                return true;
            }
        }

        return false;
    }

    private function hasServicesConflict(Appointment $appointment, array $data): bool
    {
        $newServiceIds = collect($data['services'] ?? [])
            ->map(fn ($service) => (int) $service['service_id']);

        return $appointment->appointmentServices
            ->map(fn ($service) => (int) $service->service_id)
            ->diff($newServiceIds)
            ->isNotEmpty();
    }

    private function hasAvailabilityConflict(array $data): bool
    {
        return ! (bool) $data['is_available'];
    }

    private function previewItem(Appointment $appointment): array
    {
        return [
            'id' => $appointment->id,
            'status' => $appointment->status,
            'start_datetime' => $appointment->start_datetime?->toIso8601String(),
            'end_datetime' => $appointment->end_datetime?->toIso8601String(),
            'total_duration' => $appointment->total_duration,
            'total_price' => $appointment->total_price,
            'customer' => $appointment->customer?->name,
            'employee' => $appointment->employee?->name,
            'services' => $appointment->appointmentServices
                ->map(fn ($appointmentService) => [
                    'id' => $appointmentService->service_id,
                    'name' => $appointmentService->service?->name,
                    'duration' => $appointmentService->duration,
                    'price' => $appointmentService->price,
                ])
                ->values(),
        ];
    }
}
