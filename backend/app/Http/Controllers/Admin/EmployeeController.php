<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreEmployeeRequest;
use App\Http\Resources\Employee\EmployeeResource;
use App\Mail\EmployeeTemporaryPasswordMail;
use App\Models\Employee;
use App\Models\EmployeeBookingRuleConfiguration;
use App\Models\EmployeeScheduleConfiguration;
use App\Models\EmployeeServiceConfiguration;
use App\Models\EmployeeVersion;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class EmployeeController extends Controller
{
    public function store(StoreEmployeeRequest $request): EmployeeResource
    {
        $employee = DB::transaction(function () use ($request) {
            $temporaryPassword = Str::random(12);

            $user = User::create([
                'email' => $request->validated('email'),
                'password' => Hash::make($temporaryPassword),
                'role' => 'employee',
                'email_verified_at' => now(),
            ]);

            $employee = Employee::create([
                'user_id' => $user->id,
                'name' => $request->validated('name'),
                'phone' => $request->validated('phone'),
            ]);

            $this->createDefaultConfigurations($employee);

            return [
                'employee' => $employee,
                'user_email' => $user->email,
                'temporary_password' => $temporaryPassword,
            ];
        });

        Mail::to($employee['user_email'])->send(new EmployeeTemporaryPasswordMail(
            employeeName: $employee['employee']->name,
            email: $employee['user_email'],
            temporaryPassword: $employee['temporary_password'],
            loginUrl: config('app.frontend_url') . '/employee/login',
        ));

        return new EmployeeResource(
            $employee['employee']->load([
                'profileImage',
                'user',
                'versions' => fn ($query) => $query->validAt(now()),
            ])->loadAvg('reviews as rating', 'rating')
        );
    }

    public function destroy(Employee $employee): JsonResponse
    {
        $employee->user()?->delete();

        return response()->json(['message' => 'Employee deleted successfully']);
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
