<?php

namespace App\Http\Controllers;

use App\Http\Requests\IndexEmployeeTimeOffRequestsRequest;
use App\Http\Requests\StoreEmployeeTimeOffRequestRequest;
use App\Http\Requests\UpdateEmployeeTimeOffRequestRequest;
use App\Http\Resources\EmployeeTimeOffRequestResource;
use App\Models\EmployeeTimeOffRequest;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class EmployeeTimeOffRequestController extends Controller
{
    public function index(IndexEmployeeTimeOffRequestsRequest $request)
    {
        $validated = $request->validated();
        $employeeId = $validated['employee_id'] ?? null;
        $date = $validated['date'] ?? null;
        $status = $validated['status'] ?? null;

        $timeOffRequests = EmployeeTimeOffRequest::query()
            ->with('employee')
            ->when(
                $employeeId,
                fn ($query) => $query->where('employee_id', $employeeId)
            )
            ->when(
                $date,
                fn ($query) => $query->whereDate('date', $date)
            )
            ->when(
                $status,
                fn ($query) => $query->where('status', $status)
            )
            ->get();

        return EmployeeTimeOffRequestResource::collection($timeOffRequests);
    }

    public function indexForMonth(IndexEmployeeTimeOffRequestsRequest $request)
    {
        $validated = $request->validate([
            'month' => ['required', 'date_format:Y-m'],
        ]);

        $startOfMonth = Carbon::createFromFormat('Y-m', $validated['month'])->startOfMonth();
        $endOfMonth = $startOfMonth->copy()->endOfMonth();

        $timeOffRequests = EmployeeTimeOffRequest::query()
            ->with('employee')
            ->whereBetween('date', [$startOfMonth->toDateString(), $endOfMonth->toDateString()])
            ->orderBy('date')
            ->get();

        return EmployeeTimeOffRequestResource::collection($timeOffRequests);
    }

    public function store(StoreEmployeeTimeOffRequestRequest $request): EmployeeTimeOffRequestResource
    {
        $timeOffRequest = EmployeeTimeOffRequest::create($request->validated());

        return new EmployeeTimeOffRequestResource($timeOffRequest->load('employee'));
    }

    public function update(
        UpdateEmployeeTimeOffRequestRequest $request,
        EmployeeTimeOffRequest $employeeTimeOffRequest
    ): EmployeeTimeOffRequestResource {
        if ($employeeTimeOffRequest->date < now()->toDateString()) {
            abort(409, 'Past time off requests cannot be modified.');
        }

        $employeeTimeOffRequest->update($request->validated());

        return new EmployeeTimeOffRequestResource($employeeTimeOffRequest->load('employee'));
    }

    public function destroy(EmployeeTimeOffRequest $employeeTimeOffRequest): JsonResponse
    {
        $employeeTimeOffRequest->delete();

        return response()->json(['message' => 'Employee time off request deleted successfully']);
    }
}
