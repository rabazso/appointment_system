<?php

namespace App\Http\Controllers;

use App\Http\Requests\IndexEmployeeBookingRuleConfigurationsRequest;
use App\Http\Requests\StoreEmployeeBookingRuleConfigurationRequest;
use App\Http\Requests\UpdateEmployeeBookingRuleConfigurationRequest;
use App\Http\Resources\EmployeeBookingRuleConfigurationResource;
use App\Models\Employee;
use App\Models\EmployeeBookingRuleConfiguration;
use App\Services\Timeline\VersionTimelineService;
use Illuminate\Http\JsonResponse;

class EmployeeBookingRuleConfigurationController extends Controller
{
    public function index(IndexEmployeeBookingRuleConfigurationsRequest $request)
    {
        $validated = $request->validated();
        $employeeId = $validated['employee_id'] ?? null;

        $configurations = EmployeeBookingRuleConfiguration::query()
            ->when(
                $employeeId,
                fn ($query) => $query->where('employee_id', $employeeId)
            )
            ->get();

        return EmployeeBookingRuleConfigurationResource::collection($configurations);
    }

    public function store(
        StoreEmployeeBookingRuleConfigurationRequest $request,
        VersionTimelineService $timelineService
    ): EmployeeBookingRuleConfigurationResource {
        $validated = $request->validated();
        $employee = Employee::findOrFail($validated['employee_id']);

        $configuration = $timelineService->createVersion(
            $employee->bookingRuleConfigurations(),
            ['valid_from' => $request->validated('valid_from')]
        );

        return new EmployeeBookingRuleConfigurationResource($configuration);
    }

    public function update(
        UpdateEmployeeBookingRuleConfigurationRequest $request,
        EmployeeBookingRuleConfiguration $employeeBookingRuleConfiguration,
        VersionTimelineService $timelineService
    ): EmployeeBookingRuleConfigurationResource {
        $employeeBookingRuleConfiguration = $timelineService->updateVersion(
            $employeeBookingRuleConfiguration->employee->bookingRuleConfigurations(),
            $employeeBookingRuleConfiguration,
            ['valid_from' => $request->validated('valid_from')]

        );

        return new EmployeeBookingRuleConfigurationResource($employeeBookingRuleConfiguration);
    }

    public function destroy(
        EmployeeBookingRuleConfiguration $employeeBookingRuleConfiguration,
        VersionTimelineService $timelineService
    ): JsonResponse {
        $timelineService->deleteVersion(
            $employeeBookingRuleConfiguration->employee->bookingRuleConfigurations(),
            $employeeBookingRuleConfiguration
        );

        return response()->json(['message' => 'Employee booking rule configuration deleted successfully']);
    }
}
