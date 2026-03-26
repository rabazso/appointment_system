<?php

namespace Database\Seeders;

use App\Models\EmployeeBreak;
use App\Models\EmployeeScheduleConfiguration;
use Illuminate\Database\Seeder;

class EmployeeBreakSeeder extends Seeder
{
    public function run(): void
    {
        foreach (EmployeeScheduleConfiguration::all() as $configuration) {
            for ($weekday = 1; $weekday <= 5; $weekday++) {
                EmployeeBreak::create(
                    [
                        'schedule_configuration_id' => $configuration->id,
                        'weekday' => $weekday,
                        'start_time' => '12:00:00',
                        'end_time' => '13:00:00',
                    ]
                );
            }
        }
    }
}
