<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\EmployeeTimeOffRequest;
use Illuminate\Database\Seeder;

class EmployeeTimeOffRequestSeeder extends Seeder
{
    public function run(): void
    {
        $definitions = [
            'Blowout Ben' => [
                [
                    'date' => now()->addDays(3)->toDateString(),
                    'type' => 'sickness',
                    'status' => 'pending',
                    'note' => 'A reason for the time off.',
                ],
            ],
            'Crispy Chris' => [
                [
                    'date' => now()->addDays(7)->toDateString(),
                    'type' => 'vacation',
                    'status' => 'cancelled',
                    'note' => 'A reason for the time off.',
                ],
            ],
            'Bouncy Bella' => [
                [
                    'date' => now()->addDays(10)->toDateString(),
                    'type' => 'vacation',
                    'status' => 'approved',
                    'note' => 'A reason for the time off.',
                ],
            ],
            'Loud Lucy' => [
                [
                    'date' => now()->addDays(14)->toDateString(),
                    'type' => 'personal',
                    'status' => 'cancelled',
                    'note' => 'A reason for the time off.',
                ],
            ],
        ];

        foreach ($definitions as $employeeName => $timeOffEntries) {
            $employee = Employee::where('name', $employeeName)->first();

            foreach ($timeOffEntries as $entry) {
                EmployeeTimeOffRequest::create([
                    'employee_id' => $employee->id,
                    'date' => $entry['date'],
                    'type' => $entry['type'],
                    'status' => $entry['status'],
                    'note' => $entry['note'],
                ]);
            }
        }
    }
}
