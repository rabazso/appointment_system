<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\AppointmentService;
use App\Models\Customer;
use App\Models\EmployeeServiceConfiguration;
use App\Models\ServiceVersion;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class AppointmentSeeder extends Seeder
{
    public function run(): void
    {
        $customers = Customer::query()->get();
        $configurations = EmployeeServiceConfiguration::query()
            ->with(['employee', 'services'])
            ->get();

        $statusOptions = ['pending', 'confirmed', 'completed', 'cancelled', 'no_show'];

        foreach ($configurations->groupBy('employee_id') as $employeeConfigurations) {
            foreach ($statusOptions as $status) {
                $customer = $customers->random();

                $start = now()
                    ->addDays(random_int(1, 14))
                    ->setTime(random_int(9, 20), random_int(0, 1) * 30);

                $configuration = $this->resolveConfigurationForDate($employeeConfigurations, $start);


                $item = $configuration->services->random();
                $serviceVersion = $this->resolveServiceVersionForDate($item->service->versions, $start);

                $duration = $item->uses_default_values ? $serviceVersion->default_duration : $item->duration;
                $price = $item->uses_default_values ? $serviceVersion->default_price : $item->price;

                $appointment = Appointment::create([
                    'customer_id' => $customer->id,
                    'employee_id' => $configuration->employee_id,
                    'start_datetime' => $start,
                    'end_datetime' => $start->copy()->addMinutes($duration),
                    'total_duration' => $duration,
                    'total_price' => $price,
                    'status' => $status,
                    'customer_note' => 'A note from the customer.',
                ]);

                AppointmentService::create([
                    'appointment_id' => $appointment->id,
                    'service_id' => $item->service_id,
                    'duration' => $duration,
                    'price' => $price,
                ]);
            }
        }
    }

    private function resolveConfigurationForDate($employeeConfigurations, Carbon $date): ?EmployeeServiceConfiguration
    {
        return $employeeConfigurations
            ->filter(function (EmployeeServiceConfiguration $configuration) use ($date) {
                return $configuration->valid_from <= $date
                    && ($configuration->valid_to === null || $configuration->valid_to > $date);
            })
            ->sortByDesc('valid_from')
            ->first();
    }

    private function resolveServiceVersionForDate($serviceVersions, Carbon $date): ?ServiceVersion
    {
        return $serviceVersions
            ->filter(function (ServiceVersion $version) use ($date) {
                return $version->valid_from <= $date
                    && ($version->valid_to === null || $version->valid_to > $date);
            })
            ->sortByDesc('valid_from')
            ->first();
    }
}
