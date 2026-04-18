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

        foreach (Service::all() as $service) {
            ServiceVersion::create([
                'service_id' => $service->id,
                'is_available' => true,
                'valid_from' => $validFrom,
                'valid_to' => null,
            ]);
        }
    }
}
