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
            for ($weekday = 0; $weekday <= 6; $weekday++) {
                $isWorkingDay = $weekday >= 1 && $weekday <= 5;

                EmployeeWorkingHour::create(
                    [
                        'schedule_configuration_id' => $configuration->id,
                        'weekday' => $weekday,
                        'start_time' => $isWorkingDay ? '09:00:00' : null,
                        'end_time' => $isWorkingDay ? '21:00:00' : null,
                    ]
                );
            }
        }
    }
}
