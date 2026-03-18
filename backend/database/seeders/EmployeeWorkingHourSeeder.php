<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\EmployeeWorkingHour;
use Illuminate\Database\Seeder;

class EmployeeWorkingHourSeeder extends Seeder
{
    public function run(): void
    {
        $validFrom = now()->toDateString();

        foreach (Employee::all() as $employee) {
            for ($weekday = 1; $weekday <= 5; $weekday++) {
                EmployeeWorkingHour::create(
                    [
                        'employee_id' => $employee->id,
                        'weekday' => $weekday,
                        'valid_from' => $validFrom,
                        'start_time' => '09:00:00',
                        'end_time' => '21:00:00',
                        'valid_to' => null,
                    ]
                );
            }
        }
    }
}
