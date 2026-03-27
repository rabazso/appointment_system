<?php

namespace Database\Seeders;

use App\Models\ShopOpeningHour;
use Illuminate\Database\Seeder;

class ShopOpeningHourSeeder extends Seeder
{
    public function run(): void
    {
        $today = now()->toDateString();

        for ($weekday = 1; $weekday <= 5; $weekday++) {
            ShopOpeningHour::create(
                [
                    'weekday' => $weekday,
                    'open_time' => '10:00:00',
                    'close_time' => '20:00:00',
                ]
            );
        }

        foreach ([0, 6] as $weekday) {
            ShopOpeningHour::create(
                [
                    'weekday' => $weekday,
                    'close_time' => null,
                ]
            );
        }
    }
}
