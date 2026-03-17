<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Tests\TestCase;

class EmailVerificationFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_verification_endpoint_marks_user_as_verified(): void
    {
        $user = $this->createUser(['email_verified_at' => null]);

        $this->getJson($this->verificationUrlFor($user))
            ->assertOk()
            ->assertJson([
                'status' => 'verified',
                'message' => 'Your email has been verified successfully.',
            ]);

        $this->assertNotNull($user->fresh()->email_verified_at);
    }

    public function test_verification_endpoint_reports_already_verified_users(): void
    {
        $user = $this->createUser();

        $this->getJson($this->verificationUrlFor($user))
            ->assertOk()
            ->assertJson([
                'status' => 'already_verified',
                'message' => 'Your email is already verified.',
            ]);
    }

    public function test_verification_endpoint_rejects_expired_links(): void
    {
        $user = $this->createUser(['email_verified_at' => null]);

        $expiredUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->subMinute(),
            [
                'id' => $user->id,
                'hash' => sha1($user->getEmailForVerification()),
            ],
            false
        );

        $this->getJson($expiredUrl)
            ->assertStatus(403)
            ->assertJson([
                'status' => 'invalid',
                'message' => 'This verification link is invalid or has expired.',
            ]);

        $this->assertNull($user->fresh()->email_verified_at);
    }

    private function verificationUrlFor(User $user): string
    {
        return URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(config('auth.verification.expire', 60)),
            [
                'id' => $user->id,
                'hash' => sha1($user->getEmailForVerification()),
            ],
            false
        );
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
}
