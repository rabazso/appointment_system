<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Employee;
use App\Models\Service;
use App\Models\EmployeeService;
use App\Models\WorkingHour;
use App\Models\Appointment;
use App\Models\Review;
use Faker\Factory as Faker;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create();

        $customers = [];
        for ($i = 0; $i < 10; $i++) {
            $customers[] = User::create([
                'name' => $faker->name,
                'email' => $faker->unique()->safeEmail,
                'phone' => $faker->unique()->phoneNumber,
                'password' => Hash::make('password'),
            ]);
        }

        $employees = [];
        for ($i = 0; $i < 5; $i++) {
            $user = User::create([
                'name' => $faker->name,
                'email' => $faker->unique()->safeEmail,
                'phone' => $faker->unique()->phoneNumber,
                'password' => Hash::make('password'),
            ]);

            $employees[] = Employee::create([
                'user_id' => $user->id,
                'bio' => $faker->paragraph,
                'photo_url' => $faker->imageUrl(200, 200, 'people'),
            ]);
        }

        $services = [];
        $serviceNames = ['Short haircut', 'Long haircut', 'Shave', 'Coloring'];
        foreach ($serviceNames as $name) {
            $services[] = Service::create([
                'name' => $name,
                'duration' => 30, 
                'description' => $faker->sentence,
            ]);
        }

        foreach ($employees as $employee) {
        $assignedServices = $faker->randomElements($services, rand(2, 4));
            foreach ($assignedServices as $service) {
            $employee->services()->attach($service->id, [
                'price' => $faker->numberBetween(30, 100),
            ]);
            }
        }

        foreach ($employees as $employee) {
            for ($day = 1; $day <= 5; $day++) {
                WorkingHour::create([
                    'employee_id' => $employee->id,
                    'weekday' => $day,
                    'start_time' => '12:00:00',
                    'end_time' => '20:00:00',
                ]);
    }
}
    }
}
