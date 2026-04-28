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

        $this->withApiToken($barberUser);

        $this->getJson('/api/employee/appointments')
            ->assertOk()
            ->assertJsonPath('data.0.id', $appointment->id)
            ->assertJsonPath('data.0.client', $customer->name)
            ->assertJsonPath('data.0.service', $service->name)
            ->assertJsonPath('data.0.status', 'confirmed')
            ->assertJsonPath('data.0.start_datetime', $appointment->fresh()->start_datetime?->toIso8601String());
    }

    public function test_barber_profile_endpoint_returns_employee_name_and_image_urls(): void
    {
        ['user' => $barberUser, 'employee' => $employee] = $this->createBarber([
            'bio' => 'Precision cuts and sharp fades.',
        ]);

        $profileImage = EmployeeImage::create([
            'employee_id' => $employee->id,
            'type' => 'image/jpeg',
            'original' => 'profile-original',
            'preview' => 'profile-preview',
        ]);

        $employee->update(['profile_image_id' => $profileImage->id]);

        $galleryImage = EmployeeImage::create([
            'employee_id' => $employee->id,
            'type' => 'image/jpeg',
            'original' => 'gallery-original',
            'preview' => 'gallery-preview',
        ]);

        $this->withApiToken($barberUser);

        $this->getJson('/api/employee/profile')
            ->assertOk()
            ->assertJsonPath('name', $employee->name)
            ->assertJsonPath('description', 'Precision cuts and sharp fades.')
            ->assertJsonPath('photo_url', route('employee-images.preview', ['employeeImage' => $profileImage]))
            ->assertJsonPath('gallery.0.preview_url', route('employee-images.preview', ['employeeImage' => $galleryImage]));
    }

    public function test_barber_profile_update_persists_employee_name_and_description(): void
    {
        ['user' => $barberUser, 'employee' => $employee] = $this->createBarber([
            'name' => 'Original Barber',
            'bio' => 'Original bio',
        ]);

        $this->withApiToken($barberUser);

        $this->patchJson('/api/employee/profile', [
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

        $this->withApiToken($barberUser);

        $this->getJson('/api/employee/reviews')
            ->assertOk()
            ->assertJsonPath('data.0.client', $customer->name)
            ->assertJsonPath('data.0.rating', 5)
            ->assertJsonPath('data.0.comment', 'Great barber.');
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
