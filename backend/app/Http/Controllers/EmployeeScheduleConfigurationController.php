<?php

namespace App\Http\Controllers;

use App\Http\Requests\IndexEmployeeScheduleConfigurationsRequest;
use App\Http\Requests\StoreEmployeeScheduleConfigurationRequest;
use App\Http\Requests\UpdateEmployeeScheduleConfigurationRequest;
use App\Http\Resources\EmployeeScheduleConfigurationResource;
use App\Models\Employee;
use App\Models\EmployeeScheduleConfiguration;
use App\Services\Timeline\VersionTimelineService;
use Illuminate\Http\JsonResponse;

class EmployeeScheduleConfigurationController extends Controller
{
    public function index(IndexEmployeeScheduleConfigurationsRequest $request)
    {
        $validated = $request->validated();
        $employeeId = $validated['employee_id'] ?? null;

        $configurations = EmployeeScheduleConfiguration::query()
            ->when(
                $employeeId,
                fn ($query) => $query->where('employee_id', $employeeId)
            )
            ->get();

        return EmployeeScheduleConfigurationResource::collection($configurations);
    }

    public function store(StoreEmployeeScheduleConfigurationRequest $request, VersionTimelineService $timelineService): EmployeeScheduleConfigurationResource
    {
        $validated = $request->validated();
        $employee = Employee::findOrFail($validated['employee_id']);

        $configuration = $timelineService->createVersion(
            $employee->scheduleConfigurations(),
            ['valid_from' => $request->validated('valid_from')]
        );

        return new EmployeeScheduleConfigurationResource($configuration);
    }

    public function update(UpdateEmployeeScheduleConfigurationRequest $request, EmployeeScheduleConfiguration $employeeScheduleConfiguration, 
    VersionTimelineService $timelineService): EmployeeScheduleConfigurationResource
    {
        $employeeScheduleConfiguration = $timelineService->updateVersion(
            $employeeScheduleConfiguration->employee->scheduleConfigurations(),
            $employeeScheduleConfiguration,
            ['valid_from' => $request->validated('valid_from')]
        );

        return new EmployeeScheduleConfigurationResource($employeeScheduleConfiguration);
    }

    public function destroy(EmployeeScheduleConfiguration $employeeScheduleConfiguration, VersionTimelineService $timelineService): JsonResponse
    {
        $timelineService->deleteVersion($employeeScheduleConfiguration->employee->scheduleConfigurations(), $employeeScheduleConfiguration);

        return response()->json(['message' => 'Employee schedule configuration deleted successfully']);
    }
}
