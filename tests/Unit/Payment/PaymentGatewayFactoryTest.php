<?php

namespace Tests\Unit\Payment;

use App\Contracts\PaymentGatewayInterface;
use App\Enums\PaymentMethod;
use App\Services\Payment\Gateways\CreditCardGateway;
use App\Services\Payment\Gateways\PaypalGateway;
use App\Services\Payment\PaymentGatewayFactory;
use InvalidArgumentException;
use Tests\TestCase;

class PaymentGatewayFactoryTest extends TestCase
{
    public function test_factory_returns_paypal_gateway_for_paypal(): void
    {
        $gateway = PaymentGatewayFactory::make(PaymentMethod::PayPal);

        $this->assertInstanceOf(PaypalGateway::class, $gateway);
    }

    public function test_factory_returns_credit_card_gateway_for_credit_card(): void
    {
        $gateway = PaymentGatewayFactory::make(PaymentMethod::CreditCard);

        $this->assertInstanceOf(CreditCardGateway::class, $gateway);
    }

    public function test_factory_returns_gateway_implementing_contract(): void
    {
        $paypal = PaymentGatewayFactory::make(PaymentMethod::PayPal);
        $creditCard = PaymentGatewayFactory::make(PaymentMethod::CreditCard);

        $this->assertInstanceOf(PaymentGatewayInterface::class, $paypal);
        $this->assertInstanceOf(PaymentGatewayInterface::class, $creditCard);
    }

    public function test_factory_make_accepts_string_value(): void
    {
        $gateway = PaymentGatewayFactory::make(PaymentMethod::from('paypal'));

        $this->assertInstanceOf(PaypalGateway::class, $gateway);
    }

    public function test_all_enum_cases_resolve_to_correct_gateway(): void
    {
        $expected = [
            'paypal' => PaypalGateway::class,
            'credit_card' => CreditCardGateway::class,
        ];

        foreach (PaymentMethod::cases() as $method) {
            $gateway = PaymentGatewayFactory::make($method);
            $expectedClass = $expected[$method->value];
            $this->assertInstanceOf($expectedClass, $gateway, "PaymentMethod::{$method->value} did not resolve to {$expectedClass}");
            $this->assertInstanceOf(PaymentGatewayInterface::class, $gateway);
        }
    }

    public function test_factory_throws_exception_for_unsupported_payment_gateway(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported payment method: crypto');

        PaymentGatewayFactory::make('crypto');
    }
}
