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
}
