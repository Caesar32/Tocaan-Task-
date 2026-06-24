<?php

namespace Tests\Feature\Order;

use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderDeleteTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_delete_order_without_payments(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->for($user)->create();

        $this->withJwtToken($user)
            ->deleteJson("/api/orders/{$order->id}")
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Order deleted successfully');

        $this->assertDatabaseMissing('orders', ['id' => $order->id]);
    }

    public function test_user_cannot_delete_order_with_payments(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->for($user)->create();
        Payment::factory()->for($order)->create([
            'status' => PaymentStatus::Successful,
        ]);

        $response = $this->withJwtToken($user)->deleteJson("/api/orders/{$order->id}");

        $response->assertStatus(409)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Order cannot be deleted because it has associated payments.');

        // Order should still exist
        $this->assertDatabaseHas('orders', ['id' => $order->id]);
    }

    public function test_delete_order_cascades_items(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->for($user)->create();

        $create = $this->withJwtToken($user)->postJson('/api/orders', [
            'customer_name' => 'Cascade Test',
            'customer_email' => 'cascade@example.com',
            'items' => [
                ['product_name' => 'Item 1', 'quantity' => 1, 'price' => 10.00],
                ['product_name' => 'Item 2', 'quantity' => 2, 'price' => 20.00],
            ],
        ]);

        $orderId = $create->json('data.id');

        // Delete the order
        $this->withJwtToken($user)->deleteJson("/api/orders/{$orderId}")->assertOk();

        // Order items should be cascaded
        $this->assertDatabaseMissing('order_items', ['order_id' => $orderId]);
    }

    public function test_user_cannot_delete_other_users_order(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $order = Order::factory()->for($owner)->create();

        $this->withJwtToken($intruder)
            ->deleteJson("/api/orders/{$order->id}")
            ->assertNotFound();

        // Order should still exist
        $this->assertDatabaseHas('orders', ['id' => $order->id]);
    }
}
