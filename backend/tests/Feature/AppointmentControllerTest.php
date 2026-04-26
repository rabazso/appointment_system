<?php

namespace Tests\Feature;

use App\Mail\BookingSummary;
use App\Mail\ReviewRequest;
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
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Tests\TestCase;

class AppointmentControllerTest extends TestCase
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

    public function test_booking_routes_return_services_employees_days_and_slots(): void
    {
        [$employee, $service] = $this->createBookableEmployeeAndService();
        $secondService = $this->createService();
        ServiceVersion::create([
            'service_id' => $secondService->id,
            'is_available' => true,
            'valid_from' => now()->subMonth(),
        ]);
        $employee->serviceConfigurations->first()->services()->create([
            'service_id' => $secondService->id,
            'duration' => 20,
            'price' => 15,
        ]);

        $this->getJson('/api/booking/services')
            ->assertOk()
            ->assertJsonPath('data.0.id', $service->id);

        $this->getJson("/api/booking/employees?service_ids[]={$service->id}&service_ids[]={$secondService->id}")
            ->assertOk()
            ->assertJsonPath('data.0.employee.id', $employee->id)
            ->assertJsonMissingPath('data.0.employee.profile_image.original_url')
            ->assertJsonMissingPath('data.0.total_price')
            ->assertJsonMissingPath('data.0.services');

        $this->getJson(
            "/api/booking/summary?service_ids[]={$service->id}&service_ids[]={$secondService->id}&employee_id={$employee->id}&appointment_start=2026-03-18%2008:00"
        )
            ->assertOk()
            ->assertJsonPath('data.total_price', 40)
            ->assertJsonCount(2, 'data.services');

        $daysResponse = $this->getJson("/api/booking/days?service_ids[]={$service->id}&service_ids[]={$secondService->id}&employee_id={$employee->id}&month=2026-03")
            ->assertOk();

        $this->assertTrue(
            collect($daysResponse->json('data'))->contains(
                fn (array $day) => ($day['date'] ?? null) === '2026-03-18'
            )
        );

        $response = $this->getJson("/api/booking/slots?service_ids[]={$service->id}&service_ids[]={$secondService->id}&employee_id={$employee->id}&selected_date=2026-03-18")
            ->assertOk();

        $this->assertContains('08:00', $response->json('data.0.slots'));
    }

    public function test_customer_only_sees_and_cancels_own_appointments(): void
    {
        $customer = $this->createCustomer();
        $otherCustomer = $this->createCustomer();
        $employee = $this->createEmployee();
        $service = $this->createService();

        $ownAppointment = $this->createAppointment($customer, $employee, $service, 'confirmed', '2026-03-18 10:00:00');
        $otherAppointment = $this->createAppointment($otherCustomer, $employee, $service, 'confirmed', '2026-03-18 11:00:00');

        $this->withApiToken($customer->user);

        $this->getJson('/api/user/appointments')
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.id', $ownAppointment->id);

        $this->postJson("/api/user/appointments/{$otherAppointment->id}/cancel")
            ->assertForbidden();

        $this->postJson("/api/user/appointments/{$ownAppointment->id}/cancel")
            ->assertOk()
            ->assertJsonPath('appointment.status', 'cancelled')
            ->assertJsonPath('appointment.cancelled_by', 'customer');

        $this->assertSame('cancelled', $ownAppointment->fresh()->status);
        $this->assertSame('customer', $ownAppointment->fresh()->cancelled_by);
    }

    public function test_logged_in_customer_can_book_with_bearer_token_without_guest_details(): void
    {
        $customer = $this->createCustomer();
        [$employee, $service] = $this->createBookableEmployeeAndService();
        $token = $this->apiTokenFor($customer->user);

        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/appointments', [
                'service_ids' => [$service->id],
                'employee_id' => $employee->id,
                'appointment_start' => '2026-03-19 10:00',
            ])
            ->assertCreated()
            ->assertJsonPath('appointment.customer_id', $customer->id);

        $this->assertDatabaseHas('appointments', [
            'customer_id' => $customer->id,
            'employee_id' => $employee->id,
            'status' => 'pending',
        ]);
    }

    public function test_invalid_bearer_token_cannot_book_as_guest_by_accident(): void
    {
        [$employee, $service] = $this->createBookableEmployeeAndService();

        $this->withHeader('Authorization', 'Bearer wrong-token')
            ->postJson('/api/appointments', [
                'service_ids' => [$service->id],
                'employee_id' => $employee->id,
                'appointment_start' => '2026-03-19 10:00',
            ])
            ->assertUnauthorized()
            ->assertJsonPath('message', 'Unauthenticated.');
    }

    public function test_expired_bearer_token_cannot_book_as_guest_by_accident(): void
    {
        $customer = $this->createCustomer();
        [$employee, $service] = $this->createBookableEmployeeAndService();
        $token = $this->apiTokenFor($customer->user, now()->subMinute());

        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/appointments', [
                'service_ids' => [$service->id],
                'employee_id' => $employee->id,
                'appointment_start' => '2026-03-19 10:00',
            ])
            ->assertUnauthorized()
            ->assertJsonPath('message', 'Unauthenticated.');
    }

    public function test_confirmation_changes_pending_appointment_to_confirmed_once(): void
    {
        config(['app.frontend_url' => 'frontend.test']);

        $customer = $this->createCustomer();
        $employee = $this->createEmployee();
        $service = $this->createService();
        $appointment = $this->createAppointment($customer, $employee, $service, 'pending', '2026-03-18 10:00:00');

        $url = URL::temporarySignedRoute(
            'appointments.confirm',
            now()->addHour(),
            ['appointment' => $appointment->id],
            false
        );

        $this->get($url)
            ->assertRedirect();

        $this->assertSame('confirmed', $appointment->fresh()->status);
        Mail::assertSent(BookingSummary::class);

        $this->getJson($url)
            ->assertStatus(410)
            ->assertJsonPath('message', 'Booking already confirmed');
    }

    public function test_employee_can_complete_own_confirmed_appointment(): void
    {
        $customer = $this->createCustomer();
        $employee = $this->createEmployee();
        $service = $this->createService();
        $appointment = $this->createAppointment($customer, $employee, $service, 'confirmed', '2026-03-18 10:00:00');

        $this->withApiToken($employee->user);

        $this->postJson("/api/employee/appointments/{$appointment->id}/complete")
            ->assertOk()
            ->assertJsonPath('appointment.status', 'completed');

        $this->assertSame('completed', $appointment->fresh()->status);
        Mail::assertSent(ReviewRequest::class);
    }

    private function createBookableEmployeeAndService(): array
    {
        $employee = $this->createEmployee();
        $service = $this->createService();

        ServiceVersion::create([
            'service_id' => $service->id,
            'is_available' => true,
            'valid_from' => now()->subMonth(),
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
            'duration' => 30,
            'price' => 25,
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

    private function createCustomer(): Customer
    {
        $user = $this->createUser(['role' => 'customer']);

        return Customer::create([
            'user_id' => $user->id,
            'name' => 'Customer ' . Str::upper(Str::random(6)),
            'email' => $user->email,
            'phone' => 'customer-' . Str::uuid(),
        ]);
    }

    private function createEmployee(): Employee
    {
        $user = $this->createUser(['role' => 'employee']);

        return Employee::create([
            'user_id' => $user->id,
            'name' => 'Employee ' . Str::upper(Str::random(6)),
            'phone' => 'employee-' . Str::uuid(),
            'bio' => 'Employee bio',
        ]);
    }

    private function createService(): Service
    {
        return Service::create([
            'name' => 'Haircut ' . Str::upper(Str::random(4)),
            'description' => 'Simple haircut.',
        ]);
    }

    private function createUser(array $attributes = []): User
    {
        return User::create(array_merge([
            'email' => 'user-' . Str::uuid() . '@example.test',
            'password' => bcrypt('password'),
            'role' => 'customer',
            'email_verified_at' => now(),
        ], $attributes));
    }
}
