<?php

namespace Tests;

use App\Models\ApiToken;
use App\Models\User;
use DateTimeInterface;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function apiTokenFor(User $user, ?DateTimeInterface $expiresAt = null): string
    {
        return ApiToken::issueForUser($user, $expiresAt ?? now()->addDay());
    }

    protected function withApiToken(User $user, ?DateTimeInterface $expiresAt = null): static
    {
        $token = $this->apiTokenFor($user, $expiresAt);
        $this->withHeader('Authorization', 'Bearer ' . $token);

        return $this;
    }
}
