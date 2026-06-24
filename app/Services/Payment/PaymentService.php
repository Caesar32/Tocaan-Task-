<?php

namespace App\Services\Payment;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class PaymentService
{
    /**
     * Get a paginated list of payments for the authenticated user.
     */
    public function list(array $filters = []): LengthAwarePaginator
    {
        $user = Auth::guard('jwt')->user();
        $perPage = $filters['per_page'] ?? 15;

        return Payment::query()
            ->whereHas('order', fn ($q) => $q->where('user_id', $user->id))
            ->with('order')
            ->orderByDesc('created_at')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Get a single payment by ID for the authenticated user.
     */
    public function findById(int $id): Payment
    {
        $user = Auth::guard('jwt')->user();

        return Payment::where('id', $id)
            ->whereHas('order', fn ($q) => $q->where('user_id', $user->id))
            ->with('order')
            ->firstOrFail();
    }

    /**
     * Get all payments for a specific order belonging to the authenticated user.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getByOrderId(int $orderId)
    {
        $user = Auth::guard('jwt')->user();

        $order = Order::where('id', $orderId)
            ->where('user_id', $user->id)
            ->firstOrFail();

        return $order->payments()->orderByDesc('created_at')->get();
    }

    /**
     * Process a payment for a confirmed order using the appropriate gateway.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws \Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException
     */
    public function process(array $data): Payment
    {
        $user = Auth::guard('jwt')->user();

        // Load order scoped to authenticated user
        $order = Order::where('id', $data['order_id'])
            ->where('user_id', $user->id)
            ->firstOrFail();

        // Business rule: payments only for confirmed orders
        if ($order->status !== OrderStatus::Confirmed) {
            throw new \Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException(
                'Payments can only be processed for confirmed orders.'
            );
        }

        // Resolve gateway through factory
        $method = PaymentMethod::from($data['payment_method']);
        $gateway = PaymentGatewayFactory::make($method);

        // Execute gateway
        $result = $gateway->pay($order);

        // Create payment record regardless of result
        return Payment::create([
            'order_id' => $order->id,
            'payment_id' => 'pay_' . Str::uuid()->toString(),
            'payment_method' => $method,
            'status' => $result['status'],
            'amount' => $order->total,
            'gateway_response' => $result['gateway_response'],
        ]);
    }
}
