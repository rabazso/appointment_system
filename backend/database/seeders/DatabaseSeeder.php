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
            EmployeeServiceConfigurationSeeder::class,
            EmployeeScheduleConfigurationSeeder::class,
            EmployeeImageSeeder::class,
            EmployeeWorkingHourSeeder::class,
            EmployeeBreakSeeder::class,
            EmployeeBookingRuleSeeder::class,
            EmployeeTimeOffRequestSeeder::class,
            ShopOpeningHourSeeder::class,
            ShopSpecialDaySeeder::class,
            ShopSettingSeeder::class,
            AppointmentSeeder::class,
            ReviewSeeder::class,
            UserSeeder::class,
        ]);
    }
}
