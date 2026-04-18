<?php

namespace Database\Seeders;

use App\Models\ShopSetting;
use App\Models\ShopSettingVersion;
use Illuminate\Database\Seeder;

class ShopSettingSeeder extends Seeder
{
    public function run(): void
    {
        $shopSetting = ShopSetting::create([
            'logo_path' => null,
            'about_us_text' => 'A welcoming neighborhood barbershop focused on precise cuts and friendly service.',
            'cancellation_deadline_hours' => 24,
            ]);
    }
}
