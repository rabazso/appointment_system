<?php

namespace Database\Seeders;

use App\Models\EmployeeScheduleConfiguration;
use App\Models\EmployeeWorkingHour;
use Illuminate\Database\Seeder;

class EmployeeWorkingHourSeeder extends Seeder
{
    public function run(): void
    {
        foreach (EmployeeScheduleConfiguration::all() as $configuration) {
            for ($weekday = 1; $weekday <= 5; $weekday++) {
                EmployeeWorkingHour::create(
                    [
                        'schedule_configuration_id' => $configuration->id,
                        'weekday' => $weekday,
                        'start_time' => '09:00:00',
                        'end_time' => '21:00:00',
                    ]
                );
            }
        }
    }
}
