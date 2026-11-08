<?php

namespace Tests\Unit;

use App\Models\Order;
use Tests\TestCase;

class OrderLifecycleTest extends TestCase
{
    public function test_completed_and_cancelled_orders_are_finalized(): void
    {
        $completed = new Order(['status' => Order::STATUS_COMPLETED]);
        $cancelled = new Order(['status' => Order::STATUS_CANCELLED]);
        $processing = new Order(['status' => Order::STATUS_PROCESSING]);

        $this->assertTrue($completed->isFinalized());
        $this->assertTrue($cancelled->isFinalized());
        $this->assertFalse($processing->isFinalized());
    }

    public function test_profile_status_distinguishes_cancellation_request_from_final_cancellation(): void
    {
        $requestedCancellation = new Order(['status' => Order::STATUS_READY_FOR_PICKUP]);
        $requestedCancellation->cancellation_requested_at = now();

        $cancelled = new Order(['status' => Order::STATUS_CANCELLED]);
        $cancelled->cancellation_requested_at = now();

        $this->assertSame(Order::ORDER_CANCELLATION, $requestedCancellation->profile_status);
        $this->assertSame(Order::ORDER_CANCELLED, $cancelled->profile_status);
    }

    public function test_profile_status_label_shows_pending_cancellation(): void
    {
        $order = new Order(['status' => Order::STATUS_PROCESSING]);
        $order->cancellation_requested_at = now();

        $this->assertSame('Отправлен на отмену', $order->profile_status_label);
    }

    public function test_courier_order_uses_in_transit_status_before_completion(): void
    {
        $order = new Order([
            'status' => Order::STATUS_IN_TRANSIT,
            'delivery_method' => Order::DELIVERY_COURIER,
        ]);

        $this->assertSame(Order::ORDER_IN_TRANSIT, $order->profile_status);
        $this->assertSame('Передан в доставку', $order->profile_status_label);
        $this->assertFalse($order->isFinalized());
    }

    public function test_legacy_courier_ready_status_is_presented_as_in_transit(): void
    {
        $order = new Order([
            'status' => Order::STATUS_READY_FOR_PICKUP,
            'delivery_method' => Order::DELIVERY_COURIER,
        ]);

        $this->assertSame(Order::ORDER_IN_TRANSIT, $order->profile_status);
        $this->assertSame('Передан в доставку', $order->profile_status_label);
    }

    public function test_pickup_ready_status_remains_ready_for_pickup(): void
    {
        $order = new Order([
            'status' => Order::STATUS_READY_FOR_PICKUP,
            'delivery_method' => Order::DELIVERY_PICKUP,
        ]);

        $this->assertSame(Order::ORDER_READY_FOR_PICKUP, $order->profile_status);
        $this->assertSame('Готов к выдаче', $order->profile_status_label);
    }
}
