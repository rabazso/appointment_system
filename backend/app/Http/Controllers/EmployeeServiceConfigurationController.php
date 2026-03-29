<?php

namespace App\Http\Controllers;

use App\Http\Requests\IndexEmployeeServiceConfigurationsRequest;
use App\Http\Requests\StoreEmployeeServiceConfigurationRequest;
use App\Http\Requests\UpdateEmployeeServiceConfigurationRequest;
use App\Http\Resources\EmployeeServiceConfigurationResource;
use App\Models\Employee;
use App\Models\EmployeeServiceConfiguration;
use App\Services\Timeline\VersionTimelineService;
use Illuminate\Http\JsonResponse;

class EmployeeServiceConfigurationController extends Controller
{
    public function index(IndexEmployeeServiceConfigurationsRequest $request)
    {
        $validated = $request->validated();
        $employeeId = $validated['employee_id'] ?? null;

        $configurations = EmployeeServiceConfiguration::query()
            ->when(
                $employeeId,
                fn ($query) => $query->where('employee_id', $employeeId)
            )
            ->get();

        return EmployeeServiceConfigurationResource::collection($configurations);
    }

    public function store(StoreEmployeeServiceConfigurationRequest $request, VersionTimelineService $timelineService): EmployeeServiceConfigurationResource
    {
        $validated = $request->validated();
        $employee = Employee::findOrFail($validated['employee_id']);

        $configuration = $timelineService->createVersion($employee->serviceConfigurations(), $validated);

        return new EmployeeServiceConfigurationResource($configuration);
    }

    public function update(UpdateEmployeeServiceConfigurationRequest $request, EmployeeServiceConfiguration $employeeServiceConfiguration,
    VersionTimelineService $timelineService): EmployeeServiceConfigurationResource
    {
        $employeeServiceConfiguration = $timelineService->updateVersion($employeeServiceConfiguration->employee->serviceConfigurations(),
        $employeeServiceConfiguration, $request->validated());

        return new EmployeeServiceConfigurationResource($employeeServiceConfiguration);
    }

    public function destroy(EmployeeServiceConfiguration $employeeServiceConfiguration, VersionTimelineService $timelineService): JsonResponse
    {
        $timelineService->deleteVersion($employeeServiceConfiguration->employee->serviceConfigurations(), $employeeServiceConfiguration);

        return response()->json(['message' => 'Employee service configuration deleted successfully']);
    }
}
