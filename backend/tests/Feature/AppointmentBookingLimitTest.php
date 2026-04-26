<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\AppointmentService;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\EmployeeBookingRule;
use App\Models\EmployeeBookingRuleConfiguration;
use App\Models\EmployeeScheduleConfiguration;
use App\Models\EmployeeService;
use App\Models\EmployeeServiceConfiguration;
use App\Models\EmployeeVersion;
use App\Models\EmployeeWorkingHour;
use App\Models\Service;
use App\Models\ServiceVersion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Tests\TestCase;

class AppointmentBookingLimitTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::create(2026, 3, 10, 9, 0, 0, config('app.timezone')));
        Mail::fake();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_authenticated_user_can_book_multiple_active_appointments_in_same_calendar_week(): void
    {
        $customer = $this->createCustomer();
        [$employee, $service] = $this->createBookableEmployeeAndService();

        $this->createAppointment($customer, $employee, $service, 'confirmed', '2026-03-16 09:00:00');

        $this->withApiToken($customer->user);

        $this->postJson('/api/appointments', [
            'service_id' => $service->id,
            'employee_id' => $employee->id,
            'appointment_start' => '2026-03-18 11:00',
        ])->assertCreated();
    }

    public function test_guest_can_book_multiple_active_appointments_in_same_calendar_week_using_same_email(): void
    {
        [$employee, $service] = $this->createBookableEmployeeAndService();
        $guest = Customer::create([
            'name' => 'Guest One',
            'email' => 'guest@example.test',
        ]);

        $this->createAppointment($guest, $employee, $service, 'pending', '2026-03-17 09:00:00');

        $this->postJson('/api/appointments', [
            'service_id' => $service->id,
            'employee_id' => $employee->id,
            'appointment_start' => '2026-03-19 12:00',
            'guest_name' => 'Guest One',
            'guest_email' => 'guest@example.test',
        ])->assertCreated();
    }

    public function test_cancelled_appointments_do_not_count_toward_weekly_limit(): void
    {
        $customer = $this->createCustomer();
        [$employee, $service] = $this->createBookableEmployeeAndService();

        $this->createAppointment($customer, $employee, $service, 'cancelled', '2026-03-16 13:00:00');

        $this->withApiToken($customer->user);

        $this->postJson('/api/appointments', [
            'service_id' => $service->id,
            'employee_id' => $employee->id,
            'appointment_start' => '2026-03-18 15:00',
        ])->assertCreated();
    }

    public function test_completed_appointments_do_not_count_toward_weekly_limit(): void
    {
        $customer = $this->createCustomer();
        [$employee, $service] = $this->createBookableEmployeeAndService();

        $this->createAppointment($customer, $employee, $service, 'completed', '2026-03-17 13:00:00');

        $this->withApiToken($customer->user);

        $this->postJson('/api/appointments', [
            'service_id' => $service->id,
            'employee_id' => $employee->id,
            'appointment_start' => '2026-03-20 15:00',
        ])->assertCreated();
    }

    public function test_customer_can_book_again_in_a_different_calendar_week(): void
    {
        $customer = $this->createCustomer();
        [$employee, $service] = $this->createBookableEmployeeAndService();

        $this->createAppointment($customer, $employee, $service, 'pending', '2026-03-16 10:00:00');

        $this->withApiToken($customer->user);

        $this->postJson('/api/appointments', [
            'service_id' => $service->id,
            'employee_id' => $employee->id,
            'appointment_start' => '2026-03-23 10:00',
        ])->assertCreated();
    }

    private function createBookableEmployeeAndService(): array
    {
        $service = Service::create([
            'name' => 'Haircut',
            'description' => 'Standard haircut',
        ]);

        ServiceVersion::create([
            'service_id' => $service->id,
            'is_available' => true,
            'valid_from' => now()->subMonth(),
        ]);

        $employeeUser = $this->createUser([
            'role' => 'employee',
            'email' => 'employee-' . Str::uuid() . '@example.test',
        ]);

        $employee = Employee::create([
            'user_id' => $employeeUser->id,
            'name' => 'Test Barber',
            'phone' => 'employee-' . Str::uuid(),
            'bio' => 'Barber bio',
        ]);

        EmployeeVersion::create([
            'employee_id' => $employee->id,
            'is_available' => true,
            'valid_from' => now()->subMonth(),
        ]);

        $serviceConfiguration = EmployeeServiceConfiguration::create([
            'employee_id' => $employee->id,
            'valid_from' => now()->subMonth(),
        ]);

        EmployeeService::create([
            'configuration_id' => $serviceConfiguration->id,
            'service_id' => $service->id,
            'price' => 25,
            'duration' => 30,
        ]);

        $scheduleConfiguration = EmployeeScheduleConfiguration::create([
            'employee_id' => $employee->id,
            'valid_from' => now()->subMonth(),
        ]);

        foreach (range(0, 6) as $weekday) {
            EmployeeWorkingHour::create([
                'schedule_configuration_id' => $scheduleConfiguration->id,
                'weekday' => $weekday,
                'start_time' => '08:00',
                'end_time' => '17:00',
            ]);
        }

        $ruleConfiguration = EmployeeBookingRuleConfiguration::create([
            'employee_id' => $employee->id,
            'valid_from' => now()->subMonth(),
        ]);

        EmployeeBookingRule::create([
            'booking_rule_configuration_id' => $ruleConfiguration->id,
            'booking_interval_minutes' => 30,
            'booking_window_days' => 60,
        ]);

        return [$employee, $service];
    }

    private function createUser(array $attributes = []): User
    {
        $defaults = [
            'name' => 'Test User',
            'email' => 'user-' . Str::uuid() . '@example.test',
            'password' => bcrypt('password'),
            'role' => 'customer',
            'email_verified_at' => now(),
        ];

        return User::create(array_merge($defaults, $attributes));
    }

    private function createCustomer(array $userOverrides = [], array $customerOverrides = []): Customer
    {
        $user = $this->createUser($userOverrides);

        return Customer::create(array_merge([
            'user_id' => $user->id,
            'name' => 'Test Customer',
            'email' => $user->email,
            'phone' => 'customer-' . Str::uuid(),
        ], $customerOverrides));
    }

    private function createAppointment(
        Customer $customer,
        Employee $employee,
        Service $service,
        string $status,
        string $startDatetime
    ): Appointment {
        $start = Carbon::parse($startDatetime);

        $appointment = Appointment::create([
            'customer_id' => $customer->id,
            'employee_id' => $employee->id,
            'total_duration' => 30,
            'total_price' => 25,
            'status' => $status,
            'start_datetime' => $start,
            'end_datetime' => $start->copy()->addMinutes(30),
        ]);

        AppointmentService::create([
            'appointment_id' => $appointment->id,
            'service_id' => $service->id,
            'duration' => 30,
            'price' => 25,
        ]);

        return $appointment;
    }
}
