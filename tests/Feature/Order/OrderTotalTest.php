<?php

namespace Tests\Feature\Order;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderTotalTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_total_is_calculated_from_item_subtotals(): void
    {
        $user = User::factory()->create();

        $response = $this->withJwtToken($user)->postJson('/api/orders', [
            'customer_name' => 'John Doe',
            'customer_email' => 'john@example.com',
            'items' => [
                ['product_name' => 'Item A', 'quantity' => 2, 'price' => 29.99],  // 59.98
                ['product_name' => 'Item B', 'quantity' => 3, 'price' => 9.50],   // 28.50
            ],
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.total', '88.48');

        // Verify subtotals are correct
        $this->assertEquals('59.98', $response->json('data.items.0.subtotal'));
        $this->assertEquals('28.50', $response->json('data.items.1.subtotal'));
    }

    public function test_updating_items_recalculates_total(): void
    {
        $user = User::factory()->create();

        // Create order with original items (total = 100.00)
        $create = $this->withJwtToken($user)->postJson('/api/orders', [
            'customer_name' => 'John Doe',
            'customer_email' => 'john@example.com',
            'items' => [
                ['product_name' => 'Original', 'quantity' => 1, 'price' => 100.00],
            ],
        ]);

        $orderId = $create->json('data.id');
        $this->assertEquals('100.00', $create->json('data.total'));

        // Update with new items (total should become 75.00)
        $update = $this->withJwtToken($user)->putJson("/api/orders/{$orderId}", [
            'items' => [
                ['product_name' => 'New Item', 'quantity' => 3, 'price' => 25.00],  // 75.00
            ],
        ]);

        $update->assertOk()
            ->assertJsonPath('data.total', '75.00');
    }

    public function test_updating_order_without_items_preserves_total(): void
    {
        $user = User::factory()->create();

        $create = $this->withJwtToken($user)->postJson('/api/orders', [
            'customer_name' => 'John Doe',
            'customer_email' => 'john@example.com',
            'items' => [
                ['product_name' => 'Item', 'quantity' => 2, 'price' => 50.00],  // 100.00
            ],
        ]);

        $orderId = $create->json('data.id');

        // Update only the name, total should stay 100.00
        $update = $this->withJwtToken($user)->putJson("/api/orders/{$orderId}", [
            'customer_name' => 'Renamed Customer',
        ]);

        $update->assertOk()
            ->assertJsonPath('data.customer_name', 'Renamed Customer')
            ->assertJsonPath('data.total', '100.00');
    }

    public function test_order_total_never_accepts_client_value(): void
    {
        $user = User::factory()->create();

        $response = $this->withJwtToken($user)->postJson('/api/orders', [
            'customer_name' => 'John Doe',
            'customer_email' => 'john@example.com',
            'total' => 999999.99,  // Client tries to inject total — should be ignored
            'items' => [
                ['product_name' => 'Item', 'quantity' => 1, 'price' => 10.00],
            ],
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.total', '10.00');  // 10.00, not 999999.99
    }
}
