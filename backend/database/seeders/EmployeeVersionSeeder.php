<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\EmployeeVersion;
use Illuminate\Database\Seeder;

class EmployeeVersionSeeder extends Seeder
{
    public function run(): void
    {
        $validFrom = now()->startOfDay();

        foreach (Employee::all() as $employee) {
            $usesDefaultBookingInterval = (bool) random_int(0, 1);
            $usesDefaultBookingWindow = (bool) random_int(0, 1);

            EmployeeVersion::create(
                [
                    'employee_id' => $employee->id,
                    'valid_from' => $validFrom,
                    'uses_default_booking_interval' => $usesDefaultBookingInterval,
                    'booking_interval_minutes' => $usesDefaultBookingInterval ? null : 15,
                    'uses_default_booking_window' => $usesDefaultBookingWindow,
                    'booking_window_days' => $usesDefaultBookingWindow ? null : 15,
                    'valid_to' => null,
                ]
            );
        }
    }
}
