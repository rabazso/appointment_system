<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Employee;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Tests\TestCase;

class AppointmentBookingLimitTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::create(2026, 3, 10, 9, 0, 0, config('app.timezone')));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_authenticated_user_cannot_book_second_active_appointment_in_same_calendar_week(): void
    {
        $customer = $this->createUser();
        [$employee, $service] = $this->createBookableEmployeeAndService();

        $this->createAppointment([
            'customer_id' => $customer->id,
            'employee_id' => $employee->id,
            'service_id' => $service->id,
            'status' => 'confirmed',
            'start_datetime' => '2026-03-16 09:00:00',
            'end_datetime' => '2026-03-16 09:30:00',
        ]);

        $this->postJson('/api/appointments', [
            'service_id' => $service->id,
            'employee_id' => $employee->id,
            'appointment_start' => '2026-03-18 11:00',
            'customer_id' => $customer->id,
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['appointment_start'])
            ->assertJsonPath(
                'errors.appointment_start.0',
                'You can only keep 1 appointment per week. Please choose a date in another week.'
            );
    }

    public function test_guest_cannot_book_second_active_appointment_in_same_calendar_week_using_same_email(): void
    {
        [$employee, $service] = $this->createBookableEmployeeAndService();

        $this->createAppointment([
            'guest_name' => 'Guest One',
            'guest_email' => 'guest@example.test',
            'employee_id' => $employee->id,
            'service_id' => $service->id,
            'status' => 'pending',
            'start_datetime' => '2026-03-17 09:00:00',
            'end_datetime' => '2026-03-17 09:30:00',
        ]);

        $this->postJson('/api/appointments', [
            'service_id' => $service->id,
            'employee_id' => $employee->id,
            'appointment_start' => '2026-03-19 12:00',
            'guest_name' => 'Guest One',
            'guest_email' => 'guest@example.test',
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['appointment_start']);
    }

    public function test_cancelled_appointments_do_not_count_toward_weekly_limit(): void
    {
        $customer = $this->createUser();
        [$employee, $service] = $this->createBookableEmployeeAndService();

        $this->createAppointment([
            'customer_id' => $customer->id,
            'employee_id' => $employee->id,
            'service_id' => $service->id,
            'status' => 'cancelled',
            'start_datetime' => '2026-03-16 13:00:00',
            'end_datetime' => '2026-03-16 13:30:00',
        ]);

        $this->postJson('/api/appointments', [
            'service_id' => $service->id,
            'employee_id' => $employee->id,
            'appointment_start' => '2026-03-18 15:00',
            'customer_id' => $customer->id,
        ])->assertCreated();
    }

    public function test_completed_appointments_do_not_count_toward_weekly_limit(): void
    {
        $customer = $this->createUser();
        [$employee, $service] = $this->createBookableEmployeeAndService();

        $this->createAppointment([
            'customer_id' => $customer->id,
            'employee_id' => $employee->id,
            'service_id' => $service->id,
            'status' => 'completed',
            'start_datetime' => '2026-03-17 13:00:00',
            'end_datetime' => '2026-03-17 13:30:00',
        ]);

        $this->postJson('/api/appointments', [
            'service_id' => $service->id,
            'employee_id' => $employee->id,
            'appointment_start' => '2026-03-20 15:00',
            'customer_id' => $customer->id,
        ])->assertCreated();
    }

    public function test_customer_can_book_again_in_a_different_calendar_week(): void
    {
        $customer = $this->createUser();
        [$employee, $service] = $this->createBookableEmployeeAndService();

        $this->createAppointment([
            'customer_id' => $customer->id,
            'employee_id' => $employee->id,
            'service_id' => $service->id,
            'status' => 'pending',
            'start_datetime' => '2026-03-16 10:00:00',
            'end_datetime' => '2026-03-16 10:30:00',
        ]);

        $this->postJson('/api/appointments', [
            'service_id' => $service->id,
            'employee_id' => $employee->id,
            'appointment_start' => '2026-03-23 10:00',
            'customer_id' => $customer->id,
        ])->assertCreated();
    }

    private function createBookableEmployeeAndService(): array
    {
        $service = Service::create([
            'name' => 'Haircut',
            'description' => 'Standard haircut',
            'default_duration' => 30,
            'default_price' => 25,
            'active' => true,
        ]);

        $employeeUser = $this->createUser([
            'role' => 'employee',
            'email' => 'employee-' . Str::uuid() . '@example.test',
        ]);

        $employee = Employee::create([
            'user_id' => $employeeUser->id,
            'bio' => 'Barber bio',
        ]);

        $employee->services()->attach($service->id, [
            'price' => 25,
            'duration' => 30,
        ]);

        return [$employee, $service];
    }

    private function createUser(array $attributes = []): User
    {
        $defaults = [
            'name' => 'Test User',
            'email' => 'user-' . Str::uuid() . '@example.test',
            'password' => bcrypt('password'),
            'role' => 'user',
            'email_verified_at' => now(),
        ];

        return User::create(array_merge($defaults, $attributes));
    }

    private function createAppointment(array $attributes): Appointment
    {
        return Appointment::create(array_merge([
            'price' => 25,
        ], $attributes));
    }
}
