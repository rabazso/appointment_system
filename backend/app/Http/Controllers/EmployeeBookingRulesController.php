<?php

namespace App\Http\Controllers;

use App\Http\Requests\EmployeeBookingRulesRequest;
use App\Http\Resources\EmployeeBookingRulesResource;
use App\Models\Employee;
use App\Models\EmployeeBookingRuleConfiguration;
use App\Services\Timeline\VersionTimelineService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class EmployeeBookingRulesController extends Controller
{
    public function index(Employee $employee)
    {
        $bookingRules = $employee->bookingRuleConfigurations()
            ->with('rules')
            ->orderBy('valid_from')
            ->get();

        return EmployeeBookingRulesResource::collection($bookingRules);
    }

    public function store(
        EmployeeBookingRulesRequest $request,
        Employee $employee,
        VersionTimelineService $timelineService
    ): EmployeeBookingRulesResource
    {
        $bookingRules = DB::transaction(function () use ($request, $employee, $timelineService) {
            $bookingRules = $timelineService->createVersion(
                $employee->bookingRuleConfigurations(),
                ['valid_from' => $request->validated('valid_from')]
            );
            $this->createRule($bookingRules, $request->validated());

            return $bookingRules->load('rules');
        });

        return new EmployeeBookingRulesResource($bookingRules);
    }

    public function update(
        EmployeeBookingRulesRequest $request,
        EmployeeBookingRuleConfiguration $bookingRules,
        VersionTimelineService $timelineService
    ): EmployeeBookingRulesResource {
        $bookingRules = DB::transaction(function () use ($request, $bookingRules, $timelineService) {
            $bookingRules = $timelineService->updateVersion(
                $bookingRules->employee->bookingRuleConfigurations(),
                $bookingRules,
                ['valid_from' => $request->validated('valid_from')]

            );
            $bookingRules->rules()->delete();
            $this->createRule($bookingRules, $request->validated());

            return $bookingRules->refresh()->load('rules');
        });

        return new EmployeeBookingRulesResource($bookingRules);
    }

    public function destroy(
        EmployeeBookingRuleConfiguration $bookingRules,
        VersionTimelineService $timelineService
    ): JsonResponse
    {
        $timelineService->deleteVersion($bookingRules->employee->bookingRuleConfigurations(), $bookingRules);

        return response()->json(['message' => 'Employee booking rules deleted successfully']);
    }

    private function createRule(EmployeeBookingRuleConfiguration $bookingRules, array $data): void
    {
        $bookingRules->rules()->create([
            'booking_interval_minutes' => $data['booking_interval_minutes'],
            'booking_window_days' => $data['booking_window_days'],
        ]);
    }

}
