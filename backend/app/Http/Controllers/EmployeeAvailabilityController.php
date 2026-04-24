<?php

namespace App\Http\Controllers;

use App\Http\Requests\EmployeeAvailabilityRequest;
use App\Http\Resources\EmployeeAvailabilityResource;
use App\Models\Employee;
use App\Models\EmployeeVersion;
use App\Services\Timeline\VersionTimelineService;
use Illuminate\Http\JsonResponse;

class EmployeeAvailabilityController extends Controller
{
    public function index(Employee $employee)
    {
        $availability = $employee->versions()
            ->orderBy('valid_from')
            ->get();

        return EmployeeAvailabilityResource::collection($availability);
    }

    public function store(
        EmployeeAvailabilityRequest $request,
        Employee $employee,
        VersionTimelineService $timelineService
    ): EmployeeAvailabilityResource {
        $availability = $timelineService->createVersion(
            $employee->versions(),
            $request->validated()
        );

        return new EmployeeAvailabilityResource($availability);
    }

    public function update(
        EmployeeAvailabilityRequest $request,
        EmployeeVersion $availability,
        VersionTimelineService $timelineService
    ): EmployeeAvailabilityResource {
        $availability = $timelineService->updateVersion(
            $availability->employee->versions(),
            $availability,
            $request->validated()
        );

        return new EmployeeAvailabilityResource($availability);
    }

    public function destroy(EmployeeVersion $availability, VersionTimelineService $timelineService): JsonResponse
    {
        $timelineService->deleteVersion($availability->employee->versions(), $availability);

        return response()->json(['message' => 'Employee availability deleted successfully']);
    }

}
