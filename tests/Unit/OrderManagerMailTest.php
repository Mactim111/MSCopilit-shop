<?php

namespace Tests\Unit;

use App\Mail\OrderManager;
use Tests\TestCase;

class OrderManagerMailTest extends TestCase
{
    public function test_manager_email_contains_order_address_and_multiline_comment(): void
    {
        $html = (new OrderManager(
            42,
            'г. Брест, ул. Московская, 15',
            "Позвонить перед доставкой.\nОставить заказ у консьержа."
        ))->render();

        $this->assertStringContainsString('Адрес доставки / пункт выдачи:', $html);
        $this->assertStringContainsString('г. Брест, ул. Московская, 15', $html);
        $this->assertStringContainsString('Позвонить перед доставкой.', $html);
        $this->assertStringContainsString('Оставить заказ у консьержа.', $html);
    }

    public function test_manager_email_omits_empty_order_comment(): void
    {
        $html = (new OrderManager(42, 'г. Брест, ул. Московская, 15'))->render();

        $this->assertStringNotContainsString('Комментарий к заказу:', $html);
    }
}
