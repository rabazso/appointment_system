<?php

namespace App\Http\Controllers;

use App\Http\Requests\IndexEmployeeServicesRequest;
use App\Http\Requests\StoreEmployeeServiceRequest;
use App\Http\Requests\UpdateEmployeeServiceRequest;
use App\Http\Resources\EmployeeServiceResource;
use App\Models\EmployeeService;
use Illuminate\Http\JsonResponse;

class EmployeeServiceController extends Controller
{
    public function index(IndexEmployeeServicesRequest $request)
    {
        $validated = $request->validated();
        $configurationId = $validated['configuration_id'] ?? null;
        $serviceId = $validated['service_id'] ?? null;

        $employeeServices = EmployeeService::query()
            ->when(
                $configurationId,
                fn ($query) => $query->where('configuration_id', $configurationId)
            )
            ->when(
                $serviceId,
                fn ($query) => $query->where('service_id', $serviceId)
            )
            ->get();

        return EmployeeServiceResource::collection($employeeServices);
    }

    public function store(StoreEmployeeServiceRequest $request): EmployeeServiceResource
    {
        $employeeService = EmployeeService::create($request->validated());

        return new EmployeeServiceResource($employeeService);
    }

    public function update(UpdateEmployeeServiceRequest $request, EmployeeService $employeeService): EmployeeServiceResource
    {
        $employeeService->update($request->validated());

        return new EmployeeServiceResource($employeeService);
    }

    public function destroy(EmployeeService $employeeService): JsonResponse
    {
        $employeeService->delete();

        return response()->json(['message' => 'Employee service deleted successfully']);
    }
}
