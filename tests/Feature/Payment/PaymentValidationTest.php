<?php

namespace Tests\Feature\Payment;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_missing_payment_method_returns_422(): void
    {
        $user = User::factory()->create();

        $response = $this->withJwtToken($user)->postJson('/api/payments', [
            'order_id' => 1,
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Validation failed')
            ->assertJsonValidationErrors(['payment_method']);
    }

    public function test_missing_order_id_returns_422(): void
    {
        $user = User::factory()->create();

        $response = $this->withJwtToken($user)->postJson('/api/payments', [
            'payment_method' => 'paypal',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['order_id']);
    }

    public function test_invalid_payment_method_returns_422(): void
    {
        $user = User::factory()->create();

        $response = $this->withJwtToken($user)->postJson('/api/payments', [
            'order_id' => 1,
            'payment_method' => 'crypto',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['payment_method']);
    }

    public function test_nonexistent_order_returns_422(): void
    {
        $user = User::factory()->create();

        $response = $this->withJwtToken($user)->postJson('/api/payments', [
            'order_id' => 9999,
            'payment_method' => 'paypal',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['order_id']);
    }

    public function test_empty_payload_returns_422(): void
    {
        $user = User::factory()->create();

        $response = $this->withJwtToken($user)->postJson('/api/payments', []);

        $response->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonValidationErrors(['order_id', 'payment_method']);
    }

    public function test_show_nonexistent_payment_returns_404(): void
    {
        $user = User::factory()->create();

        $this->withJwtToken($user)
            ->getJson('/api/payments/9999')
            ->assertNotFound()
            ->assertJsonPath('success', false);
    }
}
