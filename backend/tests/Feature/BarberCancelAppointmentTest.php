<?php

namespace Tests\Feature;

use App\Mail\AppointmentCancelled;
use App\Models\Appointment;
use App\Models\AppointmentService;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Tests\TestCase;

class BarberCancelAppointmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_barber_can_cancel_own_appointment_with_valid_reason(): void
    {
        [$barberUser, $employee] = $this->createBarber();
        $customer = $this->createCustomer();
        $service = $this->createService();
        $appointment = $this->createAppointment($customer->id, $employee->id, $service->id, 'confirmed');
        $reason = str_repeat('a', 10);

        Mail::fake();
        $this->withApiToken($barberUser);

        $this->postJson("/api/employee/appointments/{$appointment->id}/cancel", [
            'cancellation_reason' => $reason,
        ])
            ->assertOk()
            ->assertJsonPath('data.status', 'cancelled');

        $appointment->refresh();
        $this->assertSame('cancelled', $appointment->status);
        $this->assertSame($reason, $appointment->cancellation_reason);

        Mail::assertSent(AppointmentCancelled::class, function (AppointmentCancelled $mail) use ($customer, $appointment, $reason) {
            return $mail->hasTo($customer->email)
                && $mail->appointment->is($appointment)
                && $mail->appointment->cancellation_reason === $reason;
        });
    }

    public function test_barber_cancel_requires_cancellation_reason_for_upcoming_appointments(): void
    {
        [$barberUser, $employee] = $this->createBarber();
        $customer = $this->createCustomer();
        $service = $this->createService();
        $appointment = $this->createAppointment($customer->id, $employee->id, $service->id, 'pending');

        $this->withApiToken($barberUser);

        $this->postJson("/api/employee/appointments/{$appointment->id}/cancel", [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['cancellation_reason']);

        $this->postJson("/api/employee/appointments/{$appointment->id}/cancel", [
            'cancellation_reason' => str_repeat('b', 9),
        ])
            ->assertOk()
            ->assertJsonPath('data.status', 'cancelled');

        $appointment->refresh();
        $this->assertSame('cancelled', $appointment->status);
        $this->assertSame(str_repeat('b', 9), $appointment->cancellation_reason);
    }

    public function test_barber_cancel_past_appointment_still_requires_reason(): void
    {
        [$barberUser, $employee] = $this->createBarber();
        $customer = $this->createCustomer();
        $service = $this->createService();
        $appointment = $this->createAppointment(
            $customer->id,
            $employee->id,
            $service->id,
            'confirmed',
            now()->subHour()
        );

        Mail::fake();
        $this->withApiToken($barberUser);

        $this->postJson("/api/employee/appointments/{$appointment->id}/cancel", [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['cancellation_reason']);

        $appointment->refresh();
        $this->assertSame('confirmed', $appointment->status);
        $this->assertNull($appointment->cancellation_reason);
        Mail::assertNothingSent();
    }

    public function test_barber_cannot_cancel_appointment_of_another_barber(): void
    {
        [$ownerUser, $ownerEmployee] = $this->createBarber();
        [$otherBarberUser] = $this->createBarber();
        $customer = $this->createCustomer();
        $service = $this->createService();
        $appointment = $this->createAppointment($customer->id, $ownerEmployee->id, $service->id, 'confirmed');

        $this->withApiToken($otherBarberUser);

        $this->postJson("/api/employee/appointments/{$appointment->id}/cancel", [
            'cancellation_reason' => str_repeat('c', 35),
        ])->assertStatus(403);

        $appointment->refresh();
        $this->assertSame('confirmed', $appointment->status);
        $this->assertNull($appointment->cancellation_reason);
    }

    public function test_barber_cannot_cancel_already_cancelled_appointment(): void
    {
        [$barberUser, $employee] = $this->createBarber();
        $customer = $this->createCustomer();
        $service = $this->createService();
        $appointment = $this->createAppointment($customer->id, $employee->id, $service->id, 'cancelled');
        $appointment->update(['cancellation_reason' => str_repeat('z', 40)]);

        $this->withApiToken($barberUser);

        $this->postJson("/api/employee/appointments/{$appointment->id}/cancel", [
            'cancellation_reason' => str_repeat('d', 45),
        ])->assertStatus(409);

        $appointment->refresh();
        $this->assertSame('cancelled', $appointment->status);
        $this->assertSame(str_repeat('z', 40), $appointment->cancellation_reason);
    }

    private function createBarber(): array
    {
        $user = $this->createUser(['role' => 'employee']);
        $employee = Employee::create([
            'user_id' => $user->id,
            'name' => 'Test Barber',
            'phone' => 'employee-' . Str::uuid(),
            'bio' => 'Barber bio',
        ]);

        return [$user, $employee];
    }

    private function createUser(array $attributes = []): User
    {
        $defaults = [
            'email' => 'user-' . Str::uuid() . '@example.test',
            'password' => bcrypt('password'),
            'role' => 'customer',
            'email_verified_at' => now(),
        ];

        return User::create(array_merge($defaults, $attributes));
    }

    private function createCustomer(): Customer
    {
        $user = $this->createUser();

        return Customer::create([
            'user_id' => $user->id,
            'name' => 'Test Customer',
            'email' => $user->email,
            'phone' => 'customer-' . Str::uuid(),
        ]);
    }

    private function createService(): Service
    {
        return Service::create([
            'name' => 'Haircut',
            'description' => 'Standard haircut',
        ]);
    }

    private function createAppointment(
        int $customerId,
        int $employeeId,
        int $serviceId,
        string $status,
        ?Carbon $start = null
    ): Appointment
    {
        $start = $start ? $start->copy() : now()->addDay();

        $appointment = Appointment::create([
            'customer_id' => $customerId,
            'employee_id' => $employeeId,
            'total_duration' => 30,
            'total_price' => 25,
            'status' => $status,
            'start_datetime' => $start,
            'end_datetime' => $start->copy()->addMinutes(30),
        ]);

        AppointmentService::create([
            'appointment_id' => $appointment->id,
            'service_id' => $serviceId,
            'duration' => 30,
            'price' => 25,
        ]);

        return $appointment;
    }
}
