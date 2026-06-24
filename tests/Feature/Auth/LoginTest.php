<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login_successfully_and_receive_token(): void
    {
        $user = User::factory()->create([
            'email' => 'login@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'login@example.com',
            'password' => 'password123',
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Login successful')
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'user' => ['id', 'name', 'email'],
                    'token',
                ],
            ]);

        $this->assertNotEmpty($response->json('data.token'));
    }

    public function test_invalid_credentials_return_401(): void
    {
        User::factory()->create([
            'email' => 'valid@example.com',
            'password' => bcrypt('correct-password'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'valid@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertUnauthorized()
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Invalid credentials');
    }

    public function test_login_with_nonexistent_email_returns_401(): void
    {
        $response = $this->postJson('/api/login', [
            'email' => 'ghost@example.com',
            'password' => 'password123',
        ]);

        $response->assertUnauthorized()
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Invalid credentials');
    }

    public function test_missing_login_fields_return_422(): void
    {
        $response = $this->postJson('/api/login', []);

        $response->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Validation failed')
            ->assertJsonValidationErrors(['email', 'password']);
    }
}
