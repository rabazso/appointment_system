<?php

namespace Tests\Feature;

use App\Mail\AppointmentCancelled;
use App\Models\Appointment;
use App\Models\Employee;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class BarberCancelAppointmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_barber_can_cancel_own_appointment_with_valid_reason(): void
    {
        [$barberUser, $employee] = $this->createBarber();
        $customer = $this->createUser();
        $service = $this->createService();
        $appointment = $this->createAppointment($customer->id, $employee->id, $service->id, 'confirmed');
        $reason = str_repeat('a', 10);

        Mail::fake();
        Sanctum::actingAs($barberUser);

        $this->postJson("/api/barber/appointments/{$appointment->id}/cancel", [
            'cancellation_reason' => $reason,
        ])
            ->assertOk()
            ->assertJsonPath('appointment.status', 'cancelled');

        $appointment->refresh();
        $this->assertSame('cancelled', $appointment->status);
        $this->assertSame($reason, $appointment->cancellation_reason);

        Mail::assertSent(AppointmentCancelled::class, function (AppointmentCancelled $mail) use ($customer, $appointment, $reason) {
            return $mail->hasTo($customer->email)
                && $mail->appointment->is($appointment)
                && $mail->appointment->cancellation_reason === $reason;
        });
    }

    public function test_barber_cancel_requires_cancellation_reason_with_minimum_length_for_upcoming_appointments(): void
    {
        [$barberUser, $employee] = $this->createBarber();
        $customer = $this->createUser();
        $service = $this->createService();
        $appointment = $this->createAppointment($customer->id, $employee->id, $service->id, 'pending');

        Sanctum::actingAs($barberUser);

        $this->postJson("/api/barber/appointments/{$appointment->id}/cancel", [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['cancellation_reason']);

        $this->postJson("/api/barber/appointments/{$appointment->id}/cancel", [
            'cancellation_reason' => str_repeat('b', 9),
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['cancellation_reason']);

        $appointment->refresh();
        $this->assertSame('pending', $appointment->status);
        $this->assertNull($appointment->cancellation_reason);
    }

    public function test_barber_can_cancel_past_appointment_without_reason_for_no_show(): void
    {
        [$barberUser, $employee] = $this->createBarber();
        $customer = $this->createUser();
        $service = $this->createService();
        $appointment = $this->createAppointment(
            $customer->id,
            $employee->id,
            $service->id,
            'confirmed',
            now()->subHour()
        );

        Mail::fake();
        Sanctum::actingAs($barberUser);

        $this->postJson("/api/barber/appointments/{$appointment->id}/cancel", [])
            ->assertOk()
            ->assertJsonPath('appointment.status', 'cancelled');

        $appointment->refresh();
        $this->assertSame('cancelled', $appointment->status);
        $this->assertNull($appointment->cancellation_reason);

        Mail::assertSent(AppointmentCancelled::class, function (AppointmentCancelled $mail) use ($customer, $appointment) {
            return $mail->hasTo($customer->email)
                && $mail->appointment->is($appointment)
                && $mail->appointment->cancellation_reason === null;
        });
    }

    public function test_barber_cannot_cancel_appointment_of_another_barber(): void
    {
        [$ownerUser, $ownerEmployee] = $this->createBarber();
        [$otherBarberUser] = $this->createBarber();
        $customer = $this->createUser();
        $service = $this->createService();
        $appointment = $this->createAppointment($customer->id, $ownerEmployee->id, $service->id, 'confirmed');

        Sanctum::actingAs($otherBarberUser);

        $this->postJson("/api/barber/appointments/{$appointment->id}/cancel", [
            'cancellation_reason' => str_repeat('c', 35),
        ])->assertStatus(403);

        $appointment->refresh();
        $this->assertSame('confirmed', $appointment->status);
        $this->assertNull($appointment->cancellation_reason);
    }

    public function test_barber_cannot_cancel_already_cancelled_appointment(): void
    {
        [$barberUser, $employee] = $this->createBarber();
        $customer = $this->createUser();
        $service = $this->createService();
        $appointment = $this->createAppointment($customer->id, $employee->id, $service->id, 'cancelled');
        $appointment->update(['cancellation_reason' => str_repeat('z', 40)]);

        Sanctum::actingAs($barberUser);

        $this->postJson("/api/barber/appointments/{$appointment->id}/cancel", [
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
            'bio' => 'Barber bio',
        ]);

        return [$user, $employee];
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

    private function createService(): Service
    {
        return Service::create([
            'name' => 'Haircut',
            'description' => 'Standard haircut',
            'default_duration' => 30,
            'default_price' => 25,
            'active' => true,
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

        return Appointment::create([
            'customer_id' => $customerId,
            'employee_id' => $employeeId,
            'service_id' => $serviceId,
            'price' => 25,
            'status' => $status,
            'start_datetime' => $start,
            'end_datetime' => $start->copy()->addMinutes(30),
        ]);
    }
}
