<?php

namespace Tests\Feature\Order;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_validation_errors_for_missing_items_return_422(): void
    {
        $user = User::factory()->create();

        $response = $this->withJwtToken($user)->postJson('/api/orders', [
            'customer_name' => 'John Doe',
            'customer_email' => 'john@example.com',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Validation failed')
            ->assertJsonValidationErrors(['items']);
    }

    public function test_validation_error_for_missing_customer_name(): void
    {
        $user = User::factory()->create();

        $response = $this->withJwtToken($user)->postJson('/api/orders', [
            'customer_email' => 'john@example.com',
            'items' => [
                ['product_name' => 'Widget', 'quantity' => 1, 'price' => 10.00],
            ],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['customer_name']);
    }

    public function test_validation_error_for_invalid_email(): void
    {
        $user = User::factory()->create();

        $response = $this->withJwtToken($user)->postJson('/api/orders', [
            'customer_name' => 'John Doe',
            'customer_email' => 'not-an-email',
            'items' => [
                ['product_name' => 'Widget', 'quantity' => 1, 'price' => 10.00],
            ],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['customer_email']);
    }

    public function test_validation_error_for_zero_quantity(): void
    {
        $user = User::factory()->create();

        $response = $this->withJwtToken($user)->postJson('/api/orders', [
            'customer_name' => 'John Doe',
            'customer_email' => 'john@example.com',
            'items' => [
                ['product_name' => 'Widget', 'quantity' => 0, 'price' => 10.00],
            ],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['items.0.quantity']);
    }

    public function test_validation_error_for_negative_price(): void
    {
        $user = User::factory()->create();

        $response = $this->withJwtToken($user)->postJson('/api/orders', [
            'customer_name' => 'John Doe',
            'customer_email' => 'john@example.com',
            'items' => [
                ['product_name' => 'Widget', 'quantity' => 1, 'price' => -5.00],
            ],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['items.0.price']);
    }

    public function test_show_nonexistent_order_returns_404(): void
    {
        $user = User::factory()->create();

        $this->withJwtToken($user)
            ->getJson('/api/orders/9999')
            ->assertNotFound()
            ->assertJsonPath('success', false);
    }
}
