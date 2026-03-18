<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class EmployeeSeeder extends Seeder
{
    public function run(): void
    {
        $definitions = [
            [
                'name' => 'Blowout Ben',
                'email' => 'blowout.ben@barbershop.test',
                'phone' => '+3610000001',
                'bio' => 'My blowouts give your hair shine, volume, and a flawless finish every time.',
            ],
            [
                'name' => 'Crispy Chris',
                'email' => 'crispy.chris@barbershop.test',
                'phone' => '+3610000002',
                'bio' => 'Sharp and precise, Chris makes sure every cut is crispy and clean.',
            ],
            [
                'name' => 'Bouncy Bella',
                'email' => 'bouncy.bella@barbershop.test',
                'phone' => '+3610000003',
                'bio' => 'Full of energy and style, Bella specializes in lively, bouncy haircuts.',
            ],
            [
                'name' => 'Loud Lucy',
                'email' => 'loud.lucy@barbershop.test',
                'phone' => '+3610000004',
                'bio' => 'Always upbeat and chatty, Lucy makes every appointment fun and memorable.',
            ],
            [
                'name' => 'Haircut Harry',
                'email' => 'haircut.harry@barbershop.test',
                'phone' => '+3610000005',
                'bio' => 'Harry keeps up with every trend and delivers consistently fresh cuts.',
            ],
        ];

        foreach ($definitions as $definition) {
            $user = User::create(
                [
                    'email' => $definition['email'],
                    'password' => Hash::make('password'),
                    'role' => 'employee',
                    'email_verified_at' => now(),
                ]
            );

            Employee::create(
                [
                    'user_id' => $user->id,
                    'name' => $definition['name'],
                    'phone' => $definition['phone'],
                    'bio' => $definition['bio'],
                    'photo_path' => null,
                    'instagram_url' => null,
                ]
            );
        }
    }
}
