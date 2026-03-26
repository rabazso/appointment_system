<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\EmployeeScheduleConfiguration;
use Illuminate\Database\Seeder;

class EmployeeScheduleConfigurationSeeder extends Seeder
{
    public function run(): void
    {
        $validFrom = now()->startOfDay();

        foreach (Employee::all() as $employee) {
            EmployeeScheduleConfiguration::create([
                'employee_id' => $employee->id,
                'valid_from' => $validFrom,
                'valid_to' => null,
            ]);
        }
    }
}
