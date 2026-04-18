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
            EmployeeVersion::create(
                [
                    'employee_id' => $employee->id,
                    'is_available' => true,
                    'valid_from' => $validFrom,
                    'valid_to' => null,
                ]
            );
        }
    }
}
