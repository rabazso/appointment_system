<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Employee;
use App\Models\EmployeeVersion;
use App\Models\Service;
use App\Models\ServiceVersion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class BackendApiBasicFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_services_endpoint_returns_services(): void
    {
        $service = $this->createService('Haircut');

        $this->getJson('/api/services')
            ->assertOk()
            ->assertJsonPath('data.0.id', $service->id)
            ->assertJsonPath('data.0.name', 'Haircut');
    }

    public function test_customer_can_login_with_valid_credentials(): void
    {
        $user = $this->createUser('customer');
        $this->createCustomer($user);

        $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
        ])
            ->assertOk()
            ->assertJsonPath('user.role', 'customer')
            ->assertJsonStructure(['token']);
    }

    public function test_protected_customer_endpoint_requires_login(): void
    {
        $this->getJson('/api/user')
            ->assertUnauthorized();
    }

    public function test_customer_cannot_use_admin_service_create_endpoint(): void
    {
        $user = $this->createUser('customer');
        $this->createCustomer($user);

        $this->withApiToken($user)
            ->postJson('/api/services', [
                'name' => 'New Service',
                'description' => 'Test description',
            ])
            ->assertForbidden();
    }

    public function test_admin_can_create_service(): void
    {
        $admin = $this->createUser('admin');

        $this->withApiToken($admin)
            ->postJson('/api/services', [
                'name' => 'New Service',
                'description' => 'Test description',
            ])
            ->assertSuccessful()
            ->assertJsonPath('data.name', 'New Service');

        $this->assertDatabaseHas('services', [
            'name' => 'New Service',
        ]);
    }

    public function test_employee_can_open_own_profile_endpoint(): void
    {
        [$user, $employee] = $this->createEmployee();

        $this->withApiToken($user)
            ->getJson('/api/employee/profile')
            ->assertOk()
            ->assertJsonPath('name', $employee->name);
    }

    public function test_booking_requires_basic_required_fields(): void
    {
        $this->postJson('/api/appointments', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'service_ids',
                'employee_id',
                'appointment_start',
            ]);
    }

    private function createUser(string $role): User
    {
        return User::create([
            'email' => $role . '-' . Str::uuid() . '@example.test',
            'password' => bcrypt('password'),
            'role' => $role,
            'email_verified_at' => now(),
        ]);
    }

    private function createCustomer(User $user): Customer
    {
        return Customer::create([
            'user_id' => $user->id,
            'name' => 'Test Customer',
            'email' => $user->email,
            'phone' => 'customer-' . Str::uuid(),
        ]);
    }

    private function createEmployee(): array
    {
        $user = $this->createUser('employee');

        $employee = Employee::create([
            'user_id' => $user->id,
            'name' => 'Test Barber',
            'phone' => 'employee-' . Str::uuid(),
            'bio' => 'Test barber profile.',
        ]);

        EmployeeVersion::create([
            'employee_id' => $employee->id,
            'is_available' => true,
            'valid_from' => now()->subDay(),
        ]);

        return [$user, $employee];
    }

    private function createService(string $name): Service
    {
        $service = Service::create([
            'name' => $name,
            'description' => 'Simple test service.',
        ]);

        ServiceVersion::create([
            'service_id' => $service->id,
            'is_available' => true,
            'valid_from' => now()->subDay(),
        ]);

        return $service;
    }
}
