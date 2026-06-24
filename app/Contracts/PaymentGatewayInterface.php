<?php

namespace App\Contracts;

use App\Enums\PaymentStatus;
use App\Models\Order;

interface PaymentGatewayInterface
{
    /**
     * Process a payment for the given order.
     *
     * @return array{status: PaymentStatus, gateway_response: array<string, mixed>}
     */
    public function pay(Order $order): array;
}
