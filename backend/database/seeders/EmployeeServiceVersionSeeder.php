<?php

namespace Database\Seeders;

use App\Models\EmployeeService;
use App\Models\EmployeeServiceVersion;
use Illuminate\Database\Seeder;

class EmployeeServiceVersionSeeder extends Seeder
{
    public function run(): void
    {
        $validFrom = now()->startOfDay();

        $definitions = [
            'Blowout Ben' => [
                'Short haircut' => ['duration' => 30, 'price' => 6000],
                'Normal haircut' => ['duration' => 45, 'price' => 7500],
                'Long haircut' => ['duration' => 60, 'price' => 9000],
                'Beard trim' => ['duration' => 30, 'price' => 6000],
                'Fullbox' => ['duration' => 75, 'price' => 12000],
            ],
            'Crispy Chris' => [
                'Short haircut' => ['duration' => 30, 'price' => 6000],
                'Normal haircut' => ['duration' => 45, 'price' => 7500],
                'Long haircut' => ['duration' => 60, 'price' => 9000],
                'Beard trim' => ['duration' => 30, 'price' => 6000],
                'Fullbox' => ['duration' => 75, 'price' => 12000],
            ],
            'Bouncy Bella' => [
                'Short haircut' => ['duration' => 30, 'price' => 5500],
                'Normal haircut' => ['duration' => 45, 'price' => 7000],
                'Long haircut' => ['duration' => 60, 'price' => 8000],
                'Brothers haircut' => ['duration' => 60, 'price' => 9000],
                'Father and son haircut' => ['duration' => 65, 'price' => 9000],
            ],
            'Loud Lucy' => [
                'Short haircut' => ['duration' => 30, 'price' => 5500],
                'Normal haircut' => ['duration' => 45, 'price' => 7000],
                'Long haircut' => ['duration' => 60, 'price' => 8000],
                'Brothers haircut' => ['duration' => 60, 'price' => 10000],
                'Father and son haircut' => ['duration' => 60, 'price' => 10000],
            ],
            'Haircut Harry' => [
                'Short haircut' => ['duration' => 30, 'price' => 6000],
                'Normal haircut' => ['duration' => 45, 'price' => 7500],
                'Long haircut' => ['duration' => 60, 'price' => 9000],
                'Beard trim' => ['duration' => 30, 'price' => 6000],
                'Fullbox' => ['duration' => 75, 'price' => 12000],
                'Brothers haircut' => ['duration' => 60, 'price' => 10000],
                'Father and son haircut' => ['duration' => 60, 'price' => 10000],
            ],
        ];

        foreach ($definitions as $employeeName => $services) {
            foreach ($services as $serviceName => $versionData) {
                $employeeService = EmployeeService::whereHas('employee', fn ($query) => $query->where('name', $employeeName))
                    ->whereHas('service', fn ($query) => $query->where('name', $serviceName))
                    ->first();

                EmployeeServiceVersion::create([
                    'employee_service_id' => $employeeService->id,
                    'valid_from' => $validFrom,
                    'duration' => $versionData['duration'],
                    'price' => $versionData['price'],
                    'valid_to' => null,
                    'is_active' => true,
                ]);
            }
        }
    }
}
