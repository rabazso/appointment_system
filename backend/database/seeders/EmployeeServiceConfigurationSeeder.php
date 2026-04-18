<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\EmployeeServiceConfiguration;
use App\Models\EmployeeService;
use App\Models\Service;
use Illuminate\Database\Seeder;

class EmployeeServiceConfigurationSeeder extends Seeder
{
    public function run(): void
    {
        $definitions = [
            'Blowout Ben' => ['Short haircut', 'Normal haircut', 'Long haircut', 'Beard trim', 'Fullbox'],
            'Crispy Chris' => ['Short haircut', 'Normal haircut', 'Long haircut', 'Beard trim', 'Fullbox'],
            'Bouncy Bella' => ['Short haircut', 'Normal haircut', 'Long haircut', 'Brothers haircut', 'Father and son haircut'],
            'Loud Lucy' => ['Short haircut', 'Normal haircut', 'Long haircut', 'Brothers haircut', 'Father and son haircut'],
            'Haircut Harry' => ['Short haircut', 'Normal haircut', 'Long haircut', 'Beard trim', 'Fullbox', 'Brothers haircut', 'Father and son haircut'],
        ];

        foreach ($definitions as $employeeName => $serviceNames) {
            $employee = Employee::where('name', $employeeName)->first();

            $configuration = EmployeeServiceConfiguration::create([
                'employee_id' => $employee->id,
                'valid_from' => now()->startOfDay(),
                'valid_to' => null,
            ]);

            foreach ($serviceNames as $serviceName) {
                $service = Service::where('name', $serviceName)->first();


                EmployeeService::create([
                    'configuration_id' => $configuration->id,
                    'service_id' => $service->id,
                    'duration' => 30,
                    'price' => 5000,
                ]);
            }
        }
    }
}
