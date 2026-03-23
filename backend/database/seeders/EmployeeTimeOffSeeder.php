<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\EmployeeTimeOff;
use Illuminate\Database\Seeder;

class EmployeeTimeOffSeeder extends Seeder
{
    public function run(): void
    {
        $definitions = [
            'Blowout Ben' => [
                [
                    'date' => now()->addDays(7)->toDateString(),
                    'type' => 'sickness',
                    'status' => 'requested',
                    'note' => 'A reason for the time off.',
                ],
            ],
            'Crispy Chris' => [
                [
                    'date' => now()->addDays(8)->toDateString(),
                    'type' => 'vacation',
                    'status' => 'cancel_requested',
                    'note' => 'A reason for the time off.',
                ],
            ],
            'Bouncy Bella' => [
                [
                    'date' => now()->addDays(9)->toDateString(),
                    'type' => 'vacation',
                    'status' => 'approved',
                    'note' => 'A reason for the time off.',
                ],
            ],
            'Loud Lucy' => [
                [
                    'date' => now()->addDays(10)->toDateString(),
                    'type' => 'emergency',
                    'status' => 'cancelled',
                    'note' => 'A reason for the time off.',
                ],
            ],
        ];

        foreach ($definitions as $employeeName => $timeOffEntries) {
            $employee = Employee::where('name', $employeeName)->first();

            foreach ($timeOffEntries as $entry) {
                EmployeeTimeOff::create([
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
