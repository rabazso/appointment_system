<?php

namespace App\Http\Controllers;

use App\Http\Resources\EmployeeAvailabilityResource;
use App\Http\Resources\EmployeeBookingRulesResource;
use App\Http\Resources\EmployeeScheduleResource;
use App\Http\Resources\EmployeeServicesResource;
use Illuminate\Http\Request;

class EmployeeOwnConfigurationController extends Controller
{
    public function services(Request $request)
    {
        $today = now()->startOfDay();
        $employee = $request->user()->employee;

        $serviceConfigurations = $employee->serviceConfigurations()
            ->with('services.service')
            ->currentAndUpcomingFrom($today)
            ->get();

        return EmployeeServicesResource::collection($serviceConfigurations);
    }

    public function schedules(Request $request)
    {
        $today = now()->startOfDay();
        $employee = $request->user()->employee;

        $schedules = $employee->scheduleConfigurations()
            ->with(['workingHours', 'breaks'])
            ->currentAndUpcomingFrom($today)
            ->get();

        return EmployeeScheduleResource::collection($schedules);
    }

    public function availability(Request $request)
    {
        $today = now()->startOfDay();
        $employee = $request->user()->employee;

        $availability = $employee->versions()
            ->currentAndUpcomingFrom($today)
            ->get();

        return EmployeeAvailabilityResource::collection($availability);
    }

    public function bookingRules(Request $request)
    {
        $today = now()->startOfDay();
        $employee = $request->user()->employee;

        $bookingRules = $employee->bookingRuleConfigurations()
            ->with('rules')
            ->currentAndUpcomingFrom($today)
            ->get();

        return EmployeeBookingRulesResource::collection($bookingRules);
    }
}
