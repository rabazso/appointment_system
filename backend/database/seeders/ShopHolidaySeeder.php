<?php

namespace Database\Seeders;

use App\Models\ShopHoliday;
use Illuminate\Database\Seeder;

class ShopHolidaySeeder extends Seeder
{
    public function run(): void
    {
        ShopHoliday::create(
            [
                'date' => now()->addDays(14)->toDateString(),
                'name' => 'Spring Maintenance Day',
                'is_open' => false,
                'open_time' => null,
                'close_time' => null,
            ]
        );
    }
}
