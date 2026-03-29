<?php

namespace App\Http\Controllers;

use App\Http\Requests\IndexEmployeeServiceConfigurationsRequest;
use App\Http\Requests\StoreEmployeeServiceConfigurationRequest;
use App\Http\Requests\UpdateEmployeeServiceConfigurationRequest;
use App\Http\Resources\EmployeeServiceConfigurationResource;
use App\Models\EmployeeServiceConfiguration;
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

    public function store(StoreEmployeeServiceConfigurationRequest $request): EmployeeServiceConfigurationResource
    {
        $configuration = EmployeeServiceConfiguration::create($request->validated());

        return new EmployeeServiceConfigurationResource($configuration);
    }

    public function update(
        UpdateEmployeeServiceConfigurationRequest $request,
        EmployeeServiceConfiguration $employeeServiceConfiguration
    ): EmployeeServiceConfigurationResource {
        $employeeServiceConfiguration->update($request->validated());

        return new EmployeeServiceConfigurationResource($employeeServiceConfiguration);
    }

    public function destroy(EmployeeServiceConfiguration $employeeServiceConfiguration): JsonResponse
    {
        $employeeServiceConfiguration->delete();

        return response()->json(['message' => 'Employee service configuration deleted successfully']);
    }
}
