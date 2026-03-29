<?php

namespace App\Http\Controllers;

use App\Http\Requests\IndexEmployeeWorkingHoursRequest;
use App\Http\Requests\StoreEmployeeWorkingHourRequest;
use App\Http\Requests\UpdateEmployeeWorkingHourRequest;
use App\Http\Resources\EmployeeWorkingHourResource;
use App\Models\EmployeeWorkingHour;
use Illuminate\Http\JsonResponse;

class EmployeeWorkingHourController extends Controller
{
    public function index(IndexEmployeeWorkingHoursRequest $request)
    {
        $validated = $request->validated();
        $scheduleConfigurationId = $validated['schedule_configuration_id'] ?? null;
        $weekday = $validated['weekday'] ?? null;

        $workingHours = EmployeeWorkingHour::query()
            ->when(
                $scheduleConfigurationId,
                fn ($query) => $query->where('schedule_configuration_id', $scheduleConfigurationId)
            )
            ->when(
                $weekday !== null,
                fn ($query) => $query->where('weekday', $weekday)
            )
            ->get();

        return EmployeeWorkingHourResource::collection($workingHours);
    }

    public function store(StoreEmployeeWorkingHourRequest $request): EmployeeWorkingHourResource
    {
        $workingHour = EmployeeWorkingHour::create($request->validated());

        return new EmployeeWorkingHourResource($workingHour);
    }

    public function update(
        UpdateEmployeeWorkingHourRequest $request,
        EmployeeWorkingHour $employeeWorkingHour
    ): EmployeeWorkingHourResource {
        $employeeWorkingHour->update($request->validated());

        return new EmployeeWorkingHourResource($employeeWorkingHour);
    }

    public function destroy(EmployeeWorkingHour $employeeWorkingHour): JsonResponse
    {
        $employeeWorkingHour->delete();

        return response()->json(['message' => 'Employee working hour deleted successfully']);
    }
}
