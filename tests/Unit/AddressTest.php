<?php

namespace Tests\Unit;

use App\Models\Address;
use Tests\TestCase;

class AddressTest extends TestCase
{
    public function test_full_address_uses_the_local_delivery_format(): void
    {
        $address = new Address([
            'city' => 'Брест',
            'street' => 'Ленина',
            'house' => '10',
            'apartment' => '5',
            'entrance' => '2',
            'floor' => '3',
        ]);

        $this->assertSame(
            'Брест, ул. Ленина, д. 10, кв. 5, подъезд 2, этаж 3',
            $address->fullAddress()
        );
    }

    public function test_full_address_omits_empty_parts_and_keeps_legacy_address_line(): void
    {
        $address = new Address([
            'city' => 'Брест',
            'address_line' => 'Ленинградская, 115, 191',
            'entrance' => null,
            'floor' => '',
        ]);

        $this->assertSame(
            'Брест, Ленинградская, 115, 191',
            $address->fullAddress()
        );
    }

    public function test_legacy_address_line_is_generated_from_structured_fields(): void
    {
        $address = new Address([
            'street' => 'Ленина',
            'house' => '10',
            'apartment' => '5',
            'entrance' => '2',
            'floor' => '3',
        ]);

        $this->assertSame(
            'ул. Ленина, д. 10, кв. 5, подъезд 2, этаж 3',
            $address->formattedAddressLine()
        );
    }
}
