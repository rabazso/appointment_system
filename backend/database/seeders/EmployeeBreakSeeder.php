<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\EmployeeBreak;
use Illuminate\Database\Seeder;

class EmployeeBreakSeeder extends Seeder
{
    public function run(): void
    {
        $validFrom = now()->toDateString();

        foreach (Employee::all() as $employee) {
            for ($weekday = 1; $weekday <= 5; $weekday++) {
                EmployeeBreak::create(
                    [
                        'employee_id' => $employee->id,
                        'weekday' => $weekday,
                        'valid_from' => $validFrom,
                        'start_time' => '12:00:00',
                        'end_time' => '13:00:00',
                        'valid_to' => null,
                    ]
                );
            }
        }
    }
}
