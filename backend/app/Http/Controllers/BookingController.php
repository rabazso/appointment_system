<?php

namespace App\Http\Controllers;
use App\Models\Employee;
use App\Models\Service;

class BookingController extends Controller
{

    private ?Collection $shopSettingVersions = null;

    public function services()
    {
        $now = now();

        $services = Service::with([
            'versions' => fn ($query) => $query->orderBy('valid_from')
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
                $nextValidVersion = $service->versions->first(
                    fn ($version) => $version->valid_from > $now
                );
            }

            return [
                'id' => $service->id,
                'name' => $service->name,
                'is_valid' => $validVersion !== null,
                'valid_from' => $nextValidVersion?->valid_from?->toIso8601String(),
                'valid_to' => $validVersion
                    ? $validVersion->valid_to?->toIso8601String()
                    : $nextValidVersion?->valid_to?->toIso8601String(),
            ];
        })
        ->values();

        return response()->json($services);
    }

    public function employees(Request $request, EmployeeCalculation $ec)
        {
            $employees = Employee::with([
                'versions',
                'serviceConfigurations'
            ])->get();

            $serviceId = $request->service_id;

            return $employees->map(function ($employee) use ($serviceId, $ec)
                {
                $now = now();

                $employeeValidVersion = $employee->versions->validAt($now);

                $configurationValidVersin = $employee->serviceConfigurations
                    ->where('service_id', $serviceId)
                    ->validAt($now);

                if ($employeeValidVersion && $configurationValidVersin) {
                    return [
                        'is_valid'   => true,
                        'valid_from' => null,
                        'valid_to'   => $ec->calculateValidTo($employee, $serviceId, $now),
                    ];
                }

                $next = $ec->calculateValidFrom($employee, $serviceId, $now);

                return [
                    'is_valid'   => false,
                    'valid_from' => $next,
                    'valid_to'   => $next
                        ? $ec->calculateValidTo($employee, $serviceId, $next)
                        : null,
                ];
                }
            );
        }
    }