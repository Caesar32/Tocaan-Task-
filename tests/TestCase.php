<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use JWTAuth;

abstract class TestCase extends BaseTestCase
{
    /**
     * Generate a real JWT token for the given user and set it as the Bearer token.
     * This replaces actingAs($user, 'jwt') which is incompatible with tymon/jwt-auth.
     */
    protected function withJwtToken(User $user): self
    {
        $token = JWTAuth::fromUser($user);

        return $this->withHeader('Authorization', 'Bearer ' . $token);
    }
}
