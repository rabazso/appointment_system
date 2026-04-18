<?php

namespace Database\Seeders;

use App\Models\EmployeeBookingRule;
use App\Models\EmployeeBookingRuleConfiguration;
use Illuminate\Database\Seeder;

class EmployeeBookingRuleSeeder extends Seeder
{
    public function run(): void
    {
        foreach (EmployeeBookingRuleConfiguration::all() as $configuration) {
            EmployeeBookingRule::create([
                'booking_rule_configuration_id' => $configuration->id,
                'booking_interval_minutes' => 30,
                'booking_window_days' => 30,
            ]);
        }
    }
}
