<?php

namespace Tests\Feature\Payment;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_cannot_access_another_users_payment(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();

        $order = Order::factory()->for($owner)->create();
        $payment = Payment::factory()->for($order)->create();

        // Intruder cannot view owner's payment
        $this->withJwtToken($intruder)
            ->getJson("/api/payments/{$payment->id}")
            ->assertNotFound();
    }

    public function test_user_cannot_process_payment_for_another_users_order(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();

        $order = Order::factory()->for($owner)->create(['status' => OrderStatus::Confirmed]);

        // Intruder tries to pay for owner's order
        $this->withJwtToken($intruder)->postJson('/api/payments', [
            'order_id' => $order->id,
            'payment_method' => 'paypal',
        ])
            ->assertNotFound();
    }

    public function test_user_cannot_view_payments_for_another_users_order(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();

        $order = Order::factory()->for($owner)->create();
        Payment::factory()->for($order)->create();

        // Intruder cannot list payments for owner's order
        $this->withJwtToken($intruder)
            ->getJson("/api/orders/{$order->id}/payments")
            ->assertNotFound();
    }

    public function test_user_can_view_own_payment(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->for($user)->create();
        $payment = Payment::factory()->for($order)->create();

        $this->withJwtToken($user)
            ->getJson("/api/payments/{$payment->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $payment->id);
    }

    public function test_user_can_view_payments_for_own_order(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->for($user)->create();
        Payment::factory()->for($order)->count(2)->create();

        $response = $this->withJwtToken($user)
            ->getJson("/api/orders/{$order->id}/payments");

        $response->assertOk()
            ->assertJsonPath('success', true);

        $this->assertCount(2, $response->json('data'));
    }
}
