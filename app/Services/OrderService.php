<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class OrderService
{
    /**
     * Get a paginated list of orders for the authenticated user, optionally filtered by status.
     */
    public function list(array $filters = []): LengthAwarePaginator
    {
        $user = Auth::guard('jwt')->user();
        $perPage = $filters['per_page'] ?? 15;

        $query = Order::query()
            ->where('user_id', $user->id)
            ->with(['items', 'payments']);

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query
            ->orderByDesc('created_at')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Create a new order with items inside a database transaction.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Order
    {
        $user = Auth::guard('jwt')->user();

        return \DB::transaction(function () use ($user, $data) {
            $order = Order::create([
                'user_id' => $user->id,
                'customer_name' => $data['customer_name'],
                'customer_email' => $data['customer_email'],
                'status' => $data['status'] ?? OrderStatus::Pending,
                'total' => 0,
            ]);

            $total = $this->createItems($order, $data['items']);

            $order->update(['total' => $total]);

            return $order->load(['items', 'payments']);
        });
    }

    /**
     * Find a single order by ID for the authenticated user.
     */
    public function findById(int $id): Order
    {
        $user = Auth::guard('jwt')->user();

        return Order::where('id', $id)
            ->where('user_id', $user->id)
            ->with(['items', 'payments'])
            ->firstOrFail();
    }

    /**
     * Update an order and optionally its items inside a database transaction.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(Order $order, array $data): Order
    {
        return \DB::transaction(function () use ($order, $data) {
            $fillableFields = [];

            if (isset($data['customer_name'])) {
                $fillableFields['customer_name'] = $data['customer_name'];
            }

            if (isset($data['customer_email'])) {
                $fillableFields['customer_email'] = $data['customer_email'];
            }

            if (isset($data['status'])) {
                $fillableFields['status'] = $data['status'];
            }

            if (isset($data['items'])) {
                // Delete existing items and recreate
                $order->items()->delete();
                $total = $this->createItems($order, $data['items']);
                $fillableFields['total'] = $total;
            }

            if (! empty($fillableFields)) {
                $order->update($fillableFields);
            }

            return $order->load(['items', 'payments']);
        });
    }

    /**
     * Delete an order if it has no associated payments.
     *
     * @throws \Symfony\Component\HttpKernel\Exception\ConflictHttpException
     */
    public function delete(Order $order): void
    {
        if ($order->payments()->exists()) {
            throw new \Symfony\Component\HttpKernel\Exception\ConflictHttpException(
                'Order cannot be deleted because it has associated payments.'
            );
        }

        $order->delete();
    }

    /**
     * Create order items and return the calculated total.
     *
     * @param  Order  $order
     * @param  array<int, array<string, mixed>>  $items
     * @return string Calculated total as a decimal string
     */
    private function createItems(Order $order, array $items): string
    {
        $total = 0;

        foreach ($items as $item) {
            $subtotal = round((float) $item['price'] * (int) $item['quantity'], 2);

            $order->items()->create([
                'product_name' => $item['product_name'],
                'quantity' => $item['quantity'],
                'price' => $item['price'],
                'subtotal' => $subtotal,
            ]);

            $total += $subtotal;
        }

        return (string) round($total, 2);
    }
}
