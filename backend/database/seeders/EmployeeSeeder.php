<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\EmployeeGallery;
use App\Models\Service;
use App\Models\User;
use App\Models\WorkingHour;
use Carbon\Carbon;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();

        $services = Service::all();

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
                'email_verified_at' => Carbon::now(),
            ]);

            $employee = Employee::create([
                'user_id' => $user->id,
                'bio' => $employeeData['bio'],
                'photo_url' => null,
            ]);

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
                    'duration' => 30,
                ]);
            }
        }
    }
}
