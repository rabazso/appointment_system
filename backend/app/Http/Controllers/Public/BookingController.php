<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;

use App\Http\Requests\Public\BookingEmployeesRequest;
use App\Http\Requests\Public\BookingDaysRequest;
use App\Http\Requests\Public\BookingSlotsRequest;
use App\Http\Requests\Public\BookingSummaryRequest;
use App\Http\Resources\Booking\BookingDayResource;
use App\Http\Resources\Booking\BookingEmployeeResource;
use App\Http\Resources\Booking\BookingServiceResource;
use App\Http\Resources\Booking\BookingSlotResource;
use App\Models\Employee;
use App\Models\EmployeeService;
use App\Models\Service;
use App\Services\Booking\EmployeeAvailabilityService;
use App\Services\Booking\AppointmentAvailabilityService;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class BookingController extends Controller
{
    public function services()
    {
        $now = now();

        $services = Service::with([
            'versions' => fn ($query) => $query
                ->where('is_available', true)
                ->where(function ($query) use ($now) {
                    $query
                        ->whereNull('valid_to')
                        ->orWhere('valid_to', '>', $now);
                })
                ->orderBy('valid_from'),
        ])
        ->get()
        ->map(function (Service $service) use ($now) {

            $validVersion = $service->versions->first(
                fn ($version) =>
                    $version->valid_from <= $now &&
                    (!$version->valid_to || $version->valid_to > $now)
            );

            $nextValidVersion = null;

            if (!$validVersion) {
                $nextValidVersion = $service->versions->first();
            }

            $service->is_valid = $validVersion !== null;
            $service->valid_from = $nextValidVersion?->valid_from;
            $service->valid_to = $validVersion
                ? $validVersion->valid_to
                : $nextValidVersion?->valid_to;

            return $service;
        })
        ->values();

        return BookingServiceResource::collection($services);
    }

    public function employees(BookingEmployeesRequest $request, EmployeeAvailabilityService $availability)
    {
        $serviceIds = $request->validated('service_ids');
        $now = now();

        $employees = Employee::with([
            'versions' => fn ($query) => $query
                ->where('is_available', true)
                ->where(function ($query) use ($now) {
                    $query
                        ->whereNull('valid_to')
                        ->orWhere('valid_to', '>', $now);
                })
                ->orderBy('valid_from'),

            'serviceConfigurations' => fn ($query) => $query
                ->where(function ($query) use ($now) {
                    $query
                        ->whereNull('valid_to')
                        ->orWhere('valid_to', '>', $now);
                })
                ->whereHas(
                    'services',
                    fn ($query) => $query->whereIn('service_id', $serviceIds),
                    '=',
                    count($serviceIds)
                )
                ->orderBy('valid_from'),

            'profileImage',
        ])->get();

        $employees = $employees->map(function ($employee) use ($availability, $now) {
            $employeeValidVersion = $employee->versions->first(
                fn ($version) =>
                    $version->valid_from <= $now &&
                    (!$version->valid_to || $version->valid_to > $now)
            );

            $configurationValidVersion = $employee->serviceConfigurations->first(
                fn ($configuration) =>
                    $configuration->valid_from <= $now &&
                    (!$configuration->valid_to || $configuration->valid_to > $now)
            );

            if ($employeeValidVersion && $configurationValidVersion) {
                $employee->is_valid = true;
                $employee->valid_from = null;
                $employee->valid_to = $availability->calculateValidTo($employee, $now);

                return $employee;
            }

            $next = $availability->calculateValidFrom($employee, $now);

            $employee->is_valid = false;
            $employee->valid_from = $next;
            $employee->valid_to = $next
                ? $availability->calculateValidTo($employee, $next)
                : null;

            return $employee;
        });

        return BookingEmployeeResource::collection($employees);
    }

    public function summary(BookingSummaryRequest $request)
    {
        $serviceIds = array_map('intval', $request->validated('service_ids'));
        $employeeId = (int) $request->validated('employee_id');
        $timezone = (string) config('app.timezone', 'UTC');
        $appointmentStart = Carbon::createFromFormat('Y-m-d H:i', $request->validated('appointment_start'), $timezone);

        $employeeServices = EmployeeService::query()
            ->whereHas('configuration', fn ($query) => $query
                ->where('employee_id', $employeeId)
                ->validAt($appointmentStart)
            )
            ->whereIn('service_id', $serviceIds)
            ->with('service')
            ->get();

        if ($employeeServices->count() !== count($serviceIds)) {
            throw ValidationException::withMessages([
                'service_ids' => 'Selected services are not available for this barber at the chosen time.',
            ]);
        }

        return response()->json([
            'data' => [
                'services' => $employeeServices->map(fn ($employeeService) => [
                    'id' => $employeeService->service_id,
                    'name' => $employeeService->service?->name,
                    'duration' => (int) $employeeService->duration,
                    'price' => (float) $employeeService->price,
                ])->values(),
                'total_duration' => (int) $employeeServices->sum('duration'),
                'total_price' => (float) $employeeServices->sum('price'),
            ],
        ]);
    }

    public function days(BookingDaysRequest $request, AppointmentAvailabilityService $availability)
    {
        return BookingDayResource::collection($availability->bookableDaysFromDate($request));
    }

    public function slots(BookingSlotsRequest $request, AppointmentAvailabilityService $availability)
    {
        $slots = collect($availability->bookableSlotsForDate($request))
            ->map(fn (array $slots, string $date) => [
                'date' => $date,
                'slots' => $slots,
            ])
            ->values();

        return BookingSlotResource::collection($slots);
    }
}
