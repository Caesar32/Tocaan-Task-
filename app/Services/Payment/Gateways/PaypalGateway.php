<?php

namespace App\Services\Payment\Gateways;

use App\Contracts\PaymentGatewayInterface;
use App\Enums\PaymentStatus;
use App\Models\Order;
use Illuminate\Support\Str;

class PaypalGateway implements PaymentGatewayInterface
{
    /**
     * Process a PayPal payment with a 90% success rate.
     *
     * @return array{status: PaymentStatus, gateway_response: array<string, mixed>}
     */
    public function pay(Order $order): array
    {
        // Simulate 90% success, 10% failure
        $isSuccessful = random_int(1, 100) <= 90;

        if ($isSuccessful) {
            return [
                'status' => PaymentStatus::Successful,
                'gateway_response' => [
                    'transaction_id' => 'pp_' . Str::uuid()->toString(),
                    'message' => 'PayPal payment processed successfully.',
                ],
            ];
        }

        return [
            'status' => PaymentStatus::Failed,
            'gateway_response' => [
                'message' => 'PayPal payment failed. Please try again.',
            ],
        ];
    }
}
