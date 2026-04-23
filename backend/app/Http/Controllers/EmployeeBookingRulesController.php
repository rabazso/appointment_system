<?php

namespace App\Http\Controllers;

use App\Http\Requests\EmployeeBookingRulesRequest;
use App\Http\Resources\EmployeeBookingRulesResource;
use App\Models\Employee;
use App\Models\EmployeeBookingRuleConfiguration;
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

    public function store(EmployeeBookingRulesRequest $request, Employee $employee): EmployeeBookingRulesResource
    {
        $bookingRules = DB::transaction(function () use ($request, $employee) {
            $bookingRules = $employee->bookingRuleConfigurations()->create($request->only('valid_from', 'valid_to'));
            $this->createRule($bookingRules, $request->validated());

            return $bookingRules->load('rules');
        });

        return new EmployeeBookingRulesResource($bookingRules);
    }

    public function update(
        EmployeeBookingRulesRequest $request,
        EmployeeBookingRuleConfiguration $bookingRules
    ): EmployeeBookingRulesResource {
        $bookingRules = DB::transaction(function () use ($request, $bookingRules) {
            $bookingRules->update($request->only('valid_from', 'valid_to'));
            $bookingRules->rules()->delete();
            $this->createRule($bookingRules, $request->validated());

            return $bookingRules->refresh()->load('rules');
        });

        return new EmployeeBookingRulesResource($bookingRules);
    }

    public function destroy(EmployeeBookingRuleConfiguration $bookingRules): JsonResponse
    {
        $bookingRules->delete();

        return response()->json(['message' => 'Employee booking rules deleted successfully']);
    }

    private function createRule(EmployeeBookingRuleConfiguration $bookingRules, array $data): void
    {
        $bookingRules->rules()->create([
            'booking_interval_minutes' => $data['slot_interval_minutes'],
            'booking_window_days' => $data['max_advance_days'],
        ]);
    }
}
