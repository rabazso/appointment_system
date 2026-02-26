<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Employee;
use App\Models\EmployeeGallery;
use App\Models\Review;
use App\Models\Service;
use App\Models\WorkingHour;
use Carbon\Carbon;
use Faker\Factory as Faker;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create();
        $backendBase = rtrim((string) config('app.url'), '/');
        if ($backendBase !== '' && !preg_match('#^https?://#', $backendBase)) {
            $backendBase = 'http://' . $backendBase;
        }

        $services = [
            Service::create([
                'name' => 'Regular haircut',
                'default_duration' => 30,
                'default_price' => $faker->numberBetween(50, 100),
                'description' => 'A quick, precise haircut for a clean, polished look. Perfect for busy schedules.'
            ]),
            Service::create([
                'name' => 'Perfect haircut',
                'default_duration' => 60,
                'default_price' => $faker->numberBetween(50, 100),
                'description' => 'Basically the same as regular, but you give the barber more time, so he could be more precise.'
            ]),
            Service::create([
                'name' => 'Beard trim',
                'default_duration' => 30,
                'default_price' => $faker->numberBetween(50, 100),
                'description' => 'Expert trimming and shaping to maintain a sharp, well-groomed beard.'
            ]),
            Service::create([
                'name' => 'Fullbox',
                'default_duration' => 60,
                'default_price' => $faker->numberBetween(50, 100),
                'description' => 'A bundle option for a regular haircut and a beard trim.'
            ]),
        ];

        $employees = [
            [
                'name' => 'Blowout Ben',
                'email' => 'blowout.ben@barbershop.test',
                'bio' => 'My blowouts give your hair shine, volume, and a flawless finish every time.',
            ],
            [
                'name' => 'Crispy Chris',
                'email' => 'crispy.chris@barbershop.test',
                'bio' => 'Sharp and precise, Chris makes sure every cut is crisp and clean.',
            ],
            [
                'name' => 'Bouncy Bella',
                'email' => 'bouncy.bella@barbershop.test',
                'bio' => 'Full of energy and style, Bella specializes in lively, bouncy haircuts.',
            ],
            [
                'name' => 'Loud Lucy',
                'email' => 'loud.lucy@barbershop.test',
                'bio' => 'Always upbeat and chatty, Lucy makes your haircut a fun and memorable experience.',
            ],
            [
                'name' => 'Haircut Harry',
                'email' => 'haircut.harry@barbershop.test',
                'bio' => 'Harry knows all the latest trends and ensures your haircut always looks fresh.',
            ],
        ];

        foreach ($employees as $employeeData) {
            $user = User::create([
                'name' => $employeeData['name'],
                'email' => $employeeData['email'],
                'password' => Hash::make('password'),
                'role' => 'employee',
                "email_verified_at" => Carbon::now(),
            ]);

            $employee = Employee::create([
                'user_id' => $user->id,
                'bio' => $employeeData['bio'],
                'photo_url' => null,
            ]);
            $employee->forceFill([
                'photo_url' => $backendBase . '/storage/images/' . $employee->id . '/' . $employeeData['name'] . '.png',
            ])->save();
            EmployeeGallery::firstOrCreate([
                'employee_id' => $employee->id,
                'image_url' => $employee->photo_url,
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
                    "duration" => 30,
                ]);
            }
        }

        for ($i = 0; $i < 10; $i++) {
            User::create([
                'name' => $faker->name,
                'email' => $faker->unique()->safeEmail,
                'password' => Hash::make('password'),
                "email_verified_at" => Carbon::now(),
            ]);
        }

        $this->call([
            AppointmentSeeder::class,
            ReviewSeeder::class,
        ]);
    }
}
