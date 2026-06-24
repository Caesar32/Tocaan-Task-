<?php

namespace Tests\Feature\Payment;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentProcessTest extends TestCase
{
    use RefreshDatabase;

    public function test_confirmed_order_can_be_paid(): void
    {
        $user = User::factory()->create();
        $order = $this->createConfirmedOrder($user, 150.00);

        $response = $this->withJwtToken($user)->postJson('/api/payments', [
            'order_id' => $order->id,
            'payment_method' => 'paypal',
        ]);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id', 'payment_id', 'order_id', 'payment_method',
                    'status', 'amount', 'gateway_response', 'created_at',
                ],
            ]);

        $this->assertContains($response->json('data.status'), ['successful', 'failed']);
    }

    public function test_pending_order_cannot_be_paid(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->for($user)->create(['status' => OrderStatus::Pending]);

        $response = $this->withJwtToken($user)->postJson('/api/payments', [
            'order_id' => $order->id,
            'payment_method' => 'paypal',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Payments can only be processed for confirmed orders.');
    }

    public function test_cancelled_order_cannot_be_paid(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->for($user)->create(['status' => OrderStatus::Cancelled]);

        $response = $this->withJwtToken($user)->postJson('/api/payments', [
            'order_id' => $order->id,
            'payment_method' => 'credit_card',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Payments can only be processed for confirmed orders.');
    }

    public function test_payment_amount_equals_order_total(): void
    {
        $user = User::factory()->create();
        $order = $this->createConfirmedOrder($user, 250.00);

        $response = $this->withJwtToken($user)->postJson('/api/payments', [
            'order_id' => $order->id,
            'payment_method' => 'paypal',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.amount', '250.00');
    }

    public function test_payment_record_is_stored_even_if_gateway_returns_failed(): void
    {
        $user = User::factory()->create();
        $order = $this->createConfirmedOrder($user, 100.00);

        $response = $this->withJwtToken($user)->postJson('/api/payments', [
            'order_id' => $order->id,
            'payment_method' => 'credit_card',
        ]);

        $response->assertCreated();

        $status = $response->json('data.status');

        // Payment is stored regardless of success/failure
        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id,
            'amount' => 100.00,
        ]);

        // Verify the gateway_response is stored as array
        $this->assertNotNull($response->json('data.gateway_response'));
    }

    public function test_payment_via_credit_card_works(): void
    {
        $user = User::factory()->create();
        $order = $this->createConfirmedOrder($user, 75.50);

        $response = $this->withJwtToken($user)->postJson('/api/payments', [
            'order_id' => $order->id,
            'payment_method' => 'credit_card',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.payment_method', 'credit_card')
            ->assertJsonPath('data.amount', '75.50');
    }

    /**
     * Helper: create a confirmed order with a single item that sets the given total.
     */
    private function createConfirmedOrder(User $user, float $total): Order
    {
        $order = Order::factory()->for($user)->create([
            'status' => OrderStatus::Confirmed,
            'total' => $total,
        ]);

        OrderItem::factory()->for($order)->create([
            'price' => $total,
            'quantity' => 1,
            'subtotal' => $total,
        ]);

        return $order->fresh();
    }
}
