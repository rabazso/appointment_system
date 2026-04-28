<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\AppointmentService;
use App\Models\Customer;
use App\Models\EmployeeServiceConfiguration;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class AppointmentSeeder extends Seeder
{
    private const APPOINTMENTS_PER_STATUS = 8;

    public function run(): void
    {
        $customers = Customer::query()->get();
        $configurations = EmployeeServiceConfiguration::query()
            ->with(['employee', 'services'])
            ->get();

        $statusOptions = ['pending', 'confirmed', 'completed', 'cancelled', 'no_show'];

        foreach ($configurations->groupBy('employee_id') as $employeeConfigurations) {
            foreach ($statusOptions as $status) {
                for ($i = 0; $i < self::APPOINTMENTS_PER_STATUS; $i++) {
                    $customer = $customers->random();

                    $start = $this->appointmentStartForStatus($status);

                    $configuration = $this->resolveConfigurationForDate($employeeConfigurations, $start);
                    if ($configuration === null) {
                        continue;
                    }

                    $item = $configuration->services->random();

                    $duration = $item->duration ?? 30;
                    $price = $item->price ?? 5000;

                    $appointment = Appointment::create([
                        'customer_id' => $customer->id,
                        'employee_id' => $configuration->employee_id,
                        'start_datetime' => $start,
                        'end_datetime' => $start->copy()->addMinutes($duration),
                        'total_duration' => $duration,
                        'total_price' => $price,
                        'status' => $status,
                        'cancellation_reason' => $status === 'cancelled' ? 'Customer schedule changed.' : null,
                        'cancelled_by' => $status === 'cancelled' ? $this->randomCancellationActor() : null,
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
    }

    private function appointmentStartForStatus(string $status): Carbon
    {
        $date = in_array($status, ['completed', 'cancelled', 'no_show'], true)
            ? now()->subDays(random_int(1, 28))
            : now()->addDays(random_int(1, 28));

        return $date->setTime(random_int(9, 20), random_int(0, 1) * 30);
    }

    private function randomCancellationActor(): string
    {
        $actors = ['customer', 'employee', 'admin'];

        return $actors[array_rand($actors)];
    }

    private function resolveConfigurationForDate($employeeConfigurations, Carbon $date): ?EmployeeServiceConfiguration
    {
        $sortedConfigurations = $employeeConfigurations->sortByDesc('valid_from');

        return $sortedConfigurations
            ->filter(function (EmployeeServiceConfiguration $configuration) use ($date) {
                return $configuration->valid_from <= $date
                    && ($configuration->valid_to === null || $configuration->valid_to > $date);
            })
            ->first() ?? $sortedConfigurations->first();
    }

}
