<?php

namespace Database\Seeders;

use App\Models\ShopSetting;
use App\Models\ShopSettingsVersion;
use Illuminate\Database\Seeder;

class ShopSettingSeeder extends Seeder
{
    public function run(): void
    {
        $shopSetting = ShopSetting::create([
            'logo_path' => null,
            'about_us_text' => 'A welcoming neighborhood barbershop focused on precise cuts and friendly service.',
        ]);

        ShopSettingsVersion::create([
            'shop_setting_id' => $shopSetting->id,
            'default_booking_interval_minutes' => 15,
            'default_booking_window_days' => 30,
            'cancellation_deadline_hours' => 24,
            'sync_opening_hours_with_employee_schedule' => false,
            'valid_from' => now()->startOfDay(),
            'valid_to' => null,
        ]);
    }
}
