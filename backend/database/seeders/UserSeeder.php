<?php

namespace Database\Seeders;

use App\Models\User;
use Carbon\Carbon;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();

        for ($i = 0; $i < 2; $i++) {
            User::create([
                'email' => $faker->unique()->safeEmail,
                'password' => Hash::make('password'),
                'role' => 'customer',
                'email_verified_at' => Carbon::now(),
            ]);
        }
        User::create([
                'email' => 'admin@barbershop.com',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'email_verified_at' => Carbon::now(),
            ]);
    }
}
