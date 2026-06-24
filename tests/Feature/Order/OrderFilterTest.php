<?php

namespace Tests\Feature\Order;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_filter_by_valid_status_confirmed_works(): void
    {
        $user = User::factory()->create();
        Order::factory()->for($user)->create(['status' => OrderStatus::Pending]);
        Order::factory()->for($user)->create(['status' => OrderStatus::Confirmed]);
        Order::factory()->for($user)->create(['status' => OrderStatus::Cancelled]);

        $response = $this->withJwtToken($user)->getJson('/api/orders?status=confirmed');

        $response->assertOk()
            ->assertJsonPath('meta.total', 1);

        $this->assertEquals('confirmed', $response->json('data.0.status'));
    }

    public function test_filter_by_valid_status_pending_works(): void
    {
        $user = User::factory()->create();
        Order::factory()->for($user)->count(2)->create(['status' => OrderStatus::Pending]);
        Order::factory()->for($user)->create(['status' => OrderStatus::Confirmed]);

        $response = $this->withJwtToken($user)->getJson('/api/orders?status=pending');

        $response->assertOk()
            ->assertJsonPath('meta.total', 2);

        collect($response->json('data'))->each(fn ($order) => $this->assertEquals('pending', $order['status']));
    }

    public function test_invalid_status_filter_returns_422(): void
    {
        $user = User::factory()->create();

        $response = $this->withJwtToken($user)->getJson('/api/orders?status=invalid');

        $response->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Validation failed')
            ->assertJsonValidationErrors(['status']);
    }

    public function test_no_filter_returns_all_statuses(): void
    {
        $user = User::factory()->create();
        Order::factory()->for($user)->create(['status' => OrderStatus::Pending]);
        Order::factory()->for($user)->create(['status' => OrderStatus::Confirmed]);
        Order::factory()->for($user)->create(['status' => OrderStatus::Cancelled]);

        $response = $this->withJwtToken($user)->getJson('/api/orders');

        $response->assertOk()
            ->assertJsonPath('meta.total', 3);
    }
}
