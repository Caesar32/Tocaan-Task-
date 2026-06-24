<?php

namespace Database\Seeders;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Use existing user or create one
        $user = User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => bcrypt('password123'),
            ]
        );

        // -------------------------------------------------------
        // Order 1: Pending with 2 items (no payment)
        // -------------------------------------------------------
        $order1 = Order::create([
            'user_id' => $user->id,
            'customer_name' => 'John Smith',
            'customer_email' => 'john@example.com',
            'status' => OrderStatus::Pending,
            'total' => 0,
        ]);

        OrderItem::create([
            'order_id' => $order1->id,
            'product_name' => 'Wireless Bluetooth Headphones',
            'quantity' => 1,
            'price' => 59.99,
            'subtotal' => 59.99,
        ]);

        OrderItem::create([
            'order_id' => $order1->id,
            'product_name' => 'USB-C Charging Cable',
            'quantity' => 3,
            'price' => 12.99,
            'subtotal' => 38.97,
        ]);

        $order1->update([
            'total' => $order1->items()->sum('subtotal'),
        ]);

        // -------------------------------------------------------
        // Order 2: Confirmed with 3 items + successful payment
        // -------------------------------------------------------
        $order2 = Order::create([
            'user_id' => $user->id,
            'customer_name' => 'Jane Doe',
            'customer_email' => 'jane@example.com',
            'status' => OrderStatus::Confirmed,
            'total' => 0,
        ]);

        OrderItem::create([
            'order_id' => $order2->id,
            'product_name' => 'Mechanical Keyboard',
            'quantity' => 1,
            'price' => 129.99,
            'subtotal' => 129.99,
        ]);

        OrderItem::create([
            'order_id' => $order2->id,
            'product_name' => 'Gaming Mouse',
            'quantity' => 1,
            'price' => 49.99,
            'subtotal' => 49.99,
        ]);

        OrderItem::create([
            'order_id' => $order2->id,
            'product_name' => 'Mouse Pad XL',
            'quantity' => 2,
            'price' => 19.99,
            'subtotal' => 39.98,
        ]);

        $order2->update([
            'total' => $order2->items()->sum('subtotal'),
        ]);

        Payment::create([
            'order_id' => $order2->id,
            'payment_id' => 'pay_'.Str::uuid()->toString(),
            'payment_method' => PaymentMethod::CreditCard,
            'status' => PaymentStatus::Successful,
            'amount' => $order2->total,
            'gateway_response' => [
                'transaction_id' => 'txn_'.Str::uuid()->toString(),
                'status' => 'approved',
                'message' => 'Payment processed successfully',
            ],
        ]);

        // -------------------------------------------------------
        // Order 3: Cancelled with 1 item (no payment)
        // -------------------------------------------------------
        $order3 = Order::create([
            'user_id' => $user->id,
            'customer_name' => 'Bob Wilson',
            'customer_email' => 'bob@example.com',
            'status' => OrderStatus::Cancelled,
            'total' => 0,
        ]);

        OrderItem::create([
            'order_id' => $order3->id,
            'product_name' => 'Standing Desk Converter',
            'quantity' => 1,
            'price' => 249.99,
            'subtotal' => 249.99,
        ]);

        $order3->update([
            'total' => $order3->items()->sum('subtotal'),
        ]);
    }
}
