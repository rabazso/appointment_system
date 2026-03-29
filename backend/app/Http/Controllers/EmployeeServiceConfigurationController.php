<?php

namespace App\Http\Controllers;

use App\Http\Requests\IndexEmployeeServiceConfigurationsRequest;
use App\Http\Requests\ShowEmployeeServiceConfigurationValidAtRequest;
use App\Http\Requests\StoreEmployeeServiceConfigurationRequest;
use App\Http\Requests\UpdateEmployeeServiceConfigurationRequest;
use App\Http\Resources\EmployeeServiceConfigurationResource;
use App\Models\EmployeeServiceConfiguration;
use Carbon\Carbon;
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

    public function showValidAt(ShowEmployeeServiceConfigurationValidAtRequest $request): EmployeeServiceConfigurationResource
    {
        $validated = $request->validated();
        $employeeId = $validated['employee_id'];
        $date = Carbon::parse($validated['date']);

        $configuration = EmployeeServiceConfiguration::query()
            ->validAt($date)
            ->where('employee_id', $employeeId)
            ->firstOrFail();

        return new EmployeeServiceConfigurationResource($configuration);
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
