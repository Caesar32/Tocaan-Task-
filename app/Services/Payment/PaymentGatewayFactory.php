<?php

namespace App\Services\Payment;

use App\Contracts\PaymentGatewayInterface;
use App\Enums\PaymentMethod;
use App\Services\Payment\Gateways\CreditCardGateway;
use App\Services\Payment\Gateways\PaypalGateway;
use InvalidArgumentException;

class PaymentGatewayFactory
{
    /**
     * Resolve the appropriate payment gateway for the given method.
     *
     * @throws InvalidArgumentException
     */
    public static function make(PaymentMethod $method): PaymentGatewayInterface
    {
        return match ($method) {
            PaymentMethod::PayPal => new PaypalGateway(),
            PaymentMethod::CreditCard => new CreditCardGateway(),
            default => throw new InvalidArgumentException(
                "Unsupported payment method: {$method->value}"
            ),
        };
    }
}
