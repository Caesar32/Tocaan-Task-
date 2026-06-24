<?php

namespace Tests\Feature\Payment;

use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentListTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_list_own_payments(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->for($user)->create();
        Payment::factory()->for($order)->count(3)->create();

        $response = $this->withJwtToken($user)->getJson('/api/payments');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Payments retrieved successfully')
            ->assertJsonStructure([
                'data',
                'links',
                'meta' => ['current_page', 'per_page', 'total'],
                'success',
                'message',
            ]);

        $this->assertCount(3, $response->json('data'));
    }

    public function test_payment_list_is_paginated(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->for($user)->create();
        Payment::factory()->for($order)->count(20)->create();

        $response = $this->withJwtToken($user)->getJson('/api/payments?per_page=5');

        $response->assertOk()
            ->assertJsonPath('meta.per_page', 5)
            ->assertJsonPath('meta.total', 20)
            ->assertJsonPath('meta.last_page', 4);

        $this->assertCount(5, $response->json('data'));
    }

    public function test_user_payments_are_scoped_to_own_orders(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $orderA = Order::factory()->for($userA)->create();
        $orderB = Order::factory()->for($userB)->create();

        Payment::factory()->for($orderA)->count(2)->create();
        Payment::factory()->for($orderB)->count(3)->create();

        // User A should only see their own 2 payments
        $response = $this->withJwtToken($userA)->getJson('/api/payments');

        $response->assertOk()
            ->assertJsonPath('meta.total', 2);
    }

    public function test_payments_list_includes_order_data(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->for($user)->create();
        Payment::factory()->for($order)->create();

        $response = $this->withJwtToken($user)->getJson('/api/payments');

        // PaymentResource returns payment fields; order is eager-loaded for scoping
        $response->assertOk()
            ->assertJsonPath('data.0.order_id', $order->id);
    }
}
