<?php

namespace Tests\Feature\Order;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_create_order(): void
    {
        $user = User::factory()->create();

        $response = $this->withJwtToken($user)->postJson('/api/orders', [
            'customer_name' => 'John Doe',
            'customer_email' => 'john@example.com',
            'items' => [
                ['product_name' => 'Widget', 'quantity' => 2, 'price' => 10.00],
            ],
        ]);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Order created successfully')
            ->assertJsonPath('data.customer_name', 'John Doe')
            ->assertJsonPath('data.status', 'pending')
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id', 'customer_name', 'customer_email', 'status', 'total',
                    'items' => [['id', 'product_name', 'quantity', 'price', 'subtotal']],
                    'payments_count', 'created_at', 'updated_at',
                ],
            ]);

        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'customer_name' => 'John Doe',
            'customer_email' => 'john@example.com',
        ]);
    }

    public function test_user_can_list_paginated_orders(): void
    {
        $user = User::factory()->create();
        Order::factory()->count(3)->for($user)->create();

        $response = $this->withJwtToken($user)->getJson('/api/orders');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Orders retrieved successfully')
            ->assertJsonStructure([
                'data',
                'links',
                'meta' => ['current_page', 'per_page', 'total', 'last_page'],
                'success',
                'message',
            ]);

        $this->assertCount(3, $response->json('data'));
    }

    public function test_user_can_show_single_order(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->for($user)->create();

        $response = $this->withJwtToken($user)->getJson("/api/orders/{$order->id}");

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Order retrieved successfully')
            ->assertJsonPath('data.id', $order->id);
    }

    public function test_user_can_update_order_fields(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->for($user)->create();

        $response = $this->withJwtToken($user)->putJson("/api/orders/{$order->id}", [
            'customer_name' => 'Updated Name',
            'customer_email' => 'updated@example.com',
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Order updated successfully')
            ->assertJsonPath('data.customer_name', 'Updated Name')
            ->assertJsonPath('data.customer_email', 'updated@example.com');

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'customer_name' => 'Updated Name',
            'customer_email' => 'updated@example.com',
        ]);
    }

    public function test_user_can_delete_order_without_payments(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->for($user)->create();

        $response = $this->withJwtToken($user)->deleteJson("/api/orders/{$order->id}");

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Order deleted successfully');

        $this->assertDatabaseMissing('orders', ['id' => $order->id]);
    }
}
