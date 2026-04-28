<?php

namespace Database\Seeders;

use App\Models\ShopInformation;
use Illuminate\Database\Seeder;

class ShopInformationSeeder extends Seeder
{
    public function run(): void
    {
        ShopInformation::query()->firstOrCreate([], [
            'email' => 'hello@barbershop.test',
            'phone' => '+36 30 123 4567',
            'address' => 'Budapest, Example Street 12.',
            'links' => [
                [
                    'label' => 'Instagram',
                    'url' => 'https://instagram.com/examplebarbershop',
                ],
                [
                    'label' => 'Facebook',
                    'url' => 'https://facebook.com/examplebarbershop',
                ],
                [
                    'label' => 'TikTok',
                    'url' => 'https://tiktok.com/@examplebarbershop',
                ],
            ],
        ]);
    }
}
