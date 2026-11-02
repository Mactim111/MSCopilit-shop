<?php

namespace Tests\Unit;

use App\Models\Order;
use InvalidArgumentException;
use Tests\TestCase;

class OrderDeliveryPricingTest extends TestCase
{
    public function test_pickup_delivery_is_free(): void
    {
        $this->assertSame(0.0, Order::deliveryPriceFor(Order::DELIVERY_PICKUP));
    }

    public function test_courier_delivery_uses_the_static_fee(): void
    {
        $this->assertSame(20.0, Order::deliveryPriceFor(Order::DELIVERY_COURIER));
    }

    public function test_unknown_delivery_method_is_rejected(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Order::deliveryPriceFor('unknown');
    }

    public function test_payment_method_labels_match_checkout_options(): void
    {
        $cashOnDelivery = new Order(['payment_method' => Order::PAYMENT_CASH_ON_DELIVERY]);
        $onlineCard = new Order(['payment_method' => Order::PAYMENT_ONLINE_CARD]);

        $this->assertSame('При получении', $cashOnDelivery->payment_method_label);
        $this->assertSame('Наличными или банковской картой', $cashOnDelivery->payment_method_description);
        $this->assertSame('Онлайн', $onlineCard->payment_method_label);
        $this->assertSame(
            'Банковской картой Visa, MasterCard, Белкарт и др.',
            $onlineCard->payment_method_description
        );
    }
}
