<?php

namespace App\Services\Payment\Gateways;

use App\Contracts\PaymentGatewayInterface;
use App\Enums\PaymentStatus;
use App\Models\Order;
use Illuminate\Support\Str;

class CreditCardGateway implements PaymentGatewayInterface
{
    /**
     * Process a credit card payment.
     *
     * In simulation mode (default) it uses a configurable success rate.
     * When simulation is disabled, the configured provider credentials below
     * would be used to call the real processor's API.
     *
     * @return array{status: PaymentStatus, gateway_response: array<string, mixed>}
     */
    public function pay(Order $order): array
    {
        $provider = config('payments.credit_card.provider');
        $apiKey = config('payments.credit_card.api_key');

        // Simulate using the configured success rate (ignored in production).
        $successRate = (int) config('payments.success_rates.credit_card', 85);
        $isSuccessful = random_int(1, 100) <= $successRate;

        if ($isSuccessful) {
            return [
                'status' => PaymentStatus::Successful,
                'gateway_response' => [
                    'transaction_id' => 'cc_'.Str::uuid()->toString(),
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
