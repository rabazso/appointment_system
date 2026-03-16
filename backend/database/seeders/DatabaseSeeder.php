<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            ServiceSeeder::class,
            UserSeeder::class,
            EmployeeSeeder::class,
            AppointmentSeeder::class,
            ReviewSeeder::class,
        ]);
    }
}
