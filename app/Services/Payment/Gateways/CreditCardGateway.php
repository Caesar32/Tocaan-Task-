<?php

namespace App\Services\Payment\Gateways;

use App\Contracts\PaymentGatewayInterface;
use App\Enums\PaymentStatus;
use App\Models\Order;
use Illuminate\Support\Str;

class CreditCardGateway implements PaymentGatewayInterface
{
    /**
     * Process a credit card payment with an 85% success rate.
     *
     * @return array{status: PaymentStatus, gateway_response: array<string, mixed>}
     */
    public function pay(Order $order): array
    {
        // Simulate 85% success, 15% failure
        $isSuccessful = random_int(1, 100) <= 85;

        if ($isSuccessful) {
            return [
                'status' => PaymentStatus::Successful,
                'gateway_response' => [
                    'transaction_id' => 'cc_' . Str::uuid()->toString(),
                    'message' => 'Credit card payment processed successfully.',
                ],
            ];
        }

        return [
            'status' => PaymentStatus::Failed,
            'gateway_response' => [
                'message' => 'Credit card payment was declined.',
            ],
        ];
    }
}
