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
            ServiceVersion::create(
                [
                    'service_id' => $service->id,
                    'valid_from' => $validFrom,
                    'is_active' => true,
                    'valid_to' => null,
                ]
            );
        }
    }
}
