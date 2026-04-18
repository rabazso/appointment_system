<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Service;
use App\Services\Booking\EmployeeCalculation;
use Illuminate\Http\Request;

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

            return [
                'id' => $service->id,
                'name' => $service->name,
                'is_valid' => $validVersion !== null,
                'valid_from' => $nextValidVersion?->valid_from,
                'valid_to' => $validVersion
                    ? $validVersion->valid_to
                    : $nextValidVersion?->valid_to,
            ];
        })
        ->values();

        return response()->json($services);
    }

    public function employees(Request $request, EmployeeCalculation $ec)
    {
        $validated = $request->validate([
            'service_id' => ['required', 'integer', 'exists:services,id'],
        ]);

        $serviceId = (int) $validated['service_id'];
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
                ->whereHas('services', fn ($query) => $query->where('service_id', $serviceId))
                ->orderBy('valid_from'),
        ])->get();

        return $employees->map(function ($employee) use ($serviceId, $ec, $now) {
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
                return [
                    'is_valid'   => true,
                    'valid_from' => null,
                    'valid_to'   => $ec->calculateValidTo($employee, $serviceId, $now),
                ];
            }

            $next = $ec->calculateValidFrom($employee, $serviceId, $now);

            return [
                'is_valid' => false,
                'valid_from' => $next,
                'valid_to' => $next
                    ? $ec->calculateValidTo($employee, $serviceId, $next)
                    : null,
            ];
        });
    }


    
}
