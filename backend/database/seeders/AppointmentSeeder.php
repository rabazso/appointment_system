<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\Customer;
use App\Models\EmployeeService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class AppointmentSeeder extends Seeder
{
    public function run(): void
    {
        $customers = Customer::query()->get();
        $employeeServices = EmployeeService::query()
            ->with(['employee', 'service', 'versions'])
            ->get();

        $statusOptions = ['pending', 'confirmed', 'completed', 'cancelled', 'no_show'];
        $customerIndex = 0;

        foreach ($employeeServices->groupBy('employee_id') as $employeeId => $services) {
            foreach ($statusOptions as $statusIndex => $status) {
                $customer = $customers[$customerIndex % $customers->count()];
                $customerIndex++;

                $employeeService = $services->random();
                $version = $employeeService->versions->first();


                $start = Carbon::now()
                    ->addDays(($employeeId * 7) + $statusIndex + 1)
                    ->setTime(rand(10, 18), 0);

                Appointment::create([
                    'customer_id' => $customer->id,
                    'employee_id' => $employeeService->employee_id,
                    'service_id' => $employeeService->service_id,
                    'start_datetime' => $start,
                    'end_datetime' => $start->copy()->addMinutes($version->duration),
                    'duration' => $version->duration,
                    'price' => $version->price,
                    'status' => $status,
                    'customer_note' => 'A note from the customer.',
                ]);
            }
        }
    }
}
