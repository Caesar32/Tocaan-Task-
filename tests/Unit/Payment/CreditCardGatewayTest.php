<?php

namespace Tests\Unit\Payment;

use App\Contracts\PaymentGatewayInterface;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\User;
use App\Services\Payment\Gateways\CreditCardGateway;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreditCardGatewayTest extends TestCase
{
    use RefreshDatabase;

    public function test_pay_returns_array_with_status_and_gateway_response(): void
    {
        $order = Order::factory()->for(User::factory())->create();
        $gateway = new CreditCardGateway;

        $result = $gateway->pay($order);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('status', $result);
        $this->assertArrayHasKey('gateway_response', $result);
    }

    public function test_gateway_status_must_be_payment_status_enum_value(): void
    {
        $order = Order::factory()->for(User::factory())->create();
        $gateway = new CreditCardGateway;

        $result = $gateway->pay($order);

        $this->assertInstanceOf(PaymentStatus::class, $result['status']);
        $this->assertContains($result['status'], [
            PaymentStatus::Successful,
            PaymentStatus::Failed,
        ]);
    }

    public function test_gateway_response_must_be_an_array(): void
    {
        $order = Order::factory()->for(User::factory())->create();
        $gateway = new CreditCardGateway;

        $result = $gateway->pay($order);

        $this->assertIsArray($result['gateway_response']);
        $this->assertArrayHasKey('message', $result['gateway_response']);
    }

    public function test_successful_payment_includes_transaction_id(): void
    {
        $order = Order::factory()->for(User::factory())->create();
        $gateway = new CreditCardGateway;

        $foundSuccessful = false;

        for ($i = 0; $i < 50; $i++) {
            $result = $gateway->pay($order);

            if ($result['status'] === PaymentStatus::Successful) {
                $this->assertArrayHasKey('transaction_id', $result['gateway_response']);
                $this->assertStringStartsWith('cc_', $result['gateway_response']['transaction_id']);
                $foundSuccessful = true;
                break;
            }
        }

        $this->assertTrue($foundSuccessful, 'Expected at least one successful credit card payment in 50 attempts.');
    }

    public function test_failed_payment_response_has_message(): void
    {
        $order = Order::factory()->for(User::factory())->create();
        $gateway = new CreditCardGateway;

        $foundFailed = false;

        for ($i = 0; $i < 50; $i++) {
            $result = $gateway->pay($order);

            if ($result['status'] === PaymentStatus::Failed) {
                $this->assertArrayHasKey('message', $result['gateway_response']);
                $this->assertStringContainsString('declined', strtolower($result['gateway_response']['message']));
                $foundFailed = true;
                break;
            }
        }

        $this->assertTrue($foundFailed, 'Expected at least one failed credit card payment in 50 attempts.');
    }

    public function test_credit_card_gateway_implements_contract(): void
    {
        $gateway = new CreditCardGateway;

        $this->assertInstanceOf(PaymentGatewayInterface::class, $gateway);
    }
}
