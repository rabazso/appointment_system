<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Employee;
use App\Models\Service;
use App\Models\WorkingHour;
use Faker\Factory as Faker;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create();

        $services = [
            Service::create([
                'name' => 'Regular haircut',
                'duration' => 30,
                'description' => 'A quick, precise haircut for a clean, polished look. Perfect for busy schedules.'
            ]),
            Service::create([
                'name' => 'Perfect haircut',
                'duration' => 60,
                'description' => 'Basically the same as regular, but you give the barber more time, so he could be more precise.'
            ]),
            Service::create([
                'name' => 'Beard trim',
                'duration' => 30,
                'description' => 'Expert trimming and shaping to maintain a sharp, well-groomed beard.'
            ]),
            Service::create([
                'name' => 'Fullbox',
                'duration' => 60,
                'description' => 'A bundle option for a regular haircut and a beard trim.'
            ]),
        ];

        $employeesName = [
            'Blowout Ben',
            'Crispy Chris',
            'Haircut Harry',
            'Bouncy Bella',
            'Loud Lucy'
        ];

        $employeesBio = [
            'Blowout Ben' => 'My blowouts give your hair shine, volume, and a flawless finish every time.',
            'Crispy Chris' => 'Sharp and precise, Chris makes sure every cut is crisp and clean.',
            'Haircut Harry' => 'Harry knows all the latest trends and ensures your haircut always looks fresh.',
            'Bouncy Bella' => 'Full of energy and style, Bella specializes in lively, bouncy haircuts.',
            'Loud Lucy' => 'Always upbeat and chatty, Lucy makes your haircut a fun and memorable experience.'
        ];
        
        foreach ($employeesName as $name) {
            $user = User::create([
                'name' => $name,
                'email' => $faker->unique()->safeEmail,
                'password' => Hash::make('password'),
            ]);

            $employee = Employee::create([
                'user_id' => $user->id,
                'bio' => $employeesBio[$name],
                'photo_url' => $faker->imageUrl(200, 200, 'people'),
            ]);

            for ($day = 1; $day <= 5; $day++) {
                WorkingHour::create([
                    'employee_id' => $employee->id,
                    'weekday' => $day,
                    'start_time' => '12:00:00',
                    'end_time' => '20:00:00',
                ]);
            }
            foreach ($services as $service) {
                $employee->services()->attach($service->id, [
                    'price' => $faker->numberBetween(50, 100),
                ]);
            }
        }

        for ($i = 0; $i < 10; $i++) {
            User::create([
                'name' => $faker->name,
                'email' => $faker->unique()->safeEmail,
                'password' => Hash::make('password'),
            ]);
        }
    }
}
