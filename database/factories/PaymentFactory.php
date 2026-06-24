<?php

namespace Database\Factories;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'payment_id' => 'pay_'.Str::uuid()->toString(),
            'payment_method' => fake()->randomElement(PaymentMethod::cases()),
            'status' => PaymentStatus::Pending,
            'amount' => fake()->randomFloat(2, 10, 1000),
            'gateway_response' => null,
        ];
    }

    /**
     * Indicate the payment is successful.
     */
    public function successful(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PaymentStatus::Successful,
            'gateway_response' => [
                'transaction_id' => 'txn_'.Str::uuid()->toString(),
                'status' => 'approved',
                'message' => 'Payment processed successfully',
            ],
        ]);
    }

    /**
     * Indicate the payment has failed.
     */
    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PaymentStatus::Failed,
            'gateway_response' => [
                'error_code' => 'card_declined',
                'message' => 'The card was declined',
            ],
        ]);
    }
}
