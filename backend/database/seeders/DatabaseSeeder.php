<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            CustomerSeeder::class,
            ServiceSeeder::class,
            ServiceVersionSeeder::class,
            EmployeeSeeder::class,
            EmployeeVersionSeeder::class,
            EmployeeGallerySeeder::class,
            EmployeeWorkingHourSeeder::class,
            EmployeeBreakSeeder::class,
            EmployeeTimeOffSeeder::class,
            EmployeeServiceSeeder::class,
            EmployeeServiceVersionSeeder::class,
            ShopScheduleSeeder::class,
            ShopHolidaySeeder::class,
            ShopSettingSeeder::class,
            AppointmentSeeder::class,
            ReviewSeeder::class,
        ]);
    }
}
