<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\AppointmentService;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\EmployeeImage;
use App\Models\Review;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class BarberAdminDataTest extends TestCase
{
    use RefreshDatabase;

    public function test_barber_appointments_endpoint_returns_service_and_client_names(): void
    {
        ['user' => $barberUser, 'employee' => $employee] = $this->createBarber();
        $customer = $this->createCustomer();
        $service = Service::create([
            'name' => 'Classic Fade',
            'description' => 'Clean fade and finish.',
        ]);
        $appointment = $this->createAppointment($customer, $employee, $service, [
            'status' => 'confirmed',
        ]);

        Sanctum::actingAs($barberUser);

        $this->getJson('/api/barber/appointments')
            ->assertOk()
            ->assertJsonPath('0.id', $appointment->id)
            ->assertJsonPath('0.client', $customer->name)
            ->assertJsonPath('0.service', $service->name)
            ->assertJsonPath('0.status', 'confirmed')
            ->assertJsonPath('0.start_datetime', $appointment->fresh()->start_datetime?->toIso8601String());
    }

    public function test_barber_profile_endpoint_returns_employee_name_and_image_urls(): void
    {
        ['user' => $barberUser, 'employee' => $employee] = $this->createBarber([
            'photo_path' => 'images/profile/avatar.jpg',
            'bio' => 'Precision cuts and sharp fades.',
        ]);

        EmployeeImage::create([
            'employee_id' => $employee->id,
            'image_path' => 'images/profile/gallery-1.jpg',
        ]);

        Sanctum::actingAs($barberUser);

        $baseUrl = rtrim((string) config('app.url'), '/');

        $this->getJson('/api/barber/profile')
            ->assertOk()
            ->assertJsonPath('name', $employee->name)
            ->assertJsonPath('description', 'Precision cuts and sharp fades.')
            ->assertJsonPath('photo_url', $baseUrl . '/storage/images/profile/avatar.jpg')
            ->assertJsonPath('gallery.0.image_url', $baseUrl . '/storage/images/profile/gallery-1.jpg');
    }

    public function test_barber_profile_update_persists_employee_name_and_description(): void
    {
        ['user' => $barberUser, 'employee' => $employee] = $this->createBarber([
            'name' => 'Original Barber',
            'bio' => 'Original bio',
        ]);

        Sanctum::actingAs($barberUser);

        $this->postJson('/api/barber/profile', [
            'name' => 'Updated Barber',
            'description' => 'Updated bio',
        ])->assertOk()
            ->assertJsonPath('name', 'Updated Barber')
            ->assertJsonPath('description', 'Updated bio');

        $this->assertSame('Updated Barber', $employee->fresh()->name);
        $this->assertSame('Updated bio', $employee->fresh()->bio);
    }

    public function test_barber_reviews_endpoint_returns_customer_names(): void
    {
        ['user' => $barberUser, 'employee' => $employee] = $this->createBarber();
        $customer = $this->createCustomer();
        $service = Service::create([
            'name' => 'Beard Trim',
            'description' => 'Beard trim and cleanup.',
        ]);
        $appointment = $this->createAppointment($customer, $employee, $service, [
            'status' => 'completed',
        ]);

        Review::create([
            'customer_id' => $customer->id,
            'appointment_id' => $appointment->id,
            'rating' => 5,
            'comment' => 'Great barber.',
            'is_visible' => true,
        ]);

        Sanctum::actingAs($barberUser);

        $this->getJson('/api/barber/reviews')
            ->assertOk()
            ->assertJsonPath('0.client', $customer->name)
            ->assertJsonPath('0.rating', 5)
            ->assertJsonPath('0.text', 'Great barber.');
    }

    private function createBarber(array $employeeOverrides = []): array
    {
        $user = User::create([
            'email' => 'barber-' . Str::uuid() . '@example.test',
            'password' => bcrypt('password'),
            'role' => 'employee',
            'email_verified_at' => now(),
        ]);

        $employee = Employee::create(array_merge([
            'user_id' => $user->id,
            'name' => 'Barber ' . Str::upper(Str::random(6)),
            'phone' => 'employee-' . Str::uuid(),
            'bio' => 'Barber bio',
            'photo_path' => null,
            'instagram_url' => null,
        ], $employeeOverrides));

        return [
            'user' => $user,
            'employee' => $employee,
        ];
    }

    private function createCustomer(): Customer
    {
        $user = User::create([
            'email' => 'customer-' . Str::uuid() . '@example.test',
            'password' => bcrypt('password'),
            'role' => 'customer',
            'email_verified_at' => now(),
        ]);

        return Customer::create([
            'user_id' => $user->id,
            'name' => 'Customer ' . Str::upper(Str::random(6)),
            'phone' => 'customer-' . Str::uuid(),
            'email' => $user->email,
        ]);
    }

    private function createAppointment(Customer $customer, Employee $employee, Service $service, array $overrides = []): Appointment
    {
        $appointment = Appointment::create(array_merge([
            'customer_id' => $customer->id,
            'employee_id' => $employee->id,
            'total_duration' => 45,
            'total_price' => 9000,
            'status' => 'pending',
            'customer_note' => 'Please keep the top longer.',
            'start_datetime' => now()->addDay()->setTime(10, 0, 0),
            'end_datetime' => now()->addDay()->setTime(10, 45, 0),
        ], $overrides));

        AppointmentService::create([
            'appointment_id' => $appointment->id,
            'service_id' => $service->id,
            'duration' => 45,
            'price' => 9000,
        ]);

        return $appointment;
    }
}
