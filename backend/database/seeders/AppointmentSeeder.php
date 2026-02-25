<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\Employee;
use App\Models\Service;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AppointmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $customers = User::pluck('id');
        $employees = Employee::pluck('id');
        $services = Service::pluck('id');

        if ($customers->isEmpty() || $employees->isEmpty() || $services->isEmpty()) {
            return;
        }

        for ($i = 0; $i < 5; $i++) {

            $start = Carbon::now()
                ->addDays(rand(1, 14))
                ->setTime(rand(9, 17), 0);

            $end = (clone $start)->addMinutes(30);

            $statusOptions = ['pending', 'confirmed', 'completed', 'cancelled', 'no_show'];
            $status = $statusOptions[array_rand($statusOptions)];

            Appointment::create([
                'customer_id' => $customers->random(),
                'employee_id' => $employees->random(),
                'service_id' => $services->random(),
                'price' => rand(50, 100),
                'status' => $status,
                'start_datetime' => $start,
                'end_datetime' => $end,
                'confirmed_at' => $status === 'confirmed' || $status === 'completed'
                    ? Carbon::now()
                    : null,
            ]);
        }
    }
}
