<?php

namespace App\Http\Controllers;

use App\Http\Requests\IndexEmployeeScheduleConfigurationsRequest;
use App\Http\Requests\StoreEmployeeScheduleConfigurationRequest;
use App\Http\Requests\UpdateEmployeeScheduleConfigurationRequest;
use App\Http\Resources\EmployeeScheduleConfigurationResource;
use App\Models\EmployeeScheduleConfiguration;
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

    public function store(StoreEmployeeScheduleConfigurationRequest $request): EmployeeScheduleConfigurationResource
    {
        $configuration = EmployeeScheduleConfiguration::create($request->validated());

        return new EmployeeScheduleConfigurationResource($configuration);
    }

    public function update(
        UpdateEmployeeScheduleConfigurationRequest $request,
        EmployeeScheduleConfiguration $employeeScheduleConfiguration
    ): EmployeeScheduleConfigurationResource {
        $employeeScheduleConfiguration->update($request->validated());

        return new EmployeeScheduleConfigurationResource($employeeScheduleConfiguration);
    }

    public function destroy(EmployeeScheduleConfiguration $employeeScheduleConfiguration): JsonResponse
    {
        $employeeScheduleConfiguration->delete();

        return response()->json(['message' => 'Employee schedule configuration deleted successfully']);
    }
}
