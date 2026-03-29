<?php

namespace App\Http\Controllers;

use App\Http\Requests\IndexEmployeeVersionsRequest;
use App\Http\Requests\StoreEmployeeVersionRequest;
use App\Http\Requests\UpdateEmployeeVersionRequest;
use App\Http\Resources\EmployeeVersionResource;
use App\Models\Employee;
use App\Models\EmployeeVersion;
use App\Services\Timeline\VersionTimelineService;
use Illuminate\Http\JsonResponse;

class EmployeeVersionController extends Controller
{
    public function index(IndexEmployeeVersionsRequest $request)
    {
        $validated = $request->validated();
        $employeeId = $validated['employee_id'] ?? null;

        $versions = EmployeeVersion::when(
            $employeeId,
            fn ($query) => $query->where('employee_id', $employeeId)
        )->get();

        return EmployeeVersionResource::collection($versions);
    }

    public function store(StoreEmployeeVersionRequest $request, VersionTimelineService $timelineService): EmployeeVersionResource
    {
        $validated = $request->validated();
        $employee = Employee::findOrFail($validated['employee_id']);

        $version = $timelineService->createVersion($employee->versions(), $validated);

        return new EmployeeVersionResource($version);
    }

    public function update(UpdateEmployeeVersionRequest $request, EmployeeVersion $employeeVersion, VersionTimelineService $timelineService): EmployeeVersionResource
    {
        $employeeVersion = $timelineService->updateVersion($employeeVersion->employee->versions(), $employeeVersion, $request->validated());

        return new EmployeeVersionResource($employeeVersion);
    }

    public function destroy(EmployeeVersion $employeeVersion, VersionTimelineService $timelineService): JsonResponse
    {
        $timelineService->deleteVersion($employeeVersion->employee->versions(), $employeeVersion);

        return response()->json(['message' => 'Employee version deleted successfully']);
    }
}
