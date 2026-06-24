<?php

namespace Tests\Feature\Auth;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthProtectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_protected_order_routes_without_token_return_401(): void
    {
        $this->getJson('/api/orders')->assertUnauthorized();
        $this->getJson('/api/orders/1')->assertUnauthorized();
        $this->postJson('/api/orders', [])->assertUnauthorized();
        $this->putJson('/api/orders/1', [])->assertUnauthorized();
        $this->deleteJson('/api/orders/1')->assertUnauthorized();
    }

    public function test_protected_payment_routes_without_token_return_401(): void
    {
        $this->getJson('/api/payments')->assertUnauthorized();
        $this->getJson('/api/payments/1')->assertUnauthorized();
        $this->postJson('/api/payments', [])->assertUnauthorized();
        $this->getJson('/api/orders/1/payments')->assertUnauthorized();
    }

    public function test_me_without_token_returns_401(): void
    {
        $this->getJson('/api/me')
            ->assertUnauthorized()
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Unauthenticated. Please provide a valid token.');
    }

    public function test_logout_without_token_returns_401(): void
    {
        $this->postJson('/api/logout')
            ->assertUnauthorized()
            ->assertJsonPath('success', false);
    }

    public function test_refresh_without_token_returns_401(): void
    {
        $this->postJson('/api/refresh')
            ->assertUnauthorized()
            ->assertJsonPath('success', false);
    }

    public function test_invalid_token_returns_401(): void
    {
        $this->withHeader('Authorization', 'Bearer an.invalid.token')
            ->getJson('/api/me')
            ->assertUnauthorized();
    }
}
