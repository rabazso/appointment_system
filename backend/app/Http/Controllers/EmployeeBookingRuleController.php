<?php

namespace App\Http\Controllers;

use App\Http\Requests\IndexEmployeeBookingRulesRequest;
use App\Http\Requests\StoreEmployeeBookingRuleRequest;
use App\Http\Requests\UpdateEmployeeBookingRuleRequest;
use App\Http\Resources\EmployeeBookingRuleResource;
use App\Models\EmployeeBookingRule;
use Illuminate\Http\JsonResponse;

class EmployeeBookingRuleController extends Controller
{
    public function index(IndexEmployeeBookingRulesRequest $request)
    {
        $validated = $request->validated();
        $configurationId = $validated['booking_rule_configuration_id'] ?? null;

        $rules = EmployeeBookingRule::query()
            ->when(
                $configurationId,
                fn ($query) => $query->where('booking_rule_configuration_id', $configurationId)
            )
            ->get();

        return EmployeeBookingRuleResource::collection($rules);
    }

    public function store(StoreEmployeeBookingRuleRequest $request): EmployeeBookingRuleResource
    {
        $rule = EmployeeBookingRule::create($request->validated());

        return new EmployeeBookingRuleResource($rule);
    }

    public function update(
        UpdateEmployeeBookingRuleRequest $request,
        EmployeeBookingRule $employeeBookingRule
    ): EmployeeBookingRuleResource {
        $employeeBookingRule->update($request->validated());

        return new EmployeeBookingRuleResource($employeeBookingRule);
    }

    public function destroy(EmployeeBookingRule $employeeBookingRule): JsonResponse
    {
        $employeeBookingRule->delete();

        return response()->json(['message' => 'Employee booking rule deleted successfully']);
    }
}
