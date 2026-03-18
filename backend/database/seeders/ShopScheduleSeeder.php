<?php

namespace Database\Seeders;

use App\Models\ShopSchedule;
use Illuminate\Database\Seeder;

class ShopScheduleSeeder extends Seeder
{
    public function run(): void
    {
        $today = now()->toDateString();

        for ($weekday = 1; $weekday <= 5; $weekday++) {
            ShopSchedule::create(
                [
                    'weekday' => $weekday,
                    'valid_from' => $today,
                    'is_open' => true,
                    'open_time' => '10:00:00',
                    'close_time' => '20:00:00',
                    'valid_to' => null,
                ]
            );
        }

        foreach ([0, 6] as $weekday) {
            ShopSchedule::create(
                [
                    'weekday' => $weekday,
                    'valid_from' => $today,
                    'is_open' => false,
                    'open_time' => null,
                    'close_time' => null,
                    'valid_to' => null,
                ]
            );
        }
    }
}
