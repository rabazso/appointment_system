<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEmployeeRequest;
use App\Http\Resources\EmployeeResource;
use App\Models\Employee;
use App\Models\EmployeeBookingRuleConfiguration;
use App\Models\EmployeeScheduleConfiguration;
use App\Models\EmployeeServiceConfiguration;
use App\Models\User;
use App\Models\EmployeeVersion;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class EmployeeController extends Controller
{
    public function index()
    {
        return EmployeeResource::collection(Employee::with(['profileImage', 'user'])->get());
    }

    public function store(StoreEmployeeRequest $request): EmployeeResource
    {
        $employee = DB::transaction(function () use ($request) {
            $user = User::create([
                'email' => $request->validated('email'),
                'password' => Hash::make(Str::random(40)),
                'role' => 'employee',
            ]);

            $employee = Employee::create([
                'user_id' => $user->id,
                'name' => $request->validated('name'),
                'phone' => $request->validated('phone'),
            ]);

            $this->createDefaultConfigurations($employee);

            event(new Registered($user));
            Password::sendResetLink(['email' => $user->email]);

            return $employee;
        });

        return new EmployeeResource($employee->load(['profileImage', 'user']));
    }

    public function destroy(Employee $employee): JsonResponse
    {
        $employee->delete();

        return response()->json(['message' => 'Employee deleted successfully']);
    }

    public function indexEmployeesWithValidVersion()
    {
        $now = now();

        $employees = Employee::with([
            'profileImage',
            'versions' => fn ($query) => $query->validAt($now),
        ])->get();

        return EmployeeResource::collection($employees);
    }

    private function createDefaultConfigurations(Employee $employee): void
    {
        $validFrom = now()->startOfDay();

        EmployeeVersion::create([
            'employee_id' => $employee->id,
            'is_available' => false,
            'valid_from' => $validFrom,
            'valid_to' => null,
        ]);

        EmployeeServiceConfiguration::create([
            'employee_id' => $employee->id,
            'valid_from' => $validFrom,
            'valid_to' => null,
        ]);

        $scheduleConfiguration = EmployeeScheduleConfiguration::create([
            'employee_id' => $employee->id,
            'valid_from' => $validFrom,
            'valid_to' => null,
        ]);

        foreach (range(0, 6) as $weekday) {
            $scheduleConfiguration->workingHours()->create([
                'weekday' => $weekday,
                'start_time' => null,
                'end_time' => null,
            ]);
        }

        $bookingRuleConfiguration = EmployeeBookingRuleConfiguration::create([
            'employee_id' => $employee->id,
            'valid_from' => $validFrom,
            'valid_to' => null,
        ]);

        $bookingRuleConfiguration->rules()->create([
            'booking_interval_minutes' => 30,
            'booking_window_days' => 30,
        ]);
    }
}
