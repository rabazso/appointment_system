<?php

namespace Database\Seeders;

use App\Models\ShopSetting;
use Illuminate\Database\Seeder;

class ShopSettingSeeder extends Seeder
{
    public function run(): void
    {
        ShopSetting::create(
            [
                'max_advance_booking_days' => 30,
                'cancellation_deadline_hours' => 24,
                'slot_interval_minutes' => 15,
                'sync_opening_hours_with_employee_schedule' => false,
            ]
        );
    }
}
