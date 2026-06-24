<?php

namespace App\Services\Payment\Gateways;

use App\Contracts\PaymentGatewayInterface;
use App\Enums\PaymentStatus;
use App\Models\Order;
use Illuminate\Support\Str;

class PaypalGateway implements PaymentGatewayInterface
{
    /**
     * Process a PayPal payment.
     *
     * In simulation mode (default) it uses a configurable success rate.
     * When simulation is disabled, the configured PayPal credentials below
     * would be used to call the PayPal REST API.
     *
     * @return array{status: PaymentStatus, gateway_response: array<string, mixed>}
     */
    public function pay(Order $order): array
    {
        $clientId = config('payments.paypal.client_id');
        $mode = config('payments.paypal.mode', 'sandbox');

        // Simulate using the configured success rate (ignored in production).
        $successRate = (int) config('payments.success_rates.paypal', 90);
        $isSuccessful = random_int(1, 100) <= $successRate;

        if ($isSuccessful) {
            return [
                'status' => PaymentStatus::Successful,
                'gateway_response' => [
                    'transaction_id' => 'pp_'.Str::uuid()->toString(),
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
