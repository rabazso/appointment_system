<?php

namespace Database\Seeders;

use App\Models\ShopSpecialDay;
use Illuminate\Database\Seeder;

class ShopSpecialDaySeeder extends Seeder
{
    public function run(): void
    {
        ShopSpecialDay::create(
            [
                'date' => now()->addDays(14)->toDateString(),
                'name' => 'Spring Maintenance Day',
                'open_time' => null,
                'close_time' => null,
            ]
        );
    }
}
