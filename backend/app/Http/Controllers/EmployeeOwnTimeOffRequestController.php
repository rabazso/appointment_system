<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOwnEmployeeTimeOffRequestRequest;
use App\Http\Resources\OwnEmployeeTimeOffRequestResource;
use App\Models\EmployeeTimeOffRequest;
use Illuminate\Http\Request;

class EmployeeOwnTimeOffRequestController extends Controller
{
    public function index(Request $request)
    {
        $employee = $request->user()->employee;

        $timeOffRequests = $employee->timeOffRequests()->orderByDesc('date')->orderByDesc('id')->get();

        return OwnEmployeeTimeOffRequestResource::collection($timeOffRequests);
    }

    public function store(StoreOwnEmployeeTimeOffRequestRequest $request): OwnEmployeeTimeOffRequestResource
    {
        $employee = $request->user()->employee;
        
        $timeOffRequest = $employee->timeOffRequests()->create([...$request->validated(), 'status' => 'pending',]);

        return new OwnEmployeeTimeOffRequestResource($timeOffRequest);
    }

    public function cancel(Request $request, EmployeeTimeOffRequest $employeeTimeOffRequest)
    {
        $employee = $request->user()->employee;

        if ($employeeTimeOffRequest->employee_id !== $employee->id) {
            return response()->noContent(403);
        }

        if ($employeeTimeOffRequest->status !== 'pending') {
            return response()->noContent(409);
        }

        $employeeTimeOffRequest->update(['status' => 'cancelled']);

        return response()->noContent();
    }
}
