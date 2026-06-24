<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tymon\JWTAuth\Exceptions\TokenBlacklistedException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_access_me(): void
    {
        $user = User::factory()->create();
        $token = JWTAuth::fromUser($user);

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/me');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'User profile retrieved successfully')
            ->assertJsonPath('data.user.id', $user->id)
            ->assertJsonPath('data.user.email', $user->email);
    }

    public function test_user_can_refresh_token(): void
    {
        $user = User::factory()->create();
        $token = JWTAuth::fromUser($user);

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/refresh');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Token refreshed successfully')
            ->assertJsonStructure([
                'success',
                'message',
                'data' => ['user', 'token'],
            ]);

        $this->assertNotEmpty($response->json('data.token'));
    }

    public function test_user_can_logout_and_token_is_blacklisted(): void
    {
        $user = User::factory()->create();
        $token = JWTAuth::fromUser($user);

        // Logout — invalidates (blacklists) the token
        $logout = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/logout');

        $logout->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'User logged out successfully');

        // The token should now be blacklisted: JWT manager refuses to validate it.
        $this->assertFalse(JWTAuth::setToken($token)->check(), 'Token should be blacklisted after logout');

        // Using the token after it is blacklisted must throw a blacklist exception.
        try {
            JWTAuth::setToken($token)->authenticate();
            $this->fail('Expected TokenBlacklistedException was not thrown');
        } catch (TokenBlacklistedException $e) {
            $this->assertStringContainsString('blacklisted', $e->getMessage());
        }
    }
}
