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
            EmployeeBookingRuleConfigurationSeeder::class,
            EmployeeBookingRuleSeeder::class,
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
            ShopInformationSeeder::class,
            AppointmentSeeder::class,
            ReviewSeeder::class,
            UserSeeder::class,
        ]);
    }
}
