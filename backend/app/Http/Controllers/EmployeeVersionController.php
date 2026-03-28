<?php

namespace App\Http\Controllers;

use App\Http\Requests\ShowEmployeeVersionValidAtRequest;
use App\Http\Requests\IndexEmployeeVersionsRequest;
use App\Http\Requests\StoreEmployeeVersionRequest;
use App\Http\Requests\UpdateEmployeeVersionRequest;
use App\Http\Resources\EmployeeVersionResource;
use App\Models\EmployeeVersion;
use Carbon\Carbon;
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

    public function store(StoreEmployeeVersionRequest $request): EmployeeVersionResource
    {
        $version = EmployeeVersion::create($request->validated());

        return new EmployeeVersionResource($version);
    }

    public function update(UpdateEmployeeVersionRequest $request, EmployeeVersion $employeeVersion): EmployeeVersionResource
    {
        $employeeVersion->update($request->validated());

        return new EmployeeVersionResource($employeeVersion);
    }

    public function destroy(EmployeeVersion $employeeVersion): JsonResponse
    {
        $employeeVersion->delete();

        return response()->json(['message' => 'Employee version deleted successfully']);
    }

    public function ShowEmployeeVersionValidAt(ShowEmployeeVersionValidAtRequest $request)
    {
        $validated = $request->validated();
        $employeeId = $validated['employee_id'];
        $date = Carbon::parse($validated['date']);

        $version = EmployeeVersion::query()
            ->validAt($date)
            ->where('employee_id', $employeeId)
            ->first();

        return new EmployeeVersionResource($version);
    }
}
