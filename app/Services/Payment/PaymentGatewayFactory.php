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
     * Accepts either a PaymentMethod enum or a raw string value for
     * convenience and defensive validation.
     *
     *
     * @throws InvalidArgumentException When an unsupported method value is given.
     */
    public static function make(PaymentMethod|string $method): PaymentGatewayInterface
    {
        // Resolve a raw string into the enum, rejecting unknown values explicitly.
        if (is_string($method)) {
            $method = PaymentMethod::tryFrom($method)
                ?? throw new InvalidArgumentException("Unsupported payment method: {$method}");
        }

        return match ($method) {
            PaymentMethod::PayPal => new PaypalGateway,
            PaymentMethod::CreditCard => new CreditCardGateway,
        };
    }
}
