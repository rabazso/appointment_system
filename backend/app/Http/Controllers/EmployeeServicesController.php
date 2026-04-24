<?php

namespace App\Http\Controllers;

use App\Http\Requests\EmployeeServicesRequest;
use App\Http\Resources\EmployeeServicesResource;
use App\Models\Employee;
use App\Models\EmployeeServiceConfiguration;
use App\Services\Timeline\VersionTimelineService;
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

    public function store(
        EmployeeServicesRequest $request,
        Employee $employee,
        VersionTimelineService $timelineService
    ): EmployeeServicesResource
    {
        $serviceConfiguration = DB::transaction(function () use ($request, $employee, $timelineService) {
            $serviceConfiguration = $timelineService->createVersion(
                $employee->serviceConfigurations(),
                ['valid_from' => $request->validated('valid_from')]
            );
            $this->createServices(
                $serviceConfiguration,
                $request->validated('services')
            );

            return $serviceConfiguration->load('services.service');
        });

        return new EmployeeServicesResource($serviceConfiguration);
    }

    public function update(
        EmployeeServicesRequest $request,
        EmployeeServiceConfiguration $serviceConfiguration,
        VersionTimelineService $timelineService
    ): EmployeeServicesResource {
        $serviceConfiguration = DB::transaction(function () use ($request, $serviceConfiguration, $timelineService) {
            $serviceConfiguration = $timelineService->updateVersion(
                $serviceConfiguration->employee->serviceConfigurations(),
                $serviceConfiguration,
                ['valid_from' => $request->validated('valid_from')]
            );
            $this->replaceServices(
                $serviceConfiguration,
                $request->validated('services')
            );

            return $serviceConfiguration->refresh()->load('services.service');
        });

        return new EmployeeServicesResource($serviceConfiguration);
    }

    public function destroy(
        EmployeeServiceConfiguration $serviceConfiguration,
        VersionTimelineService $timelineService
    ): JsonResponse
    {
        $timelineService->deleteVersion($serviceConfiguration->employee->serviceConfigurations(), $serviceConfiguration);

        return response()->json(['message' => 'Employee service configuration deleted successfully']);
    }

    private function replaceServices(EmployeeServiceConfiguration $serviceConfiguration, array $services): void
    {
        $serviceConfiguration->services()->delete();

        $this->createServices($serviceConfiguration, $services);
    }

    private function createServices(EmployeeServiceConfiguration $serviceConfiguration, array $services): void
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
