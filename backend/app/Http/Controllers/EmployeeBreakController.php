<?php

namespace App\Http\Controllers;

use App\Http\Requests\IndexEmployeeBreaksRequest;
use App\Http\Requests\StoreEmployeeBreakRequest;
use App\Http\Requests\UpdateEmployeeBreakRequest;
use App\Http\Resources\EmployeeBreakResource;
use App\Models\EmployeeBreak;
use Illuminate\Http\JsonResponse;

class EmployeeBreakController extends Controller
{
    public function index(IndexEmployeeBreaksRequest $request)
    {
        $validated = $request->validated();
        $scheduleConfigurationId = $validated['schedule_configuration_id'] ?? null;
        $weekday = $validated['weekday'] ?? null;

        $breaks = EmployeeBreak::query()
            ->when(
                $scheduleConfigurationId,
                fn ($query) => $query->where('schedule_configuration_id', $scheduleConfigurationId)
            )
            ->when(
                $weekday !== null,
                fn ($query) => $query->where('weekday', $weekday)
            )
            ->get();

        return EmployeeBreakResource::collection($breaks);
    }

    public function store(StoreEmployeeBreakRequest $request): EmployeeBreakResource
    {
        $employeeBreak = EmployeeBreak::create($request->validated());

        return new EmployeeBreakResource($employeeBreak);
    }

    public function update(UpdateEmployeeBreakRequest $request, EmployeeBreak $employeeBreak): EmployeeBreakResource
    {
        $employeeBreak->update($request->validated());

        return new EmployeeBreakResource($employeeBreak);
    }

    public function destroy(EmployeeBreak $employeeBreak): JsonResponse
    {
        $employeeBreak->delete();

        return response()->json(['message' => 'Employee break deleted successfully']);
    }
}
