<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Employee;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class BarberCancelAppointmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_barber_can_cancel_own_appointment_with_valid_reason(): void
    {
        [$barberUser, $employee] = $this->createBarber();
        $customer = User::factory()->create();
        $service = $this->createService();
        $appointment = $this->createAppointment($customer->id, $employee->id, $service->id, 'confirmed');
        $reason = str_repeat('a', 30);

        Sanctum::actingAs($barberUser);

        $this->postJson("/api/barber/appointments/{$appointment->id}/cancel", [
            'cancellation_reason' => $reason,
        ])
            ->assertOk()
            ->assertJsonPath('appointment.status', 'cancelled');

        $appointment->refresh();
        $this->assertSame('cancelled', $appointment->status);
        $this->assertSame($reason, $appointment->cancellation_reason);
    }

    public function test_barber_cancel_requires_cancellation_reason_with_minimum_length(): void
    {
        [$barberUser, $employee] = $this->createBarber();
        $customer = User::factory()->create();
        $service = $this->createService();
        $appointment = $this->createAppointment($customer->id, $employee->id, $service->id, 'pending');

        Sanctum::actingAs($barberUser);

        $this->postJson("/api/barber/appointments/{$appointment->id}/cancel", [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['cancellation_reason']);

        $this->postJson("/api/barber/appointments/{$appointment->id}/cancel", [
            'cancellation_reason' => str_repeat('b', 29),
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['cancellation_reason']);

        $appointment->refresh();
        $this->assertSame('pending', $appointment->status);
        $this->assertNull($appointment->cancellation_reason);
    }

    public function test_barber_cannot_cancel_appointment_of_another_barber(): void
    {
        [$ownerUser, $ownerEmployee] = $this->createBarber();
        [$otherBarberUser] = $this->createBarber();
        $customer = User::factory()->create();
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
        $customer = User::factory()->create();
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
        $user = User::factory()->create(['role' => 'employee']);
        $employee = Employee::create([
            'user_id' => $user->id,
            'bio' => 'Barber bio',
        ]);

        return [$user, $employee];
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

    private function createAppointment(int $customerId, int $employeeId, int $serviceId, string $status): Appointment
    {
        $start = now()->addDay();

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
