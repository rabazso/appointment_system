<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\VerifyEmailNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
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

    public function test_registration_sends_the_custom_verification_notification(): void
    {
        Notification::fake();

        $this->postJson('/api/register', [
            'name' => 'Fresh Client',
            'email' => 'fresh-client@example.test',
            'password' => 'Password1!',
            'password_confirmation' => 'Password1!',
        ])->assertCreated();

        $user = User::where('email', 'fresh-client@example.test')->firstOrFail();

        Notification::assertSentTo($user, VerifyEmailNotification::class);
    }

    public function test_resend_verification_uses_the_custom_verification_notification(): void
    {
        Notification::fake();

        $user = $this->createUser(['email_verified_at' => null]);

        Sanctum::actingAs($user);

        $this->postJson('/api/email/verification-notification')
            ->assertOk()
            ->assertJson(['message' => 'Verification email sent']);

        Notification::assertSentTo($user, VerifyEmailNotification::class);
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
