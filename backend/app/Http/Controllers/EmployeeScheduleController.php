<?php

namespace App\Http\Controllers;

use App\Http\Requests\EmployeeScheduleRequest;
use App\Http\Resources\EmployeeScheduleResource;
use App\Models\Employee;
use App\Models\EmployeeScheduleConfiguration;
use App\Services\Timeline\VersionTimelineService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class EmployeeScheduleController extends Controller
{
    public function index(Employee $employee)
    {
        $today = now()->startOfDay();

        $schedules = $employee->scheduleConfigurations()
            ->with(['workingHours', 'breaks'])
            ->currentAndUpcomingFrom($today)
            ->get();

        return EmployeeScheduleResource::collection($schedules);
    }

    public function store(
        EmployeeScheduleRequest $request,
        Employee $employee,
        VersionTimelineService $timelineService
    ): EmployeeScheduleResource
    {
        $schedule = DB::transaction(function () use ($request, $employee, $timelineService) {
            $schedule = $timelineService->createVersion(
                $employee->scheduleConfigurations(),
                ['valid_from' => $request->validated('valid_from')]
            );
            $this->createScheduleDetails(
                $schedule,
                $request->validated('weeklyHours'),
                $request->validated('breaks')
            );

            return $schedule->load(['workingHours', 'breaks']);
        });

        return new EmployeeScheduleResource($schedule);
    }

    public function update(
        EmployeeScheduleRequest $request,
        EmployeeScheduleConfiguration $schedule,
        VersionTimelineService $timelineService
    ): EmployeeScheduleResource {
        $schedule = DB::transaction(function () use ($request, $schedule, $timelineService) {
            $schedule = $timelineService->updateVersion(
                $schedule->employee->scheduleConfigurations(),
                $schedule,
                ['valid_from' => $request->validated('valid_from')]
            );
            $this->replaceScheduleDetails(
                $schedule,
                $request->validated('weeklyHours'),
                $request->validated('breaks')
            );

            return $schedule->refresh()->load(['workingHours', 'breaks']);
        });

        return new EmployeeScheduleResource($schedule);
    }

    public function destroy(
        EmployeeScheduleConfiguration $schedule,
        VersionTimelineService $timelineService
    ): JsonResponse
    {
        $timelineService->deleteVersion($schedule->employee->scheduleConfigurations(), $schedule);

        return response()->json(['message' => 'Employee schedule deleted successfully']);
    }

    private function replaceScheduleDetails(EmployeeScheduleConfiguration $schedule, array $weeklyHours, array $breaks): void
    {
        $schedule->workingHours()->delete();
        $schedule->breaks()->delete();

        $this->createScheduleDetails($schedule, $weeklyHours, $breaks);
    }

    private function createScheduleDetails(EmployeeScheduleConfiguration $schedule, array $weeklyHours, array $breaks): void
    {
        foreach ($weeklyHours as $day) {
            $isOpen = (bool) $day['isOpen'];

            $schedule->workingHours()->create([
                'weekday' => $day['weekday'],
                'start_time' => $isOpen ? $day['start'] : null,
                'end_time' => $isOpen ? $day['end'] : null,
            ]);

        }

        foreach ($breaks as $break) {
            $schedule->breaks()->create([
                'weekday' => $break['weekday'],
                'start_time' => $break['start'],
                'end_time' => $break['end'],
            ]);
        }
    }
}
