<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\User;
use Faker\Factory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Factory::create('en_US');
        $faker->seed(20260318);

        for ($index = 1; $index <= 10; $index++) {
            $definition = [
                'name' => $faker->name(),
                'email' => $faker->unique()->safeEmail(),
                'phone' => $faker->unique()->phoneNumber(),
            ];

            if ($index % 2 === 0) {
                $user = User::create([
                    'email' => $definition['email'],
                    'password' => Hash::make('password'),
                    'role' => 'customer',
                    'email_verified_at' => now(),
                ]);
            } else {
                $user = null;
            }

            Customer::create([
                'email' => $definition['email'],
                'user_id' => $user?->id,
                'name' => $definition['name'],
                'phone' => $definition['phone'],
            ]);
        }
    }
}
