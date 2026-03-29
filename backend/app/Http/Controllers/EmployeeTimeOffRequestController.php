<?php

namespace App\Http\Controllers;

use App\Http\Requests\IndexEmployeeTimeOffRequestsRequest;
use App\Http\Requests\StoreEmployeeTimeOffRequestRequest;
use App\Http\Requests\UpdateEmployeeTimeOffRequestRequest;
use App\Http\Resources\EmployeeTimeOffRequestResource;
use App\Models\EmployeeTimeOffRequest;
use Illuminate\Http\JsonResponse;

class EmployeeTimeOffRequestController extends Controller
{
    public function index(IndexEmployeeTimeOffRequestsRequest $request)
    {
        $validated = $request->validated();
        $employeeId = $validated['employee_id'] ?? null;
        $date = $validated['date'] ?? null;
        $type = $validated['type'] ?? null;
        $status = $validated['status'] ?? null;

        $timeOffRequests = EmployeeTimeOffRequest::query()
            ->when(
                $employeeId,
                fn ($query) => $query->where('employee_id', $employeeId)
            )
            ->when(
                $date,
                fn ($query) => $query->whereDate('date', $date)
            )
            ->when(
                $type,
                fn ($query) => $query->where('type', $type)
            )
            ->when(
                $status,
                fn ($query) => $query->where('status', $status)
            )
            ->get();

        return EmployeeTimeOffRequestResource::collection($timeOffRequests);
    }

    public function store(StoreEmployeeTimeOffRequestRequest $request): EmployeeTimeOffRequestResource
    {
        $timeOffRequest = EmployeeTimeOffRequest::create($request->validated());

        return new EmployeeTimeOffRequestResource($timeOffRequest);
    }

    public function update(
        UpdateEmployeeTimeOffRequestRequest $request,
        EmployeeTimeOffRequest $employeeTimeOffRequest
    ): EmployeeTimeOffRequestResource {
        $employeeTimeOffRequest->update($request->validated());

        return new EmployeeTimeOffRequestResource($employeeTimeOffRequest);
    }

    public function destroy(EmployeeTimeOffRequest $employeeTimeOffRequest): JsonResponse
    {
        $employeeTimeOffRequest->delete();

        return response()->json(['message' => 'Employee time off request deleted successfully']);
    }
}
