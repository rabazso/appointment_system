<?php

namespace App\Http\Controllers;

use App\Http\Requests\EmployeeServicesRequest;
use App\Http\Resources\EmployeeServicesResource;
use App\Models\Employee;
use App\Models\EmployeeServiceConfiguration;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class EmployeeServicesController extends Controller
{
    public function index(Employee $employee)
    {
        $serviceConfigurations = $employee->serviceConfigurations()
            ->with('services.service')
            ->orderBy('valid_from')
            ->get();

        return EmployeeServicesResource::collection($serviceConfigurations);
    }

    public function store(EmployeeServicesRequest $request, Employee $employee): EmployeeServicesResource
    {
        $serviceConfiguration = DB::transaction(function () use ($request, $employee) {
            $serviceConfiguration = $employee->serviceConfigurations()->create($request->only('valid_from', 'valid_to'));
            $this->createServiceDetails(
                $serviceConfiguration,
                $request->validated('services', [])
            );

            return $serviceConfiguration->load('services.service');
        });

        return new EmployeeServicesResource($serviceConfiguration);
    }

    public function update(
        EmployeeServicesRequest $request,
        EmployeeServiceConfiguration $serviceConfiguration
    ): EmployeeServicesResource {
        $serviceConfiguration = DB::transaction(function () use ($request, $serviceConfiguration) {
            $serviceConfiguration->update($request->only('valid_from', 'valid_to'));
            $this->replaceServiceDetails(
                $serviceConfiguration,
                $request->validated('services', [])
            );

            return $serviceConfiguration->refresh()->load('services.service');
        });

        return new EmployeeServicesResource($serviceConfiguration);
    }

    public function destroy(EmployeeServiceConfiguration $serviceConfiguration): JsonResponse
    {
        $serviceConfiguration->delete();

        return response()->json(['message' => 'Employee service configuration deleted successfully']);
    }

    private function replaceServiceDetails(EmployeeServiceConfiguration $serviceConfiguration, array $services): void
    {
        $serviceConfiguration->services()->delete();

        $this->createServiceDetails($serviceConfiguration, $services);
    }

    private function createServiceDetails(EmployeeServiceConfiguration $serviceConfiguration, array $services): void
    {
        foreach ($services as $service) {
            $serviceConfiguration->services()->create([
                'service_id' => $service['service_id'],
                'duration' => $service['duration'],
                'price' => $service['price'],
            ]);
        }
    }
}
