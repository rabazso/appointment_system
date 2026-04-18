<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\EmployeeBookingRuleConfiguration;
use Illuminate\Database\Seeder;

class EmployeeBookingRuleConfigurationSeeder extends Seeder
{
    public function run(): void
    {
        $validFrom = now()->startOfDay();

        foreach (Employee::all() as $employee) {
            EmployeeBookingRuleConfiguration::create([
                'employee_id' => $employee->id,
                'valid_from' => $validFrom,
                'valid_to' => null,
            ]);
        }
    }
}
