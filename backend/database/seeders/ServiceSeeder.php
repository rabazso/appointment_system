<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        $definitions = [
            [
                'name' => 'Short haircut',
                'description' => 'A quick short haircut for a clean and easy-to-maintain look.',
            ],
            [
                'name' => 'Long haircut',
                'description' => 'A haircut tailored for longer hair with extra attention to shape and flow.',
            ],
            [
                'name' => 'Normal haircut',
                'description' => 'A balanced standard haircut for an everyday sharp and polished style.',
            ],
            [
                'name' => 'Beard trim',
                'description' => 'Expert trimming and shaping to maintain a sharp, well-groomed beard.',
            ],
            [
                'name' => 'Fullbox',
                'description' => 'A normal haircut and beard trim in one booking.',
            ],
            [
                'name' => 'Father and son haircut',
                'description' => 'A shared grooming session for father and son with matching fresh cuts.',
            ],
            [
                'name' => 'Brothers haircut',
                'description' => 'A haircut service tailored for brothers booking together.',
            ],
        ];

        foreach ($definitions as $definition) {
            Service::create([
                'name' => $definition['name'],
                'description' => $definition['description'],
            ]);
        }
    }
}
