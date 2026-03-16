<?php

namespace Database\Seeders;

use App\Models\Service;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();

        $services = [
            [
                'name' => 'Regular haircut',
                'default_duration' => 30,
                'description' => 'A quick, precise haircut for a clean, polished look. Perfect for busy schedules.',
            ],
            [
                'name' => 'Perfect haircut',
                'default_duration' => 60,
                'description' => 'Basically the same as regular, but you give the barber more time, so he could be more precise.',
            ],
            [
                'name' => 'Beard trim',
                'default_duration' => 30,
                'description' => 'Expert trimming and shaping to maintain a sharp, well-groomed beard.',
            ],
            [
                'name' => 'Fullbox',
                'default_duration' => 60,
                'description' => 'A bundle option for a regular haircut and a beard trim.',
            ],
        ];

        foreach ($services as $serviceData) {
            Service::create([
                ...$serviceData,
                'default_price' => $faker->numberBetween(50, 100),
            ]);
        }
    }
}
