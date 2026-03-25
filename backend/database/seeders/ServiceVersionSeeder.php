<?php

namespace Database\Seeders;

use App\Models\Service;
use App\Models\ServiceVersion;
use Illuminate\Database\Seeder;

class ServiceVersionSeeder extends Seeder
{
    public function run(): void
    {
        $validFrom = now()->startOfDay();

        $definitions = [
            'Short haircut' => ['default_duration' => 30, 'default_price' => 6000],
            'Normal haircut' => ['default_duration' => 45, 'default_price' => 7500],
            'Long haircut' => ['default_duration' => 60, 'default_price' => 9000],
            'Beard trim' => ['default_duration' => 30, 'default_price' => 6000],
            'Fullbox' => ['default_duration' => 75, 'default_price' => 12000],
            'Father and son haircut' => ['default_duration' => 65, 'default_price' => 9000],
            'Brothers haircut' => ['default_duration' => 60, 'default_price' => 9000],
        ];

        foreach (Service::all() as $service) {
            $defaults = $definitions[$service->name];

            ServiceVersion::create([
                'service_id' => $service->id,
                'default_duration' => $defaults['default_duration'],
                'default_price' => $defaults['default_price'],
                'valid_from' => $validFrom,
                'valid_to' => null,
            ]);
        }
    }
}
